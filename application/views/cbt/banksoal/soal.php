<?php
/**

 * Date: 14/07/20
 * Time: 17:46
 */

$pg_belum_komplit = [];
$pg2_belum_komplit = [];
$jodohkan_belum_komplit = [];
$isian_belum_komplit = [];
$essai_belum_komplit = [];
$urutan_belum_komplit = [];
$cloze_belum_komplit = [];

foreach ($soal_belum_komplit as $key => $value) {
    if ($value->jenis == '1') {
        $pg_belum_komplit[] = $value->nomor_soal;
    } elseif ($value->jenis == '2') {
        $pg2_belum_komplit[] = $value->nomor_soal;
    } elseif ($value->jenis == '3') {
        $jodohkan_belum_komplit[] = $value->nomor_soal;
    } elseif ($value->jenis == '4') {
        $isian_belum_komplit[] = $value->nomor_soal;
    } elseif ($value->jenis == '5') {
        $essai_belum_komplit[] = $value->nomor_soal;
    } elseif ($value->jenis == '6') {
        $urutan_belum_komplit[] = $value->nomor_soal;
    } elseif ($value->jenis == '7') {
        $cloze_belum_komplit[] = $value->nomor_soal;
    }
}

$soal_pg_ada = [];
$soal_pg2_ada = [];
$soal_jodohkan_ada = [];
$soal_isian_ada = [];
$soal_esai_ada = [];
$soal_urutan_ada = [];
$soal_cloze_ada = [];

foreach ($soal_ada as $key => $value) {
    if ($value->jenis == '1') {
        $soal_pg_ada[] = $value->nomor_soal;
    } elseif ($value->jenis == '2') {
        $soal_pg2_ada[] = $value->nomor_soal;
    } elseif ($value->jenis == '3') {
        $soal_jodohkan_ada[] = $value->nomor_soal;
    } elseif ($value->jenis == '4') {
        $soal_isian_ada[] = $value->nomor_soal;
    } elseif ($value->jenis == '5') {
        $soal_esai_ada[] = $value->nomor_soal;
    } elseif ($value->jenis == '6') {
        $soal_urutan_ada[] = $value->nomor_soal;
    } elseif ($value->jenis == '7') {
        $soal_cloze_ada[] = $value->nomor_soal;
    }
}
?>

<style>
    /* Fix Summernote Toolbar conflict with modern-redesign.css */
    .note-editor.card {
        margin-bottom: 0 !important;
        overflow: visible !important;
        /* Allow dropdowns */
    }

    .note-editor .card-header {
        display: block !important;
        /* Reset flex spread */
        padding: 5px 10px !important;
        background: #f8f9fa !important;
    }

    .note-editor .btn {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.875rem !important;
        box-shadow: none !important;
        border-radius: 0.2rem !important;
    }

    .note-toolbar {
        z-index: 10 !important;
        /* Ensure toolbar is usable */
    }

    .btn-active-soal {
        background-color: #ffc107 !important;
        border-color: #ffc107 !important;
        color: #000 !important;
    }
</style>

