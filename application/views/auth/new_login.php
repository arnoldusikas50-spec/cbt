<div class="container pt-0">
    <div class="info-box bg-transparent shadow-none">
    </div>

    <div class="slideshow-container"> <!-- hiren -->
        <div class="slide" style="background-image: url('<?= base_url("/assets/img/gambar1.jpg") ?>');"></div>
        <div class="slide" style="background-image: url('<?= base_url("/assets/img/gambar2.jpg") ?>');"></div>
        <div class="slide" style="background-image: url('<?= base_url("/assets/img/gambar3.jpg") ?>');"></div>
    </div>

    <div class="container-fluid h-100">
        <div class="row h-100 justify-content-center">
            <div class="login-box">						
						<!-- hiren -->
                         <br>
            <div class="card form-signin">
            <div class="card-body">
                        <p class="login-box-msg">
                        <?php
        // $logo_app = $setting->logo_kanan == null ? base_url() . 'assets/img/icongaruda.gif' : base_url() . $setting->logo_kiri; // for php 7.4 & php 8.3.x
        $logo_app = isset($setting->logo_kiri) && $setting->logo_kiri != null && file_exists($setting->logo_kiri) ? base_url() . $setting->logo_kiri : base_url() . 'assets/img/icongaruda.gif';
        ?>
        <img src="<?= $logo_app ?>" width="70" height="70">
                            <br>
                            <strong><font color='darkcyan'><?= $login_title ?? 'E-LEARNING & CBT' ?></font></strong><br><center> <marquee width="60%" scrolldelay="300" onmouseover="this.stop()" onmouseout="this.start()"> <strong><b><small class="form-text text-muted "><font color='red'><?= $setting->sekolah ?> </font> | <font color='blue'> <?= $setting->alamat ?> </font></small></b></strong> </marquee></center></p>
                        <div id="infoMessage"><?php echo $message; ?></div>
                        <!-- hiren -->

                        <?= form_open("auth/login", array('id' => 'login')); ?>
                        <div class="input-group mb-3 has-feedback">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <span class="fas fa-user" style="color:green"></span>
                                </div>
                            </div>
                            <input type="text" name="username" value="" id="identity" placeholder="Username" autofocus="autofocus" class="form-control" autocomplete="off" required="" style="color:green">
                            <div class="help-block"></div>
                        </div>
                        <div class="input-group mb-3 has-feedback">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <span class="fas fa-lock" style="color:darkorange"></span>
                                </div>
                            </div>
                            <input type="password" name="password" value="" id="password" placeholder="Password" class="form-control" required="" style="color:darkorange">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span id="toggle-password" class="fas fa-eye-slash" style="cursor: pointer"></span>
                                </div>
                            </div>
                            <div class="help-block"></div>
                        </div>
                        <div class="row">
                            <div class="col-8">
                                <?php if (!empty($show_mode_selector)): ?>
                                    <select class="form-control text-purple" id="cbt-only" name="cbt-only">
                                        <option class="alert alert-default-danger" value="1">Login CBT</option>
                                        <option class="alert alert-default-success" value="0">Login Elearning & Nilai</option>
                                    </select>
                                <?php else: ?>
                                    <input type="hidden" id="cbt-only" name="cbt-only" value="<?= $cbt_only_default ?? '1' ?>">
                                    <small class="form-text text-muted">Mode: CBT</small>
                                <?php endif; ?>
                            </div>
                            <div class="col-4">
                                <?= form_submit('submit', 'Login', array('id' => 'submit', 'class' => 'btn btn-primary btn-block btn-flat')); ?>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                    <center><small class="form-text text-muted"> <strong>Commit: <?= APP_COMMIT ?> | PHP: <?php print phpversion(); ?></small> </strong></center> <!-- hiren -->
                </div>

            </div>
        </div>
    </div>

</div>

<script type="text/javascript">
    let base_url = '<?=base_url();?>';
    const slides = document.querySelectorAll('.slide');
    let currentSlide = 0;

    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.classList.remove('active');
            if (i === index) {
                slide.classList.add('active');
            }
        });
    }

    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }

    // Show the first slide initially
    showSlide(currentSlide);

    // Change slides every 5 seconds
    setInterval(nextSlide, 10000);

    $(document).ready(function(){
        const isLogin = localStorage.getItem('garudaCBT.login')
        $('#cbt-only').val(isLogin || '1')

        $('form#login input').on('change', function(){
            $(this).parent().removeClass('has-error');
            $(this).next().next().text('');
        });

        $('form#login').on('submit', function(e){
            e.preventDefault();
            e.stopImmediatePropagation();

            var infobox = $('#infoMessage');
            infobox.addClass('info-box align-items-center justify-content-center bg-gradient-info').text('Checking...');

            var btnsubmit = $('#submit');
            btnsubmit.attr('disabled', 'disabled').val('Wait...');

            const cbtOnly = $('#cbt-only').val()
            localStorage.setItem('garudaCBT.login', cbtOnly)

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                success: function(data){
                    infobox.removeAttr('class').text('');
                    btnsubmit.removeAttr('disabled').val('Login');
                    console.log('login', data);
                    const payload = data.data || {};
                    const errors = data.errors || {};
                    if(data.status){
                        infobox.addClass('info-box align-items-center justify-content-center bg-gradient-success').text(data.message || 'Login Sukses');
                        const go = payload.redirect || (base_url + (payload.url || 'dashboard'));
                        window.location.href = go;
                    } else {
                        infobox.addClass('info-box align-items-center justify-content-center bg-gradient-danger').text(data.message || 'Login gagal');
                        if(Object.keys(errors).length){
                            $.each(errors, function(key, val){
                                const input = $('[name="'+key+'"');
                                input.parent().addClass('has-error');
                                input.next().next().text(val);
                                if(val === ''){
                                    input.parent().removeClass('has-error');
                                    input.next().next().text('');
                                }
                            });
                        }
                    }
                }
            });
        });

        $('#toggle-password').on('click', function (e) {
            // toggle the type attribute
            const type = $('#password').attr('type') === 'password' ? 'text' : 'password';
            $('#password').attr('type', type);
            // toggle the eye / eye slash icon
            $(this).toggleClass('fa-eye-slash fa-eye');
        });
    });
</script>