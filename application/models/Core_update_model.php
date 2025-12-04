<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Core_update_model extends CI_Model {

    private $folders = array(
        'application/controllers/' . CMS_DIR_NAME,
        'application/helpers',
        'application/language',
        'application/libraries',
        'application/models/backend',
        'application/views/backend',
        'application/core',
        'application/third_party',
        'application/hooks'
    );
    private $excluded_files = array(
        'MY_Frontend.php',
        'Config.php'
    );
    private $limit_files_per_open = true;
    // do not edit
    public $files_processed = 0;
    public $updates_folder;
    private $dir_unpack;
    private $max_files_per_open = 1024;
    private $current_file_count = 0;
    private $zip;
    private $manifest = array();
    private $manifest_file = 'MANIFEST.txt';
    private $identifier_file = 'SYSUPDATE.txt';

    public function __construct() {
        parent::__construct();
        $this->lang->load('core_update');
        $this->updates_folder = genpath(APPPATH . 'migrations/system_updates/');
        $this->dir_unpack = genpath();
        if (!extension_loaded('zip')) {
            show_error(sprintf($this->lang->line('update_zip_ext_failed_load'), __CLASS__), 500);
        } else {
            $this->zip = new \ZipArchive;
        }
    }

    public function restore_previous() {
        try {
            $beforeupdate = $this->find_previous_version();
            $new_manifest = file_get_contents($this->updates_folder . 'manifests/manifest_new.txt');
            $old_manifest = file_get_contents($this->updates_folder . 'manifests/manifest_old.txt');

            if ($new_manifest == false || $old_manifest == false) {
                throw new Exception($this->lang->line('update_manifest_error'));
            }

            $new_manifest = unserialize($new_manifest);
            $old_manifest = unserialize($old_manifest);
            $difference = array_diff($new_manifest, $old_manifest);

            if (count($difference) > 0) {
                foreach ($difference as $file) {
                    @unlink($this->dir_unpack . $file); // remove new files
                }
            }

            if ($this->zip->open($beforeupdate['path']) !== true) {
                throw new Exception(sprintf($this->lang->line('update_zip_failed_to_open'), __FUNCTION__, $beforeupdate['path']));
            }

            $this->verify_update($this->zip, $beforeupdate['name']); // name() w/o extension
            $this->files_processed = $this->zip->numFiles;
            $this->zip->extractTo($this->dir_unpack);
            $this->zip->close();
            $this->rmdir_recursive($this->updates_folder);
        } catch (Exception $e) {
            @$this->zip->close();
            throw $e;
        } finally {
            @unlink($this->dir_unpack . $this->identifier_file);
            @unlink($this->dir_unpack . $this->manifest_file);
        }
    }

    public function apply_update($file) {
        try {
            if (!file_exists($file)) {
                throw new Exception(sprintf($this->lang->line('update_file_not_exist'), $file));
            }

            $zip = new \ZipArchive;
            if ($zip->open($file) !== true) {
                throw new Exception(sprintf($this->lang->line('update_zip_failed_to_open'), __FUNCTION__, $file));
            }

            $this->verify_update($zip, basename($file, '.zip')); // name() w/o extension
            $this->delete_old_updates(basename($file)); // name w/ extension
            $this->create_update(basename($file, '.zip') . '_beforeupdate'); // image of site before update
            $this->files_processed = $zip->numFiles;
            $zip->extractTo($this->dir_unpack);
            @unlink($this->dir_unpack . $this->identifier_file);
            if (!rename($this->dir_unpack . $this->manifest_file, $this->updates_folder . 'manifests/manifest_new.txt')) {
                throw new Exception($this->lang->line('update_manifest_move_error'));
            }
            $zip->close();
        } catch (Exception $e) {
            $zip->close();
            throw $e;
        }
    }

    public function create_update($update_name = null) {
        if (is_null($update_name)) {
            $update_name = 'sysupdate_' . date('Ymd_Hms');
        }
        $destination = $this->updates_folder . $update_name . '.zip';

        try {
            $this->mk_update_dir();
            if ($this->zip->open($destination, \ZIPARCHIVE::CREATE | \ZIPARCHIVE::OVERWRITE) !== true) {
                throw new Exception(sprintf($this->lang->line('update_zip_failed_to_create'), __FUNCTION__, $destination));
            }

            $this->identify_update($update_name);
            foreach ($this->folders as $folder) {
                $this->zippy($folder, $destination);
            }
            $this->create_manifest();
            $this->zip->close();
        } catch (Exception $e) {
            @$this->zip->close();
            @unlink($destination); // zip has to be closed before we remove the $destination
            throw $e; // rethrow
        }
    }

    public function previous_exists() {
        try {
            $this->find_previous_version();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function find_previous_version() {
        $files = glob($this->updates_folder . '*_beforeupdate.zip');
        $count = count($files);

        if ($count > 1) {
            throw new Exception($this->lang->line('update_previous_error_1'));
        } elseif ($count < 1) {
            throw new Exception($this->lang->line('update_previous_error_2'));
        } else {
            return ['path' => $files[0], 'name' => basename($files[0], '.zip')];
        }
    }

    private function delete_old_updates($current_file) {
        $files = glob($this->updates_folder . '*');
        foreach ($files as $file) {
            if (basename($file) == $current_file) {
                continue;
            }
            @unlink($file);
        }
    }

    private function create_manifest() {
        $manifest_path = $this->updates_folder . 'manifests/manifest_old.txt';
        $contents = serialize($this->manifest);
        if (file_put_contents($manifest_path, $contents) == false) {
            throw new Exception($this->lang->line('update_manifest_write_error'));
        }

        if ($this->zip->addFile($manifest_path, $this->manifest_file)) {
            $this->current_file_count++;
        } else {
            @unlink($manifest_path);
            throw new Exception($this->lang->line('update_manifest_add_error'));
        }
    }

    private function identify_update($update_name) {
        if ($this->zip->addFromString($this->identifier_file, base64_encode($update_name))) {
            $this->current_file_count++;
        } else {
            throw new Exception($this->lang->line('update_id_add_error'));
        }
    }

    private function verify_update($zip, $update_name) {
        $identifier = $zip->getFromName($this->identifier_file);
        if (!$identifier || base64_decode($identifier) !== $update_name) {
            throw new Exception($this->lang->line('update_id_find_error'));
        }

        if (!$zip->getFromName($this->manifest_file)) {
            throw new Exception($this->lang->line('update_manifest_find_error'));
        }
    }

    public function mk_update_dir() {
        if (!is_dir($this->updates_folder) && mkdir($this->updates_folder, DIR_WRITE_MODE) == false) {
            throw new Exception(sprintf($this->lang->line('update_create_dir_error'), $this->updates_folder));
        }
    }

    private function rmdir_recursive($dir) {
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), array('.', '..'));
            foreach ($files as $file) {
                $filePath = $dir . DIRECTORY_SEPARATOR . $file;
                is_dir($filePath) ? $this->rmdir_recursive($filePath) : unlink($filePath);
            }
            rmdir($dir);
        }
    }

    private function zippy($dir, $destination) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }
            if ($file->isFile()) {
                $filename = $file->getBasename();
                if (in_array($filename, $this->excluded_files)) {
                    continue;
                } else {
                    $this->current_file_count++; // increment curr file count
                    if ($this->limit_files_per_open) {
                        $this->reopen($destination);
                    }
                    $path = str_replace($this->dir_unpack, '', $file);
                    $path = ltrim(fix_slashes($path, 'linux'), '/');
                    $this->zip->addFile($file, $path); // add file
                    $this->manifest[] = $path;
                    $this->files_processed++; // increment files added
                }
            }
        }
    }

    private function reopen($destination) {
        if ($this->current_file_count >= $this->max_files_per_open) {
            $this->zip->close();
            $this->current_file_count = 0; // reset curr file count
            if ($this->zip->open($destination, \ZIPARCHIVE::CREATE) !== true) {
                throw new Exception(sprintf($this->lang->line('update_zip_reopen_fail'), __FUNCTION__, $destination, $this->files_processed));
            }
        }
    }
}
