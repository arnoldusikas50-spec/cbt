<?php
$jumlahSiswa = $kelas->jumlah_siswa ?? [];
$jjs = [];
foreach ($jumlahSiswa as $js) {
    $jjs[] = $js->id;
}
?>
<div class="content-wrapper bg-white pt-4">


    <section class="content">
        <div class="container-fluid">
            <?= form_open('', array('id' => 'create')) ?>
            <div class="card my-shadow">
                <div class="card-header">
                    <div class="card-title">
                        <h6>Tambah Kelas</h6>
                    </div>
                    <div class="card-tools">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa fa-save mr-1"></i>Simpan
                        </button>
                    <a href="<?= base_url('datakelas') ?>" type="button" class="btn btn-sm btn-danger float-right ml-2">
                        <i class="fas fa-arrow-circle-left"></i><span
                                class="d-none d-sm-inline-block ml-1">Kembali</span>
                    </a>   
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group row">
                                <?php
                                if (isset($id_kelas)) echo form_hidden('id_kelas', $id_kelas);
                                ?>
                                <label for="nama_kelas" class="col-md-3 col-form-label">Nama Kelas</label>
                                <div class="col-md-9">
                                    <?php
                                    $siswas = [];
                                    if (isset($siswa)) {
                                        foreach ($siswa as $key => $row) {
                                            if ($row->id_kelas == null || $row->id_kelas == 0) {
                                                $siswas [$row->id_siswa] = $row->nama .' - '.$row->nis;
                                            }
                                        }
                                    }
                                    if (isset($siswakelas)) {
                                        foreach ($siswakelas as $key => $row) {
                                            $siswas [$row->id_siswa] = $row->nama .' - '.$row->nis;
                                        }
                                    }

                                    echo form_input(
                                        array(
                                            'name' => 'nama_kelas',
                                            'value' => $kelas->nama_kelas,
                                            'id' => 'nama_kelas',
                                            'type' => 'text',
                                            'class' => 'form-control',
                                            'placeholder' => 'Nama Kelas',
                                            'required' => 'required'
                                        )
                                    );
                                    //var_dump($siswas);
                                    ?>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 control-label">Kode Kelas</label>
                                <div class="col-md-9">
                                    <input type='text' name='kode_kelas' class='form-control'
                                           value="<?= $kelas->kode_kelas ?>" required/>
                                </div>
                            </div>
                            <?php if ($setting->jenjang == '3') : ?>
                                <div class="form-group row">
                                    <label for="jurusan_id" class="col-md-3 control-label">Jurusan</label>
                                    <div class="col-md-9">
                                        <?php
                                        echo form_dropdown(
                                            'jurusan_id',
                                            $jurusan,
                                            $kelas->jurusan_id,
                                            'class="select2 form-control" id="jurusan_id" required'
                                        );
                                        ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label for="level_id" class="col-md-3 control-label">Level</label>
                                <div class="col-md-9">
                                    <?php
                                    echo form_dropdown(
                                        'level_id',
                                        $level,
                                        $kelas->level_id,
                                        'class="select2 form-control"  id="level_id" required'
                                    );
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card border">
                                <div class="card-body">
                                    <div class="form-group row">
                                        <label for="jumlah_siswa" class="col-md-12 control-label">Daftar Siswa</label>
                                        <div class="col-md-12">
                                            <select name="siswa[]" class="form-control" id="jumlah_siswa" multiple="multiple" style="position: absolute; left: -9999px;">
                                            <?php foreach ($siswas as $id=>$siswa) :?>
                                            <option value="<?= $id ?>" <?= in_array($id, $jjs) ? 'selected="selected"' : '';?>><?=$siswa?></option>
                                            <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- <div class="col-md-4">
                            <div class="card border">
                                <div class="card-body">
                                    <div class="form-group row">
                                        <label for="siswa_id" class="control-label">Ketua Kelas</label>
                                        <?php
                                        echo form_dropdown(
                                            'siswa_id',
                                            $siswas,
                                            $kelas->siswa_id,
                                            'class="select2 form-control" id="siswa_id"'
                                        );
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        -->
                    </div>
                </div>
            </div>
            <?= form_close() ?>
        </div>
    </section>
</div>

<script>
    const arrAllSiswa = JSON.parse(JSON.stringify(<?= json_encode($siswas)?>));
    const arrSelSiswa = JSON.parse(JSON.stringify(<?= json_encode($jjs)?>));
    $(document).ready(function () {
        $("#guru_id option:first").attr('disabled', 'disabled');
        $("#jurusan_id option:first").attr('disabled', 'disabled');
        $("#level_id option:first").attr('disabled', 'disabled');
    });
</script>
<script src="<?= base_url() ?>/assets/app/js/master/kelas/add.js"></script>
