<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Update extends BaseController
{
    public function index()
    {
        $userRole = session()->get('role_name');
        // Only allow admin
        if ($userRole !== 'admin') {
            return redirect()->to('dashboard')->with('error', 'Akses ditolak');
        }

        $settingsModel = new \App\Models\SettingsModel();
        $setting = $settingsModel->getSetting();

        $data = [
            'judul' => 'Update System',
            'subjudul' => 'Cek Pembaruan',
            'menu' => 'update',
            'submenu' => '',
            'current_version' => defined('APP_VERSION') ? APP_VERSION : '4.2.0',
            'setting' => $setting
        ];

        return view('update/index', $data);
    }

    public function check()
    {
        // Only allow admin via checking session or filter (done in route usually but good here too)
        if (session()->get('role_name') !== 'admin') {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Unauthorized'
            ]);
        }

        $serverUrl = defined('UPDATE_SERVER_URL') ? UPDATE_SERVER_URL : 'https://raw.githubusercontent.com/ikasmedia/cbt-update/main/version.json';

        try {
            $client = \Config\Services::curlrequest();
            $response = $client->request('GET', $serverUrl, [
                'verify' => false,
                'timeout' => 5
            ]);

            $body = $response->getBody();
            $json = json_decode($body, true);

            if (!$json || !isset($json['version'])) {
                // If invalid response, simulated fallback for demonstration or error
                throw new \Exception("Invalid response format");
            }

            $hasUpdate = version_compare($json['version'], APP_VERSION, '>');

            return $this->response->setJSON([
                'status' => true,
                'current_version' => APP_VERSION,
                'remote_version' => $json['version'],
                'download_url' => $json['url'] ?? '#',
                'changelog' => $json['changelog'] ?? 'Pembaruan tersedia.',
                'has_update' => $hasUpdate
            ]);

        } catch (\Exception $e) {
            // Because main server might not exist yet, let's simulate a response if it fails
            // OR just return the error.
            // For user satisfaction, if it fails, I'll return a simulated "Up to date" or "Error"
            // Let's return error so they know to configure it.
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Gagal menghubungi server update: ' . $e->getMessage()
            ]);
        }
    }
    public function upload()
    {
        if (session()->get('role_name') !== 'admin') {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
            }
            return redirect()->to('dashboard')->with('error', 'Akses ditolak');
        }

        $validationRule = [
            'file_update' => [
                'label' => 'File Update',
                'rules' => 'uploaded[file_update]|ext_in[file_update,backup,zip]|max_size[file_update,51200]', // 50MB max
            ],
        ];

        if (!$this->validate($validationRule)) {
            $error = $this->validator->getErrors()['file_update'];
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => false, 'message' => $error]);
            }
            return redirect()->back()->with('error', $error);
        }

        $file = $this->request->getFile('file_update');

        if ($file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads', $newName);

            $zip = new \ZipArchive;
            $res = $zip->open(WRITEPATH . 'uploads/' . $newName);

            if ($res === TRUE) {
                // Set password
                $zip->setPassword('ikasmedia');

                // Extract to root path
                $zip->extractTo(ROOTPATH);
                $zip->close();

                // Cleanup
                unlink(WRITEPATH . 'uploads/' . $newName);

                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['status' => true, 'message' => 'Update Offline berhasil diinstal!']);
                }
                return redirect()->to('update')->with('success', 'Update Offline berhasil diinstal!');
            } else {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['status' => false, 'message' => 'Gagal membuka file update (Error Code: ' . $res . ')']);
                }
                return redirect()->to('update')->with('error', 'Gagal membuka file update (Error Code: ' . $res . ')');
            }
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => false, 'message' => 'File tidak valid']);
        }
        return redirect()->to('update')->with('error', 'File tidak valid.');
    }

    public function backup()
    {
        if (session()->get('role_name') !== 'admin') {
            return redirect()->to('dashboard')->with('error', 'Akses ditolak');
        }

        // Increase limits for backup process
        ini_set('memory_limit', '1024M'); // 1GB
        ini_set('max_execution_time', 1800); // 30 mins

        $filename = 'cbt_backup_' . date('Y-m-d_H-i-s') . '.backup';
        $filepath = WRITEPATH . 'uploads/' . $filename;

        $zip = new \ZipArchive();
        if ($zip->open($filepath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
            return redirect()->back()->with('error', 'Gagal membuat file backup.');
        }

        // Password protection
        $password = 'ikasmedia';

        // Define paths
        $rootPath = realpath(ROOTPATH);
        $directories = [
            realpath(APPPATH),
            realpath(FCPATH . 'assets')
        ];

        foreach ($directories as $dir) {
            if (!$dir || !is_dir($dir))
                continue;

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                // Skip directories (they would be added automatically)
                if (!$file->isDir()) {
                    // Get real and relative path for current file
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($rootPath) + 1);

                    // Exclude patterns
                    if (strpos($filePath, '.git') !== false)
                        continue;
                    if (strpos($filePath, $filepath) !== false)
                        continue; // Skip backup file itself

                    // Add file to zip
                    $zip->addFile($filePath, $relativePath);

                    // Encrypt if supported
                    if (method_exists($zip, 'setEncryptionName')) {
                        $zip->setEncryptionName($relativePath, \ZipArchive::EM_AES_256, $password);
                    }
                }
            }
        }

        $zip->close();

        // Check availability
        if (file_exists($filepath)) {
            // Force download and delete after
            return $this->response->download($filepath, null)->setFileName($filename);
        } else {
            return redirect()->back()->with('error', 'File backup gagal dibuat.');
        }
    }
}
