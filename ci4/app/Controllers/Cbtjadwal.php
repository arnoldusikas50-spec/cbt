<?php

namespace App\Controllers;

use App\Models\CbtModel;
use App\Models\MasterModel;
use App\Models\DashboardModel;

/**
 * Cbtjadwal Controller - Migrated from CI3
 * Jadwal ujian management
 */
class Cbtjadwal extends BaseController
{
    protected CbtModel $cbtModel;
    protected MasterModel $masterModel;
    protected DashboardModel $dashboardModel;

    public function __construct()
    {
        $this->cbtModel = new CbtModel();
        $this->masterModel = new MasterModel();
        $this->dashboardModel = new DashboardModel();
    }

    /**
     * Index page
     */
    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $setting = $this->dashboardModel->getSetting();
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $data = [
            'judul' => 'Jadwal Ujian',
            'subjudul' => 'Kelola Jadwal Ujian CBT',
            'setting' => $setting,
            'tp_active' => $tp,
            'smt_active' => $smt,
            'jenis_ujian' => $this->cbtModel->getAllJenisUjian(),
            'bank_soal' => $tp && $smt ? $this->cbtModel->getAllBankSoal($tp->id_tp, $smt->id_smt) : [],
            'jadwals' => $tp && $smt ? $this->cbtModel->getAllJadwal($tp->id_tp, $smt->id_smt) : [],
            'user' => (object) ['first_name' => session()->get('first_name')]
        ];

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar', $data)
            . view('_templates/dashboard/sidebar', $data)
            . view('cbt/jadwal/data', $data)
            . view('_templates/dashboard/_footer');
    }

    /**
     * Get data for DataTable
     */
    public function data()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Unauthorized');
        }

        try {
            $tp = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();

            if (!$tp || !$smt) {
                return $this->response->setJSON(['data' => []]);
            }

            $jadwals = $this->cbtModel->getAllJadwal($tp->id_tp, $smt->id_smt);

            $data = [];
            foreach ($jadwals as $i => $j) {
                $data[] = [
                    'no' => $i + 1,
                    'id_jadwal' => $j->id_jadwal,
                    'nama_mapel' => $j->nama_mapel ?? '-',
                    'bank_nama' => $j->bank_nama ?? '-',
                    'tgl_mulai' => date('d/m/Y H:i', strtotime($j->tgl_mulai)),
                    'tgl_selesai' => date('d/m/Y H:i', strtotime($j->tgl_selesai)),
                    'durasi' => ($j->durasi ?? 60) . ' menit',
                    'status' => $j->status ?? '0',
                ];
            }

            return $this->response->setJSON([
                'draw' => $this->request->getGet('draw'),
                'recordsTotal' => count($jadwals),
                'recordsFiltered' => count($jadwals),
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['data' => [], 'error' => $e->getMessage()]);
        }
    }

    /**
     * Create new jadwal
     */
    public function save()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Unauthorized');
        }

        try {
            $tp = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();

            $db = \Config\Database::connect();

            // Get bank info
            $idBank = $this->request->getPost('id_bank');
            $bank = $this->cbtModel->getBankSoal((int) $idBank);

            $data = [
                'id_bank' => $idBank,
                'id_mapel' => $bank->id_mapel ?? null,
                'id_jenis' => $this->request->getPost('id_jenis'),
                'tgl_mulai' => $this->request->getPost('tgl_mulai'),
                'tgl_selesai' => $this->request->getPost('tgl_selesai'),
                'durasi' => $this->request->getPost('durasi') ?: 60,
                'acak_soal' => $this->request->getPost('acak_soal') ? '1' : '0',
                'acak_jawaban' => $this->request->getPost('acak_jawaban') ? '1' : '0',
                'id_tp' => $tp->id_tp,
                'id_smt' => $smt->id_smt,
                'status' => '1'
            ];

            $result = $db->table('cbt_jadwal')->insert($data);

            return $result
                ? $this->respond(true, 'Jadwal berhasil dibuat', ['id' => $db->insertID()])
                : $this->respond(false, 'Gagal membuat jadwal');
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    /**
     * Update jadwal
     */
    public function update()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Unauthorized');
        }

        try {
            $id = (int) $this->request->getPost('id_jadwal');

            if (!$id) {
                return $this->respond(false, 'ID tidak valid');
            }

            $db = \Config\Database::connect();

            $data = [
                'id_bank' => $this->request->getPost('id_bank'),
                'id_jenis' => $this->request->getPost('id_jenis'),
                'tgl_mulai' => $this->request->getPost('tgl_mulai'),
                'tgl_selesai' => $this->request->getPost('tgl_selesai'),
                'durasi' => $this->request->getPost('durasi'),
                'acak_soal' => $this->request->getPost('acak_soal') ? '1' : '0',
                'acak_jawaban' => $this->request->getPost('acak_jawaban') ? '1' : '0',
            ];

            $result = $db->table('cbt_jadwal')
                ->where('id_jadwal', $id)
                ->update($data);

            return $result
                ? $this->respond(true, 'Jadwal berhasil diupdate')
                : $this->respond(false, 'Gagal update jadwal');
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    /**
     * Toggle jadwal status
     */
    public function toggleStatus()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Unauthorized');
        }

        try {
            $id = (int) $this->request->getPost('id_jadwal');
            $status = $this->request->getPost('status');

            $db = \Config\Database::connect();

            $result = $db->table('cbt_jadwal')
                ->where('id_jadwal', $id)
                ->update(['status' => $status]);

            return $result
                ? $this->respond(true, 'Status berhasil diubah')
                : $this->respond(false, 'Gagal mengubah status');
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    /**
     * Delete jadwal
     */
    public function delete()
    {
        if (!$this->isAdmin()) {
            return $this->respond(false, 'Hanya admin yang dapat menghapus');
        }

        try {
            $id = (int) $this->request->getPost('id_jadwal');

            if (!$id) {
                return $this->respond(false, 'ID tidak valid');
            }

            $db = \Config\Database::connect();

            $result = $db->table('cbt_jadwal')
                ->where('id_jadwal', $id)
                ->delete();

            return $result
                ? $this->respond(true, 'Jadwal berhasil dihapus')
                : $this->respond(false, 'Gagal menghapus jadwal');
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }
}
