<!-- Modal Komentar -->
<div class="modal fade" id="komentarModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            <div class="modal-header border-0 pb-2">
                <h5 class="modal-title fw-bold ps-2">
                    <i class="bi bi-chat-dots-fill me-2 text-primary"></i>Tulis Komentar
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">
                <form id="komentar">
                    <input type="hidden" id="id-post" name="id_post" value="">
                    <div class="d-flex align-items-start">
                        <div class="me-3 text-center">
                            <img class="img-fluid rounded-circle shadow-sm border"
                                src="<?= base_url('assets/img/logo.png') ?>" alt="Avatar"
                                style="width: 45px; height: 45px; object-fit: cover;" onerror="this.src='<?= base_url('assets/img/logo.png') ?>'">
                        </div>
                        <div class="w-100">
                            <textarea name="text" class="form-control bg-light border-0 text-sm" rows="3"
                                placeholder="Tulis komentar Anda di sini..." required
                                style="resize: none; border-radius: 0.8rem; padding: 12px;"></textarea>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3 pt-2 border-top">
                        <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">
                            <i class="bi bi-x me-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                            <i class="bi bi-send-fill me-2"></i>Komentari
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Balasan -->
<div class="modal fade" id="balasanModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            <div class="modal-header border-0 pb-2">
                <h5 class="modal-title fw-bold ps-2">
                    <i class="bi bi-reply-fill me-2 text-primary"></i>Tulis Balasan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">
                <form id="balasan">
                    <input type="hidden" id="id-comment" name="id_comment" value="">
                    <div class="d-flex align-items-start">
                        <div class="me-3 text-center">
                            <img class="img-fluid rounded-circle shadow-sm border"
                                src="<?= base_url('assets/img/logo.png') ?>" alt="Avatar"
                                style="width: 45px; height: 45px; object-fit: cover;" onerror="this.src='<?= base_url('assets/img/logo.png') ?>'">
                        </div>
                        <div class="w-100">
                            <textarea name="text" class="form-control bg-light border-0 text-sm" rows="3"
                                placeholder="Tulis balasan Anda..." required
                                style="resize: none; border-radius: 0.8rem; padding: 12px;"></textarea>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3 pt-2 border-top">
                        <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">
                            <i class="bi bi-x me-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                            <i class="bi bi-reply-fill me-2"></i>Balas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>