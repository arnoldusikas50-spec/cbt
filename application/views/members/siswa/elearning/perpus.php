<div class="content-wrapper" style="margin-top: 30px;">
    <div class="sticky">
    </div>
    <section class="content overlap p-0">
        <div class="container">
            <div class="row">
                <div class="col-12">
                   <!-- <?php $this->load->view('members/siswa/templates/top'); ?> --> <!-- ikasmedia -->
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="prev-container my-shadow">
                        <div class="prev-header">
                            <h3>Perpustakaan Digital</h3>
                            <div class="d-flex flex-row">
                                <label class="align-self-center my-0 mr-2"><i class="fa fa-search"></i></label>
                                <input id="input-search" type="search" class="form-control form-control-sm" placeholder="" aria-controls="users">
                            </div>
                        </div>
                        <ul class="prev-tab-list" id="tab-list">
                            <?php if (count($pustaka) > 0):
                                foreach ($pustaka as $key=>$lib) : ?>
                                    <li class="prev-tab-item">
                                        <div class="prev-tab-icon icon-tugas-belajar">
                                            <img src="https://www.google.com/s2/favicons?domain=<?=$lib->url?>"
                                                 alt="Tugas Belajar Icon">
                                        </div>
                                        <div class="prev-tab-content">
                                            <div class="prev-title"><?= $lib->title ?? $lib->siteName ?></div>
                                            <div class="prev-url-info">
                                                <div class="prev-desc"><?= $lib->description ?></div>
                                                <span class="prev-domain text-xs"><?= $lib->url ?></span>
                                                <span class="btn btn-xs btn-link"
                                                      onclick="window.open('<?=$lib->url?>', '_blank');">
                                                    <i class="fa fa-globe-americas"></i><span class="text-xs"> Kunjungi Web</span>
                                                </span>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="prev-tab-item alert-default-warning">Belum ada link perpustakaan</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript" src="<?= base_url() ?>/assets/plugins/multiselect/js/jquery.quicksearch.js"></script>
<script>
    let cur_info = {};
    $(document).ready(function () {
        $('#input-search').quicksearch('#tab-list li');
    })
</script>