<main class="app-main bg-white">
    <section class="content-header mt-3">
        <div class="container-fluid">
            <div class="d-sm-flex justify-content-between mb-2">
                <h3></h3>
                <div>
                    <button onclick="openAIGenerateModal()" class="btn btn-sm btn-primary me-2">
                        <i class="bi bi-robot"></i><span class="d-none d-sm-inline-block ms-1">Generate Soal
                            AI</span>
                    </button>
                    <button onclick="window.history.back();" type="button" class="btn btn-sm btn-danger">
                        <i class="bi bi-arrow-left-circle"></i><span
                            class="d-none d-sm-inline-block ms-1">Kembali</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card my-shadow">
                <div class="card-body">
                    <div class="row">
                        <h6 class="col-md-6"><b>Bank
                                Soal:</b>
                            <?= $bank->bank_kode . ' | PG: ' . $bank->tampil_pg . ', Essai: ' . $bank->tampil_esai ?>
                        </h6>
                    </div>
                </div>
            </div>
            <?= form_open('create', array('id' => 'create')) ?>
            <div class="card my-shadow">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a
                                class="nav-link <?= $tab_active == '1' ? 'active' : '' ?> alert-default-primary"
                                href="<?= base_url('cbtbanksoal/buatsoal/' . $bank->id_bank . '?tab=1') ?>">Soal PG</a>
                        </li>
                        <li class="nav-item"><a
                                class="nav-link <?= $tab_active == '2' ? 'active' : '' ?> alert-default-info"
                                href="<?= base_url('cbtbanksoal/buatsoal/' . $bank->id_bank . '?tab=2') ?>">PG
                                Kompleks</a></li>
                        <li class="nav-item"><a
                                class="nav-link <?= $tab_active == '3' ? 'active' : '' ?> alert-default-danger"
                                href="<?= base_url('cbtbanksoal/buatsoal/' . $bank->id_bank . '?tab=3') ?>">Menjodohkan</a>
                        </li>
                        <li class="nav-item"><a
                                class="nav-link <?= $tab_active == '4' ? 'active' : '' ?> alert-default-warning"
                                href="<?= base_url('cbtbanksoal/buatsoal/' . $bank->id_bank . '?tab=4') ?>">Isian
                                Singkat</a></li>
                        <li class="nav-item"><a
                                class="nav-link <?= $tab_active == '5' ? 'active' : '' ?> alert-default-success"
                                href="<?= base_url('cbtbanksoal/buatsoal/' . $bank->id_bank . '?tab=5') ?>">Essai</a>
                        </li>
                        <li class="nav-item"><a class="nav-link <?= $tab_active == '6' ? 'active' : '' ?>"
                                style="background-color: <?= $tab_active == '6' ? '#6f42c1' : '#f3e8ff' ?>; color: <?= $tab_active == '6' ? '#fff' : '#6f42c1' ?>;"
                                href="<?= base_url('cbtbanksoal/buatsoal/' . $bank->id_bank . '?tab=6') ?>">Urutan</a>
                        </li>
                        <li class="nav-item"><a class="nav-link <?= $tab_active == '7' ? 'active' : '' ?>"
                                style="background-color: <?= $tab_active == '7' ? '#0d6efd' : '#e7f1ff' ?>; color: <?= $tab_active == '7' ? '#fff' : '#0d6efd' ?>;"
                                href="<?= base_url('cbtbanksoal/buatsoal/' . $bank->id_bank . '?tab=7') ?>">Isian
                                Rumpang</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <input type="hidden" id="jenis-id" name="jenis" value="1" class="form-control">
                    <input type="hidden" id="bank-id" name="bank_id" value="<?= $bank->id_bank ?>" class="form-control">
                    <input type="hidden" id="soal-id" name="soal_id"
                        value="<?= is_null($soal) ? '0' : $soal->id_soal ?>" class="form-control">
                    <input type="hidden" id="method" name="method" value="<?= is_null($soal) ? 'add' : 'edit' ?>"
                        class="form-control">

                    <div class="tab-content">
                        <div class="tab-pane  <?= $tab_active == '1' ? 'active' : '' ?>" id="ganda">
                            <div class="alert alert-default-success align-content-center" role="alert">
                                Pilihan Ganda, jumlah soal seharusnya: <b><?= $bank->tampil_pg ?></b>
                            </div>
                            <span><b>PG NOMOR: </b></span><br>
                            <?php
                            $no = 1;
                            $tpg = (!isset($jml_pg) || empty($jml_pg) || !isset($jml_pg->nomor_soal)) ? 0 : $jml_pg->nomor_soal;
                            for ($i = 0; $i < $tpg; $i++): ?>
                                <a href="javascript:void(0)"
                                    class="mt-1 btn btn-circle-sm <?= !in_array($no, $pg_belum_komplit) ? 'btn-success' : 'btn-danger' ?> <?= empty($soal) ? ($no == 1 ? 'active' : '') : ($no == ((is_object($soal) && isset($soal->nomor_soal)) ? $soal->nomor_soal : 0) ? 'active' : '') ?>"
                                    onclick="getSoalById(<?= $bank->id_bank ?>, <?= $no ?>, <?= '1' . $no ?>, '1')"
                                    id="btn-<?= '1' . $no ?>"><?= $no ?>
                                </a>
                                <?php
                                $no++;
                            endfor; ?>
                            <a href="javascript:void(0)" class="pt-1 btn btn-oval-sm btn-success"
                                data-mapel="<?= $bank->bank_mapel_id ?>" data-bank="<?= $bank->id_bank ?>"
                                data-nomor="<?= $no ?>" onclick="tambahSoalPg(this)" id="btn-add-new-pg"><i
                                    class="bi bi-plus-lg"></i> Tambah PG</a>
                        </div>
                        <div class="tab-pane <?= $tab_active == '2' ? 'active' : '' ?>" id="kompleks">
                            <div class="alert alert-default-success align-content-center" role="alert">
                                Pilihan Ganda Kompleks, jumlah soal seharusnya: <b><?= $bank->tampil_kompleks ?></b>
                            </div>
                            <span><b>SOAL NOMOR: </b></span><br>
                            <?php

                            $tpg2 = (!isset($jml_pg2) || empty($jml_pg2) || !isset($jml_pg2->nomor_soal)) ? 0 : $jml_pg2->nomor_soal;
                            $no2 = 1;
                            for ($i = 0; $i < $tpg2; $i++): ?>
                                <a href="javascript:void(0)"
                                    class="mt-1 btn btn-circle-sm <?= !in_array($no2, $pg2_belum_komplit) ? 'btn-success' : 'btn-danger' ?> <?= empty($soal) ? ($no2 == 1 ? 'active' : '') : ($no2 == ((is_object($soal) && isset($soal->nomor_soal)) ? $soal->nomor_soal : 0) ? 'active' : '') ?>"
                                    onclick="getSoalById(<?= $bank->id_bank ?>, <?= $no2 ?>, <?= '2' . $no2 ?>, '2')"
                                    id="btn-<?= '2' . $no2 ?>"><?= $no2 ?>
                                </a>
                                <?php
                                $no2++;
                            endfor; ?>
                            <a href="javascript:void(0)" class="pt-1 btn btn-oval-sm btn-success"
                                data-mapel="<?= $bank->bank_mapel_id ?>" data-bank="<?= $bank->id_bank ?>"
                                data-nomor="<?= $no2 ?>" onclick="tambahSoalPg2(this)" id="btn-add-new-pg"><i
                                    class="bi bi-plus-lg"></i> Tambah PG Kompleks</a>
                        </div>
                        <div class="tab-pane <?= $tab_active == '3' ? 'active' : '' ?>" id="jodoh">
                            <div class="alert alert-default-success align-content-center" role="alert">
                                Soal Menjodohkan, jumlah soal seharusnya: <b><?= $bank->tampil_jodohkan ?></b>
                            </div>
                            <span><b>SOAL NOMOR: </b></span><br>
                            <?php $noj = 1;
                            $tej = (!isset($jml_jodohkan) || empty($jml_jodohkan) || !isset($jml_jodohkan->nomor_soal)) ? 0 : $jml_jodohkan->nomor_soal;
                            for ($i = 0; $i < $tej; $i++): ?>
                                <a href="javascript:void(0)"
                                    class="mt-1 btn btn-circle-sm <?= !in_array($noj, $jodohkan_belum_komplit) ? 'btn-primary' : 'btn-danger' ?>"
                                    onclick="getSoalById(<?= $bank->id_bank ?>, <?= $noj ?>, <?= '3' . $noj ?>, '3')"
                                    id="btn-<?= '3' . $noj ?>"><?= $noj ?>
                                </a>
                                <?php $noj++; endfor; ?>
                            <a href="javascript:void(0)" class="pt-1 btn btn-oval-sm btn-primary"
                                data-mapel="<?= $bank->bank_mapel_id ?>" data-bank="<?= $bank->id_bank ?>"
                                data-nomor="<?= $noj ?>" onclick="tambahSoalJodohkan(this)" id="btn-add-new-essai"><i
                                    class="bi bi-plus-lg"></i> Tambah Soal Menjodohkan</a>
                        </div>
                        <div class="tab-pane <?= $tab_active == '4' ? 'active' : '' ?>" id="isian">
                            <div class="alert alert-default-success align-content-center" role="alert">
                                Soal Isian Singkat, jumlah soal seharusnya: <b><?= $bank->tampil_isian ?></b>
                            </div>
                            <span><b>SOAL NOMOR: </b></span><br>
                            <?php $noi = 1;
                            $tei = (!isset($jml_isian) || empty($jml_isian) || !isset($jml_isian->nomor_soal)) ? 0 : $jml_isian->nomor_soal;
                            for ($i = 0; $i < $tei; $i++): ?>
                                <a href="javascript:void(0)"
                                    class="mt-1 btn btn-circle-sm <?= !in_array($noi, $isian_belum_komplit) ? 'btn-primary' : 'btn-danger' ?>"
                                    onclick="getSoalById(<?= $bank->id_bank ?>, <?= $noi ?>, <?= '4' . $noi ?>, '4')"
                                    id="btn-<?= '4' . $noi ?>"><?= $noi ?>
                                </a>
                                <?php $noi++; endfor; ?>
                            <a href="javascript:void(0)" class="pt-1 btn btn-oval-sm btn-primary"
                                data-mapel="<?= $bank->bank_mapel_id ?>" data-bank="<?= $bank->id_bank ?>"
                                data-nomor="<?= $noi ?>" onclick="tambahSoalIsian(this)" id="btn-add-new-essai"><i
                                    class="bi bi-plus-lg"></i> Tambah Soal Isian</a>
                        </div>
                        <div class="tab-pane <?= $tab_active == '5' ? 'active' : '' ?>" id="essai">
                            <div class="alert alert-default-success align-content-center" role="alert">
                                Soal Uraian/Essai, jumlah soal seharusnya: <b><?= $bank->tampil_esai ?></b>
                            </div>
                            <span><b>ESSAI NOMOR: </b></span><br>
                            <?php $noe = 1;
                            $tes = (!isset($jml_essai) || empty($jml_essai) || !isset($jml_essai->nomor_soal)) ? 0 : $jml_essai->nomor_soal;
                            for ($i = 0; $i < $tes; $i++): ?>
                                <a href="javascript:void(0)"
                                    class="mt-1 btn btn-circle-sm <?= !in_array($noe, $essai_belum_komplit) ? 'btn-primary' : 'btn-danger' ?>"
                                    onclick="getSoalById(<?= $bank->id_bank ?>, <?= $noe ?>, <?= '5' . $noe ?>, '5')"
                                    id="btn-<?= '5' . $noe ?>"><?= $noe ?>
                                </a>
                                <?php $noe++; endfor; ?>
                            <a href="javascript:void(0)" class="pt-1 btn btn-oval-sm btn-primary"
                                data-mapel="<?= $bank->bank_mapel_id ?>" data-bank="<?= $bank->id_bank ?>"
                                data-nomor="<?= $noe ?>" onclick="tambahSoalEssai(this)" id="btn-add-new-essai"><i
                                    class="bi bi-plus-lg"></i> Tambah Essai</a>
                        </div>
                        <div class="tab-pane <?= $tab_active == '6' ? 'active' : '' ?>" id="urutan">
                            <div class="alert alert-default-success align-content-center" role="alert">
                                Soal Urutan/Sequencing, jumlah soal seharusnya:
                                <b><?= isset($bank->tampil_urutan) ? $bank->tampil_urutan : '0' ?></b>
                            </div>
                            <span><b>SOAL NOMOR: </b></span><br>
                            <?php $nou = 1;
                            $teu = (!isset($jml_urutan) || empty($jml_urutan) || !isset($jml_urutan->nomor_soal)) ? 0 : $jml_urutan->nomor_soal;
                            for ($i = 0; $i < $teu; $i++): ?>
                                <?php
                                $is_active = ($tab_active == '6' && $nou == $p_no);
                                $base_class = !in_array($nou, $urutan_belum_komplit) ? 'btn-primary' : 'btn-danger';
                                $act_class = $is_active ? 'btn-active-soal' : $base_class;
                                ?>
                                <a href="javascript:void(0)" class="mt-1 btn btn-circle-sm <?= $act_class ?>"
                                    onclick="getSoalById(<?= $bank->id_bank ?>, <?= $nou ?>, <?= '6' . $nou ?>, '6')"
                                    id="btn-<?= '6' . $nou ?>"><?= $nou ?>
                                </a>
                                <?php $nou++; endfor; ?>
                            <a href="javascript:void(0)" class="pt-1 btn btn-oval-sm btn-primary"
                                style="background-color: #6f42c1; border-color: #6f42c1;"
                                data-mapel="<?= $bank->bank_mapel_id ?>" data-bank="<?= $bank->id_bank ?>"
                                data-nomor="<?= $nou ?>" onclick="tambahSoalUrutan(this)" id="btn-add-new-urutan"><i
                                    class="bi bi-plus-lg"></i> Tambah Soal Urutan</a>
                        </div>
                        <div class="tab-pane <?= $tab_active == '7' ? 'active' : '' ?>" id="cloze">
                            <div class="alert alert-default-success align-content-center" role="alert">
                                Soal Isian Rumpang (Cloze), jumlah soal seharusnya:
                                <b><?= isset($bank->tampil_cloze) ? $bank->tampil_cloze : '0' ?></b>
                            </div>
                            <span><b>SOAL NOMOR: </b></span><br>
                            <?php $noc = 1;
                            $tec = (!isset($jml_cloze) || empty($jml_cloze) || !isset($jml_cloze->nomor_soal)) ? 0 : $jml_cloze->nomor_soal;
                            for ($i = 0; $i < $tec; $i++): ?>
                                <?php
                                $is_active = ($tab_active == '7' && $noc == $p_no);
                                $base_class = !in_array($noc, $cloze_belum_komplit) ? 'btn-primary' : 'btn-danger';
                                $act_class = $is_active ? 'btn-active-soal' : $base_class;
                                ?>
                                <a href="javascript:void(0)" class="mt-1 btn btn-circle-sm <?= $act_class ?>"
                                    onclick="getSoalById(<?= $bank->id_bank ?>, <?= $noc ?>, <?= '7' . $noc ?>, '7')"
                                    id="btn-<?= '7' . $noc ?>"><?= $noc ?>
                                </a>
                                <?php $noc++; endfor; ?>
                            <a href="javascript:void(0)" class="pt-1 btn btn-oval-sm btn-primary"
                                style="background-color: #0d6efd; border-color: #0d6efd;"
                                data-mapel="<?= $bank->bank_mapel_id ?>" data-bank="<?= $bank->id_bank ?>"
                                data-nomor="<?= $noc ?>" onclick="tambahSoalCloze(this)" id="btn-add-new-cloze"><i
                                    class="bi bi-plus-lg"></i> Tambah Soal Isian Rumpang</a>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            $pg_none = $tab_active == '1' ? '' : 'd-none';
            $pg2_none = $tab_active == '2' ? '' : 'd-none';
            $jodohkan_none = $tab_active == '3' ? '' : 'd-none';
            $isian_none = $tab_active == '4' ? '' : 'd-none';
            $essai_none = $tab_active == '5' ? '' : 'd-none';
            ?>
            <div class="alert alert-default-info" id="empty-soal">
                <span>Belum ada soal</span>
            </div>
            <div class="card my-shadow d-none" id="not-empty-soal">
                <div class="card-header">
                    <div class="card-title">
                        <h6 id="nomor-soal"><b>Soal Nomor: 1</b></h6>
                    </div>
                    <div class="card-tools ms-auto">
                        <button type="submit" class="btn btn-primary btn-sm me-1">
                            <i class="bi bi-floppy me-1"></i>Simpan
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="soal-area mt-4">
                        <div class="row">
                            <div class="col-md-6" id="soal-area">
                                <div class="mb-3">
                                    <label>Soal
                                        <button type="button" onclick="paraphraseSoal()"
                                            class="btn btn-xs btn-warning float-end ms-2">
                                            <i class="bi bi-magic"></i> Parafrase AI
                                        </button>
                                        <button type="button" onclick="tidySoal()"
                                            class="btn btn-xs btn-info float-end ms-2"
                                            title="Rapikan struktur HTML tabel/gambar menggunakan AI">
                                            <i class="bi bi-robot"></i> Rapikan (AI)
                                        </button>
                                        <button type="button" onclick="tidyManual()"
                                            class="btn btn-xs btn-success float-end ms-2"
                                            title="Hapus semua format & kode, sisakan teks saja (Text Only)">
                                            <i class="bi bi-eraser"></i> Rapikan (Text Only)
                                        </button>
                                    </label>
                                    <textarea class="textsoal" id="textsoal"
                                        data-id="<?= $this->security->get_csrf_hash() ?>"
                                        data-name="<?= $this->security->get_csrf_token_name() ?>" name="soal"
                                        placeholder="Tulis soal disini" style="width:100%;"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6" id="jawaban-area">
                                <?php
                                $opsis[''] = 'Pilih Jawaban Benar :';
                                $opsis['a'] = 'A';
                                $opsis['b'] = 'B';
                                $opsis['c'] = 'C';

                                if ($bank->opsi == 4) {
                                    $opsis['d'] = 'D';
                                    $rangeAbjad = range('a', 'd');
                                } elseif ($bank->opsi == 5) {
                                    $opsis['d'] = 'D';
                                    $opsis['e'] = 'E';
                                    $rangeAbjad = range('a', 'e');
                                } else {
                                    $rangeAbjad = range('a', 'c');
                                }
                                ;
                                ?>
                                <div id="root-opsi-pg" class="<?= $pg_none ?>">
                                    <?php
                                    foreach ($rangeAbjad as $abjad):
                                        ?>
                                        <div class="mb-3 ms-3">
                                            <label>Jawaban <?= strtoupper($abjad ?? '') ?></label>
                                            <textarea class="textjawaban" id="textjawaban_<?= $abjad ?>"
                                                name="jawaban_<?= $abjad ?>" placeholder="Place some text here"
                                                style="width:100%;"></textarea>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div id="root-opsi-pg2" class="<?= $pg2_none ?>">
                                    <div id="opsi-pg2"></div>
                                    <div class="mb-3 ms-3">
                                        <button id="tambah-jawaban-pg2" type="button" class="btn btn-success"><a
                                                class="bi bi-plus-lg"></a> Tambah
                                            Jawaban
                                        </button>
                                    </div>
                                </div>

                                <div class="<?= $jodohkan_none ?> mt-4" id="root-opsi-jodohkan">
                                    <hr>
                                    <p><b>Jawaban</b></p>
                                    <div id="jawaban-jodohkan">
                                    </div>
                                    <div class="alert alert-default-warning">
                                        Maksimal <b>20 KOLOM</b> dan <b>20 BARIS</b>
                                    </div>
                                </div>

                                <!-- URUTAN (TYPE 6) INPUT -->
                                <div class="<?= $tab_active == '6' ? '' : 'd-none' ?> mt-4" id="root-opsi-urutan">
                                    <hr>
                                    <p><b>Item yang akan diurutkan</b> <small class="text-muted">(tambahkan minimal 2
                                            item)</small></p>
                                    <div id="urutan-items-container">
                                        <div class="input-group mb-2 urutan-item" data-idx="0">
                                            <span class="input-group-text"><i class="bi bi-grip-vertical"></i> 1</span>
                                            <input type="text" class="form-control urutan-input"
                                                placeholder="Item pertama (urutan benar)">
                                            <button type="button" class="btn btn-danger btn-remove-urutan"
                                                onclick="removeUrutanItem(this)"><i class="bi bi-x-lg"></i></button>
                                        </div>
                                        <div class="input-group mb-2 urutan-item" data-idx="1">
                                            <span class="input-group-text"><i class="bi bi-grip-vertical"></i> 2</span>
                                            <input type="text" class="form-control urutan-input"
                                                placeholder="Item kedua">
                                            <button type="button" class="btn btn-danger btn-remove-urutan"
                                                onclick="removeUrutanItem(this)"><i class="bi bi-x-lg"></i></button>
                                        </div>
                                    </div>
                                    <button id="tambah-item-urutan" type="button" class="btn btn-success btn-sm">
                                        <i class="bi bi-plus-lg"></i> Tambah Item
                                    </button>
                                    <div class="alert alert-default-info mt-2 small">
                                        <i class="bi bi-info-circle-fill"></i> Masukkan item dalam <b>urutan yang
                                            BENAR</b>.
                                        Sistem akan mengacak saat ditampilkan ke siswa.
                                    </div>
                                </div>

                                <!-- ISIAN RUMPANG / CLOZE (TYPE 7) INPUT -->
                                <div class="<?= $tab_active == '7' ? '' : 'd-none' ?> mt-4" id="root-opsi-cloze">
                                    <hr>
                                    <p><b>Teks dengan bagian kosong</b></p>
                                    <div class="alert alert-default-info small mb-2">
                                        <i class="bi bi-info-circle-fill"></i> Gunakan format <code>[[1]]</code>,
                                        <code>[[2]]</code>, dst. untuk menandai bagian yang harus diisi siswa.<br>
                                        Contoh: <em>"Indonesia merdeka pada tanggal [[1]] bulan [[2]] tahun [[3]]."</em>
                                    </div>
                                    <textarea id="cloze-text-input" class="form-control" rows="4"
                                        placeholder="Ketik teks soal dengan [[1]], [[2]], [[3]] untuk bagian kosong..."></textarea>

                                    <p class="mt-3"><b>Kunci Jawaban</b></p>
                                    <div id="cloze-answers-container">
                                        <!-- Will be populated dynamically based on [[n]] placeholders -->
                                    </div>
                                    <button id="parse-cloze-blanks" type="button" class="btn btn-info btn-sm mt-2">
                                        <i class="bi bi-arrow-repeat"></i> Perbarui Kunci Jawaban
                                    </button>
                                </div>

                                <div class="mt-1 ms-3" id="jawaban-benar">
                                    <label>Jawaban Benar</label>
                                    <div id="root-jawaban-pg" class="<?= $pg_none ?>">
                                        <?php
                                        echo form_dropdown(
                                            'jawaban_pg',
                                            $opsis,
                                            '',
                                            'id="jawaban" class="select2 form-control" required'
                                        ); ?>
                                    </div>
                                    <div class="<?= $isian_none ?>" id="root-jawaban-isian">
                                        <input type="text" id="jawaban-isian" name="jawaban_isian" class="form-control"
                                            value="" disabled="disabled" />
                                    </div>
                                    <div class="<?= $essai_none ?>" id="root-jawaban-essai">
                                        <textarea class="textjawaban-essai" id="jawaban-essai" name="jawaban_essai"
                                            placeholder="Place some text here" style="width:100%;" disabled="disabled">
                                        </textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="overlay d-none" id="loading">
                    <!-- <div class="spinner-grow"></div> -->
                    <button class="btn btn-primary" type="button" disabled>
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"> </span>
                        Loading...
                    </button>
                </div>
            </div>
            <?= form_close() ?>
        </div>
    </section>
    </div>

    <?= form_open('create', array('id' => 'addfile')) ?>
    <div class="modal fade" id="pickerModal" tabindex="-1" role="dialog" aria-labelledby="pickerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title">
                        <h5 id="pickerModalLabel">Pilih File</h5>
                        <br> hanya untuk file audio dan video
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <ul id="media-list" class="clearfix">
                            <li class="myupload">
                                <span>
                                    <i class="bi bi-plus-lg" aria-hidden="true"></i>
                                    <input name="file_uploads" type="file" id="picupload" class="picupload">
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
    <?= form_close() ?>

    <div class="modal fade" id="noteModal" tabindex="-1" role="dialog" aria-labelledby="noteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title">
                        <h5 id="noteModalLabel">Edit Field</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">
                    <textarea id="edit-dialog" style="width:100%;"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    <!-- ikasmedia -->
                    <button type="button" class="btn btn-primary" onclick="simpanValue()">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= base_url() ?>/assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
    <script src="<?= base_url() ?>/assets/app/js/mediastyler.js"></script>

    <style>
        .swal2-container {
            z-index: 20000 !important;
        }
    </style>

    <script src="<?= base_url() ?>/assets/app/js/linker-list.js"></script>
    <script src="<?= base_url() ?>/assets/plugins/element-queries/ElementQueries.js"></script>
    <script src="<?= base_url() ?>/assets/plugins/element-queries/ResizeSensor.js"></script>
    <script src="<?= base_url() ?>/assets/plugins/katex/katex.min.js"></script>

    <script type="text/javascript">
        var nomor_soal = '<?= $p_no ?>';
        let soal_ada = '<?= empty($soal) ? 'n' : 'y' ?>';
        let idBank = '<?= $bank->id_bank ?>';
        let idSoal = '<?= empty($soal) ? 0 : ((is_object($soal) && isset($soal->id_soal)) ? $soal->id_soal : 0) ?>';
        let jenis = '<?= $tab_active ?>';


        var adaPg = JSON.parse('<?= json_encode($soal_pg_ada) ?>');
        var adaPg2 = JSON.parse('<?= json_encode($soal_pg2_ada) ?>');
        var adaJodohkan = JSON.parse('<?= json_encode($soal_jodohkan_ada) ?>');
        var adaIsian = JSON.parse('<?= json_encode($soal_isian_ada) ?>');
        var adaEssai = JSON.parse('<?= json_encode($soal_esai_ada) ?>');


        $(function () {
            bsCustomFileInput.init();
        });
    </script>
    <script src="<?= base_url() ?>/assets/app/js/cbt/banksoal/soal.js?v=<?= time() ?>"></script>

    <!-- AI Generate Modal -->
    <div id="aiGenerateModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; display: none;">
        <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto"
            style="position: relative; padding: 20px; background: #fff; width: 50%; border-radius: 8px; max-height: 90vh; overflow-y: auto;">
            <div class="mt-1">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900" style="margin: 0;">
                        <i class="bi bi-robot text-blue-600 me-2"></i>Generate Soal AI
                    </h3>

                    <button type="button" onclick="closeAIGenerateModal()"
                        class="text-gray-400 hover:text-gray-600 border-0 bg-transparent"
                        style="cursor: pointer; font-size: 1.5rem;">

                    </button>
                </div>
                <div class="alert alert-default-danger mt-2 mb-3" role="alert">
                    <i class="bi bi-info-circle-fill me-1"></i> Fitur ini hanya digunakan untuk menghasilkan soal-soal
                    <b>HOTS
                        (Higher Order Thinking Skills)</b> dengan cepat dan efektif. Untuk soal LOTS (C1-C3) silahkan
                    menggunakan fitur <b>Bank Soal Manual</b>.<br>
                    <i>by ikasmedia</i>
                </div>

                <form id="aiGenerateForm">
                    <input type="hidden" name="bank_id" value="<?= $bank->id_bank ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3 mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jenjang Sekolah</label>
                                <select name="jenjang" id="jenjang" class="form-control">
                                    <option value="SMA">SMA/MA</option>
                                    <option value="SMK">SMK</option>
                                    <option value="SMP">SMP/MTS</option>
                                    <option value="SD">SD/MI</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3 mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kelas</label>
                                <input type="text" name="kelas" id="kelas" class="form-control"
                                    placeholder="Contoh: 1, 7, 10, 12">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Topik/Materi <span class="text-red-600">*</span>
                        </label>
                        <input type="text" name="topic" id="topic" required class="form-control"
                            placeholder="Contoh: Persamaan Linear, Sejarah Indonesia, dll">
                        <p class="text-xs text-gray-500 mt-1">Masukkan topik atau materi yang ingin dibuat soalnya</p>
                    </div>

                    <div class="mb-3 mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Jumlah Soal <span class="text-red-600">*</span>
                        </label>
                        <input type="number" name="jumlah_soal" id="jumlah_soal" required min="1" max="20" value="5"
                            class="form-control">
                        <p class="text-xs text-gray-500 mt-1">Maksimal 20 soal per generate</p>
                    </div>

                    <div class="mb-3 mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tipe Soal <span class="text-red-600">*</span>
                        </label>
                        <select name="tipe_soal" id="tipe_soal" required class="form-control">
                            <option value="1">Pilihan Ganda (PG)</option>
                            <option value="2">Pilihan Ganda Kompleks</option>
                            <option value="3">Menjodohkan</option>
                            <option value="4">Isian Singkat</option>
                            <option value="5">Esai</option>
                            <option value="6">Urutan (Sequencing)</option>
                            <option value="7">Isian Rumpang (Cloze)</option>
                        </select>
                    </div>

                    <div class="mb-3 mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tingkat Kesulitan <span class="text-red-600">*</span>
                        </label>
                        <select name="tingkat_kesulitan" id="tingkat_kesulitan" required class="form-control">
                            <option value="mudah">Mudah</option>
                            <option value="sedang" selected>Sedang</option>
                            <option value="sulit">Sulit</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-end space-x-3 mt-4">
                        <button type="button" onclick="closeAIGenerateModal()" class="btn btn-secondary me-2">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-magic me-2"></i>Generate Soal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAIGenerateModal() {
            document.getElementById('aiGenerateModal').style.display = 'flex';
        }

        function closeAIGenerateModal() {
            document.getElementById('aiGenerateModal').style.display = 'none';
            document.getElementById('aiGenerateForm').reset();
        }

        // Handle AI Generate Form
        document.getElementById('aiGenerateForm').addEventListener('submit', function (e) {
            e.preventDefault();

            // Get CSRF token
            const csrfName = '<?= $this->security->get_csrf_token_name() ?>';
            const csrfHash = '<?= $this->security->get_csrf_hash() ?>';

            const formData = new FormData(this);
            formData.append(csrfName, csrfHash);

            Swal.fire({
                title: 'Membuat Soal...',
                text: 'Sedang generate soal via AI, mohon tunggu...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: base_url + 'cbtbanksoal/generate_ai',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (data) {
                    Swal.close();

                    // Parse if string
                    const response = typeof data === 'string' ? JSON.parse(data) : data;

                    if (response.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            closeAIGenerateModal();
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.message || 'Terjadi kesalahan saat generate soal',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function (xhr, status, error) {
                    Swal.close();
                    let errMsg = 'Terjadi kesalahan sistem';
                    if (xhr.status === 403) {
                        errMsg = 'Akses ditolak (403). Session mungkin kadaluarsa, silakan reload halaman.';
                    } else {
                        errMsg += ': ' + error;
                    }

                    Swal.fire({
                        title: 'Error!',
                        text: errMsg,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });
    </script>