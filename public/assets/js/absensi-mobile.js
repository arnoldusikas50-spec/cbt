/**
 * Attendance Mobile JavaScript
 * Dependencies: jQuery, SweetAlert2
 */

const AbsensiApp = (function () {
    'use strict';

    // Configuration
    let config = {
        baseUrl: '',
        csrfToken: '',
        csrfName: 'csrf_test_name',
        fotoWajib: 0,
        currentMode: 'checkin', // checkin, checkout, done
        isHoliday: false,
        holidayReason: '',
        endpoints: {
            checkin: '/guru/absensi/checkin',
            checkout: '/guru/absensi/checkout',
            history: '/presensi/history',
            myAttendance: '/presensi/my-attendance',
            myCorrections: '/presensi/my-corrections',
            koreksi: '/presensi/koreksi'
        }
    };

    // State
    let state = {
        stream: null,
        latVal: null,
        lngVal: null,
        accuracyVal: null,
        isModelLoaded: false,
        isContextActive: false,
        faceDetected: false
    };

    // Initialize
    function init(options) {
        config = { ...config, ...options };
        bindEvents();
        // Pre-load models if possible
        loadFaceModels();
    }

    async function loadFaceModels() {
        if (state.isModelLoaded) return;

        // PRIORITY: CDN (Since local assets might be missing)
        try {
            await faceapi.nets.tinyFaceDetector.loadFromUri('https://justadudewhohacks.github.io/face-api.js/models');
            console.log("FaceAPI Models Loaded (CDN)");
            state.isModelLoaded = true;
        } catch (e) {
            console.warn("CDN FaceAPI Load Failed, trying local...", e);
            try {
                // Fallback to local
                await faceapi.nets.tinyFaceDetector.loadFromUri(config.baseUrl + '/assets/models');
                console.log("FaceAPI Models Loaded (Local)");
                state.isModelLoaded = true;
            } catch (ex) {
                console.error("All FaceAPI Sources Failed", ex);
            }
        }
    }

    // Bind Events
    function bindEvents() {
        // Tab navigation
        $(document).on('click', '[data-tab]', function () {
            showTab($(this).data('tab'));
        });
    }

    // Tab Management
    function showTab(tab) {
        $('#content-timeline, #content-izin, #content-history, #content-koreksi').addClass('d-none');

        switch (tab) {
            case 'home':
                $('#content-timeline').removeClass('d-none');
                break;
            case 'izin':
                $('#content-izin').removeClass('d-none');
                break;
            case 'history':
                $('#content-history').removeClass('d-none');
                loadHistory();
                break;
            case 'koreksi':
                $('#content-koreksi').removeClass('d-none');
                loadKoreksiData();
                break;
        }
    }

    // GPS Location
    function getLocationPromise() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject(new Error("Geolocation tidak didukung browser ini."));
                return;
            }
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    resolve({
                        lat: pos.coords.latitude,
                        lng: pos.coords.longitude,
                        accuracy: pos.coords.accuracy
                    });
                },
                (err) => {
                    const isHttp = location.protocol !== 'https:' && location.hostname !== 'localhost';
                    if (isHttp) {
                        console.warn("Geolocation Blocked (Insecure): Using Mock Data for Dev");
                        showToast('warning', 'Dev Mode: Lokasi Dummy (HTTP)');
                        resolve({ lat: -6.175110, lng: 106.865036, accuracy: 9999 });
                    } else {
                        reject(err);
                    }
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        });
    }

    // Haversine Formula for Distance (Meters)
    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371e3; // metres
        const φ1 = lat1 * Math.PI / 180; // φ, λ in radians
        const φ2 = lat2 * Math.PI / 180;
        const Δφ = (lat2 - lat1) * Math.PI / 180;
        const Δλ = (lon2 - lon1) * Math.PI / 180;

        const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
            Math.cos(φ1) * Math.cos(φ2) *
            Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

        return R * c; // in metres
    }

    function detectLocation() {
        $('#locText').text('Mendeteksi lokasi...');
        $('#locText').removeClass('text-success text-danger').addClass('text-white');

        getLocationPromise().then(pos => {
            state.latVal = pos.lat;
            state.lngVal = pos.lng;
            state.accuracyVal = pos.accuracy;

            // Start with closest = null
            let closest = null;
            let minDist = Infinity;

            if (config.locations && config.locations.length > 0) {
                config.locations.forEach(loc => {
                    const locLat = parseFloat(loc.lat);
                    const locLng = parseFloat(loc.lng);
                    const radius = parseFloat(loc.radius_meter);

                    const dist = calculateDistance(pos.lat, pos.lng, locLat, locLng);

                    // Always track the closest location
                    if (dist < minDist) {
                        minDist = dist;
                        closest = {
                            name: loc.nama_lokasi,
                            dist: dist,
                            radius: radius,
                            inRange: dist <= radius
                        };
                    }
                });
            }

            if (closest) {
                if (closest.inRange) {
                    // Inside Radius
                    $('#locText').html(`<i class="bi bi-geo-alt-fill text-success"></i> ${closest.name} (${Math.round(closest.dist)}m)`);
                } else {
                    // Outside Radius
                    $('#locText').html(`<i class="bi bi-geo-alt-fill text-danger"></i> Luar Area: ${closest.name} (${Math.round(closest.dist)}m)`);
                }
            } else {
                if (!config.locations || config.locations.length === 0) {
                    // No locations configured at all -> Show Warning
                    $('#locText').html(`<i class="bi bi-exclamation-triangle-fill text-warning"></i> Lokasi Belum Disetting Admin`);
                } else {
                    // Should not happen if logic is correct (closest will always be set if locations > 0)
                    let accText = state.accuracyVal < 50 ? '✓' : `±${Math.round(state.accuracyVal)}m`;
                    $('#locText').text(`${state.latVal.toFixed(5)}, ${state.lngVal.toFixed(5)} ${accText}`);
                }
            }

        }).catch(err => {
            $('#locText').text('Lokasi Gagal');
            console.error(err);
        });
    }

    // Camera Functions
    async function openCamera() {
        if (config.currentMode === 'done') {
            showAlert('info', 'Selesai', 'Anda sudah menyelesaikan absensi hari ini.');
            return;
        }

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            showAlert('error', 'Kamera Tidak Tersedia',
                'Browser Anda tidak mendukung akses kamera atau tidak menggunakan HTTPS.');
            return;
        }

        $('#cameraModal').addClass('show');
        $('#cameraTitle').text(config.currentMode === 'checkin' ? 'Absen Masuk' : 'Absen Pulang');

        state.faceDetected = false;
        $('#facedetect-status').remove();
        $('.camera-controls').before('<div id="facedetect-status" class="text-white text-center mb-2"><small>Memuat deteksi wajah...</small></div>');

        // Ensure models are loaded
        if (!state.isModelLoaded) {
            await loadFaceModels();
        }

        navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
            audio: false
        }).then(stream => {
            state.stream = stream;
            const videoEl = document.getElementById('videoElement');
            videoEl.srcObject = stream;

            detectLocation();

            // Hook for Face Detection
            videoEl.onloadedmetadata = () => {
                videoEl.play();
                startFaceDetection(videoEl);
            };

        }).catch(err => {
            closeCamera();
            showAlert('error', 'Gagal Akses Kamera', err.message);
        });
    }

    function startFaceDetection(video) {
        if (!state.isModelLoaded) {
            $('#facedetect-status').html('<small class="text-warning">Gagal memuat AI Wajah. Mode manual.</small>');
            return;
        }

        const canvas = document.getElementById('canvasElement');
        // displaySize match video logic
        // We need to wait a tick for actual render size
        setTimeout(() => {
            const displaySize = { width: video.clientWidth, height: video.clientHeight };
            faceapi.matchDimensions(canvas, displaySize);
            canvas.style.display = 'block';
            canvas.style.position = 'absolute';
            canvas.style.top = '0';
            canvas.style.left = '0';

            // Clear previous intervals if any
            if (window.faceInterval) clearInterval(window.faceInterval);

            window.faceInterval = setInterval(async () => {
                if (!state.stream) return; // Stop if closed

                const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions());
                const resizedDetections = faceapi.resizeResults(detections, displaySize);

                // Draw
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                faceapi.draw.drawDetections(canvas, resizedDetections);

                if (detections.length > 0) {
                    state.faceDetected = true;
                    $('#facedetect-status').html('<small class="text-success fw-bold"><i class="bi bi-person-check"></i> Wajah Terdeteksi</small>');

                    // Remove BS classes to allow color change
                    $('.btn-capture').removeClass('border-white').css('border-color', '#10b981');
                    $('.btn-capture div').removeClass('bg-white').css('background-color', '#10b981');
                } else {
                    state.faceDetected = false;
                    $('#facedetect-status').html('<small class="text-white-50">Wajah tidak terlihat...</small>');

                    // Restore BS classes
                    $('.btn-capture').addClass('border-white').css('border-color', '');
                    $('.btn-capture div').addClass('bg-white').css('background-color', '');
                }

            }, 200); // 5fps check
        }, 500);
    }

    function closeCamera() {
        if (state.stream) {
            state.stream.getTracks().forEach(track => track.stop());
            state.stream = null;
        }
        if (window.faceInterval) clearInterval(window.faceInterval);

        const canvas = document.getElementById('canvasElement');
        if (canvas) {
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            canvas.style.display = 'none';
        }

        $('#cameraModal').removeClass('show');
    }

    function takeSnapshot() {
        if (state.latVal === null) {
            showAlert('warning', 'Tunggu', 'Sedang mendeteksi lokasi...');
            return;
        }

        // Enforce Face Detection if not Dinas
        // Check checkbox Dinas
        const isDinas = $('#dinasCheck').is(':checked');

        if (config.fotoWajib === 1 && !isDinas && state.isModelLoaded && !state.faceDetected) {
            const Toast = Swal.mixin({
                toast: true,
                position: 'center',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
            Toast.fire({
                icon: 'warning',
                title: 'Wajah tidak terdeteksi!'
            });
            return;
        }

        const video = document.getElementById('videoElement');
        const canvas = document.createElement('canvas'); // Create fresh canvas for snapshot
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);

        const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
        processAttendance(dataUrl);
        closeCamera();
    }

    function confirmNoPhoto() {
        Swal.fire({
            title: config.currentMode === 'checkin' ? 'Absen Masuk?' : 'Absen Pulang?',
            text: 'Lokasi Anda saat ini akan dicatat.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Ya, Absen',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading('Mendeteksi Lokasi...');
                getLocationPromise().then(pos => {
                    state.latVal = pos.lat;
                    state.lngVal = pos.lng;
                    state.accuracyVal = pos.accuracy;
                    processAttendance(null, 0); // Default 0, logic auto-detects
                }).catch(err => {
                    showAlert('error', 'Error', 'Gagal mendapatkan lokasi: ' + err.message);
                });
            }
        });
    }

    function handleScanClick() {
        if (config.currentMode === 'done') {
            showAlert('info', 'Selesai', 'Anda sudah menyelesaikan absensi hari ini.');
            return;
        }

        // Holiday Check
        if (config.isHoliday && config.currentMode === 'checkin') {
            Swal.fire({
                title: 'Konfirmasi Hari Libur',
                html: '<p>' + config.holidayReason + '</p><small class="text-muted">Apakah Anda yakin ingin tetap melakukan absensi?</small>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#f59e0b',
                confirmButtonText: 'Ya, Lanjutkan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    initiateAttendance();
                }
            });
            return;
        }

        initiateAttendance();
    }

    function initiateAttendance() {
        if (config.fotoWajib === 0) {
            confirmNoPhoto();
        } else {
            openCamera();
        }
    }

    // Process Attendance
    function processAttendance(fotoBase64, isDinasExplicit = null) {
        showLoading('Mengirim...');

        const url = config.currentMode === 'checkin'
            ? config.baseUrl + config.endpoints.checkin
            : config.baseUrl + config.endpoints.checkout;

        // Determine isDinas: Explicit arg > Camera Checkbox > Default 0
        let isDinas = 0;
        if (isDinasExplicit !== null) {
            isDinas = isDinasExplicit;
        } else {
            isDinas = $('#dinasCheck').is(':checked') ? 1 : 0;
        }

        const data = {
            lat: state.latVal,
            lng: state.lngVal,
            foto: fotoBase64,
            is_dinas: isDinas,
            accuracy: state.accuracyVal
        };
        data[config.csrfName] = config.csrfToken;

        // --- OFFLINE CHECK ---
        if (!navigator.onLine) {
            // Validate Radius Client-Side BEFORE saving
            if (config.locations && config.locations.length > 0) {
                const isWithinRadius = checkRadiusOffline(state.latVal, state.lngVal, config.locations);
                if (!isWithinRadius) {
                    // Check if DINAS mode (if dinas, ignore radius)
                    if (isDinas !== 1) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal (Offline)',
                            text: 'Posisi Anda di luar jangkauan lokasi kantor. Karena sedang offline, validasi dilakukan di perangkat.',
                            footer: 'Pastikan GPS akurat atau gunakan Mode Dinas jika tugas luar.'
                        });
                        return;
                    }
                }
            }

            saveToQueue(url, data);
            return;
        }
        // ---------------------

        $.post(url, data, function (res) {
            if (res.status) {
                playSuccessSound();

                // If Dinas, keep alert open so they see "Menunggu Verifikasi"
                let swalTimer = 2000;
                let swalShowConfirm = false;

                if (isDinas === 1) {
                    swalTimer = undefined; // No auto close
                    swalShowConfirm = true; // Show OK button
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: res.message,
                    timer: swalTimer,
                    showConfirmButton: swalShowConfirm
                }).then(() => {
                    location.reload();
                });
            } else {
                // Check if error is related to Geofence/Location
                if (res.message && (res.message.toLowerCase().includes('luar area') || res.message.toLowerCase().includes('dinas'))) {
                    Swal.fire({
                        title: 'Di Luar Jangkauan',
                        text: "Anda berada di luar lokasi kantor. Apakah Anda ingin absen menggunakan Mode Dinas?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3b82f6',
                        confirmButtonText: 'Ya, Absen Dinas',
                        cancelButtonText: 'Batal'
                    }).then((retryResult) => {
                        if (retryResult.isConfirmed) {
                            processAttendance(fotoBase64, 1); // Retry with isDinas = 1
                        }
                    });
                } else if (res.message && res.message.toLowerCase().includes('belum waktunya')) {
                    // Handle Early Leave
                    Swal.fire({
                        title: 'Pulang Cepat?',
                        text: "Anda melakukan absen checkout sebelum waktunya. Apakah ini Izin/Pulang Cepat?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#e11d48',
                        confirmButtonText: 'Ya, Izin Pulang',
                        cancelButtonText: 'Batal'
                    }).then((retryResult) => {
                        if (retryResult.isConfirmed) {
                            processAttendance(fotoBase64, 2); // 2 = CheckOut Early Signal
                        }
                    });
                } else {
                    showAlert('error', 'Gagal', res.message);
                }
            }
        }, 'json').fail(function (xhr, status, error) {
            // Check for network error (status 0) or timeout to save to queue
            if (xhr.status === 0 || xhr.readyState === 0) {
                // Should we validate radius here too? 
                // Yes, duplicate validation safety or assume initial GPS check passed?
                // Let's validate to be safe.
                if (isDinas !== 1 && config.locations && config.locations.length > 0) {
                    if (!checkRadiusOffline(state.latVal, state.lngVal, config.locations)) {
                        showAlert('error', 'Gagal', 'Terjadi kesalahan jaringan & Lokasi Anda terdeteksi di luar jangkauan (Validasi Offline).');
                        return;
                    }
                }

                saveToQueue(url, data);
            } else {
                showAlert('error', 'Error', 'Terjadi kesalahan jaringan: ' + xhr.status + ' ' + xhr.statusText);
            }
        });
    }

    // Haversine Formula for Offline Validation
    function checkRadiusOffline(lat1, lon1, locations) {
        // If no locations defined, assume everywhere is valid (or strict? let's be loose for safety)
        if (!locations || locations.length === 0) return true;

        const R = 6371e3; // metres
        const rad = Math.PI / 180;

        for (let loc of locations) {
            const lat2 = parseFloat(loc.lat);
            const lon2 = parseFloat(loc.lng);
            const allowedRadius = parseFloat(loc.radius_meter);

            const φ1 = lat1 * rad;
            const φ2 = lat2 * rad;
            const Δφ = (lat2 - lat1) * rad;
            const Δλ = (lon2 - lon1) * rad;

            const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
                Math.cos(φ1) * Math.cos(φ2) *
                Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

            const d = R * c; // in metres

            if (d <= allowedRadius) {
                return true; // Found at least one valid location
            }
        }

        return false; // No location matched
    }

    // OFFLINE QUEUE UTILS
    function getFormattedDate() {
        const now = new Date();
        const pad = n => n < 10 ? '0' + n : n;
        return now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate()) + ' ' +
            pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
    }

    function saveToQueue(url, data) {
        // Inject timestamp so server knows when this happened
        data.timestamp = getFormattedDate();

        const queueItem = {
            url: url,
            data: data,
            created: Date.now(),
            userId: config.userId // Security Binding
        };

        let queue = JSON.parse(localStorage.getItem('attendance_queue') || '[]');
        queue.push(queueItem);
        localStorage.setItem('attendance_queue', JSON.stringify(queue));

        playSuccessSound(); // Give feedback as if success
        Swal.fire({
            icon: 'info',
            title: 'Tersimpan Offline',
            text: 'Koneksi bermasalah. Data tersimpan di HP dan akan dikirim otomatis saat online.',
            confirmButtonText: 'Ok, Mengerti'
        }).then(() => {
            closeCamera();
        });
    }

    function processQueue() {
        if (!navigator.onLine) return;

        let queue = JSON.parse(localStorage.getItem('attendance_queue') || '[]');
        if (queue.length === 0) return;

        // Separate items for current user and others
        let currentUserItems = [];
        let otherUserItems = [];

        queue.forEach(item => {
            // Assume strict matching
            if (item.userId && String(item.userId) === String(config.userId)) {
                currentUserItems.push(item);
            } else {
                otherUserItems.push(item);
            }
        });

        if (currentUserItems.length === 0) {
            // Nothing to sync for THIS user
            if (otherUserItems.length > 0) {
                console.log('Pending offline data exists for other users (' + otherUserItems.length + ').');
            }
            return;
        }

        console.log(`Processing ${currentUserItems.length} items for User ${config.userId}`);

        // Process ONLY the first item of THIS user
        const item = currentUserItems[0];

        $.post(item.url, item.data, function (res) {
            if (res.status) {
                console.log('Queue item synced:', res.message);

                // Re-read fresh queue from storage in case it changed during async
                let freshQueue = JSON.parse(localStorage.getItem('attendance_queue') || '[]');

                // Remove ONLY the specific item we just synced (by created timestamp)
                let updatedQueue = freshQueue.filter(q => q.created !== item.created);
                localStorage.setItem('attendance_queue', JSON.stringify(updatedQueue));

                // Recurse to process next item for this user
                setTimeout(processQueue, 1000);
            } else {
                console.warn('Server rejected offline item:', res.message);
                // If server rejects (e.g. data invalid), we MUST remove it to avoid infinite loop
                let freshQueue = JSON.parse(localStorage.getItem('attendance_queue') || '[]');
                let updatedQueue = freshQueue.filter(q => q.created !== item.created);
                localStorage.setItem('attendance_queue', JSON.stringify(updatedQueue));

                showToast('error', 'Gagal Sync: ' + res.message);
                setTimeout(processQueue, 1000);
            }
        }, 'json').fail(function () {
            // Network fail - Do nothing. Item remains in storage.
            console.log('Sync failed (Network), retrying later...');
        });
    }

    // Listen for online status
    window.addEventListener('online', processQueue);

    // Also check on init
    $(document).ready(function () {
        if (navigator.onLine) {
            setTimeout(processQueue, 3000); // Wait a bit after load
        }
    });

    // History Functions
    function loadHistory() {
        $('#history-loading').removeClass('d-none');
        $('#history-data').addClass('d-none');

        $.get(config.baseUrl + config.endpoints.history, function (res) {
            $('#history-loading').addClass('d-none');
            if (!res.status) {
                $('#history-list').html('<p class="text-center text-muted">Gagal memuat data.</p>');
                $('#history-data').removeClass('d-none');
                return;
            }

            $('#history-data').removeClass('d-none');
            const stats = res.data.stats;
            const records = res.data.week;

            // Render Stats
            renderStats(stats);

            // Render Calendar
            if (res.data.calendar && res.data.calendar.length > 0) {
                renderCalendar(res.data.calendar);
            }

            // Render List
            renderHistoryList(records);
        });
    }

    function renderStats(stats) {
        const statsHtml = `
        <div class="row g-2">
            <div class="col-6">
                <div class="stat-card primary">
                    <div class="text-muted small mb-1">Kehadiran (Bln Ini)</div>
                    <div class="d-flex align-items-baseline gap-1">
                        <div class="fw-bold fs-3">${stats.percentage}%</div>
                        <small class="text-muted ms-auto">${stats.hadir} Hari</small>
                    </div>
                    <div class="progress mt-2" style="height: 4px; background-color: rgba(0,0,0,0.05);">
                        <div class="progress-bar" role="progressbar" style="width: ${stats.percentage}%; background-color: #e11d48;" aria-valuenow="${stats.percentage}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="stat-card warning">
                    <div class="text-muted small mb-1">Terlambat</div>
                    <div class="fw-bold fs-3 d-flex align-items-center gap-1">
                        ${stats.telat_kali}x 
                        ${(stats.telat_menit > 0) ? `<span class="badge bg-danger rounded-pill fw-normal" style="font-size: 0.6rem">${stats.telat_menit}m</span>` : ''}
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="stat-card info">
                    <div class="text-muted small mb-1">Dinas Luar</div>
                    <div class="fw-bold fs-3">${stats.dinas} <span class="fs-6 text-muted fw-normal">Hari</span></div>
                </div>
            </div>
            <div class="col-6">
                <div class="stat-card success">
                    <div class="text-muted small mb-1">Izin / Cuti</div>
                    <div class="fw-bold fs-3">${stats.izin_hari} <span class="fs-6 text-muted fw-normal">Hari</span></div>
                </div>
            </div>
        </div>`;
        $('#history-stats').html(statsHtml);
    }

    function renderCalendar(calendarData) {
        const today = new Date().toISOString().split('T')[0];
        const dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

        let html = '';
        dayNames.forEach(d => {
            html += `<div class="cal-day-header">${d}</div>`;
        });

        if (calendarData.length > 0) {
            const firstDate = new Date(calendarData[0].date);
            const firstDayOfWeek = firstDate.getDay();

            for (let i = 0; i < firstDayOfWeek; i++) {
                html += '<div class="cal-day" style="background:transparent"></div>';
            }

            calendarData.forEach(day => {
                const isToday = day.date === today ? 'today' : '';
                const textColor = day.status === 'future' || day.status === 'none' ? 'color:#6b7280' : '';
                html += `<div class="cal-day ${isToday}" style="background:${day.color};${textColor}" title="${day.date} - ${day.status}">${day.day}</div>`;
            });
        }

        $('#history-calendar').html(html);
    }

    function renderHistoryList(records) {
        let listHtml = '';
        if (records.length === 0) {
            listHtml = '<div class="text-center py-3 text-muted">Belum ada riwayat minggu ini.</div>';
        } else {
            records.forEach(r => {
                let badge = 'bg-success';
                let text = 'Tepat Waktu';
                if (r.status_masuk === 'LATE') {
                    badge = 'bg-warning text-dark';
                    text = 'Terlambat ' + r.telat_menit + 'm';
                }
                if (r.status_masuk && r.status_masuk.includes('DINAS')) {
                    badge = 'bg-info text-white';
                    text = 'Dinas';
                }

                listHtml += `
                <div class="activity-card">
                    <div class="time-dot"></div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between">
                            <div class="fw-bold fs-6">${r.tgl}</div>
                            <span class="badge ${badge} rounded-pill" style="font-size:0.7rem">${text}</span>
                        </div>
                        <div class="text-muted small mt-1">
                            Masuk: ${r.jam_masuk ? r.jam_masuk.split(' ')[1].substr(0, 5) : '-'} 
                            | Pulang: ${r.jam_pulang ? r.jam_pulang.split(' ')[1].substr(0, 5) : '-'}
                        </div>
                    </div>
                </div>`;
            });
        }
        $('#history-list').html(listHtml);
    }

    // Koreksi Functions
    function loadKoreksiData() {
        // Load attendance for selection
        $.get(config.baseUrl + config.endpoints.myAttendance, function (res) {
            if (res.status && res.data) {
                const today = new Date().toLocaleDateString('en-CA'); // YYYY-MM-DD
                let options = '<option value="">-- Pilih Tanggal --</option>';
                res.data.forEach(r => {
                    let label = `${r.tgl} - Masuk: ${r.jam_masuk ? r.jam_masuk.split(' ')[1] : '-'}`;
                    let disabled = '';

                    if (r.tgl === today) {
                        label += ' ℹ️ Hari Ini';
                    }

                    if (r.has_correction) {
                        label += ' (Sudah Dikoreksi)';
                        disabled = 'disabled';
                    }

                    options += `<option value="${r.id}" ${disabled}>${label}</option>`;
                });
                $('#koreksiAttendanceId').html(options);
            }
        });

        // Load corrections history
        $.get(config.baseUrl + config.endpoints.myCorrections, function (res) {
            if (res.status && res.data && res.data.length > 0) {
                let html = '';
                res.data.forEach(c => {
                    let badgeClass = c.status === 'approved' ? 'bg-success' : (c.status === 'rejected' ? 'bg-danger' : 'bg-warning text-dark');
                    let dotColor = c.status === 'approved' ? '#10b981' : (c.status === 'rejected' ? '#ef4444' : '#f59e0b');
                    html += `
                    <div class="activity-card">
                        <div class="time-dot" style="background:${dotColor}"></div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-bold">${c.tgl}</div>
                                    <small class="text-muted">${c.field_name}: ${c.old_value || '-'} → ${c.new_value}</small>
                                </div>
                                <span class="badge ${badgeClass} rounded-pill">${c.status}</span>
                            </div>
                        </div>
                    </div>`;
                });
                $('#my-corrections-list').html(html);
            } else {
                $('#my-corrections-list').html('<div class="text-center text-muted py-3">Belum ada riwayat koreksi.</div>');
            }
        });
    }

    function submitKoreksi() {
        const form = $('#formKoreksi');
        const attendanceId = $('#koreksiAttendanceId').val();
        const fieldName = form.find('[name="field_name"]').val();
        const newValue = form.find('[name="new_value"]').val();
        const reason = form.find('[name="reason"]').val();

        if (!attendanceId || !fieldName || !newValue || !reason) {
            showAlert('error', 'Error', 'Semua field harus diisi');
            return;
        }

        showLoading('Mengirim...');

        const data = {
            attendance_id: attendanceId,
            field_name: fieldName,
            new_value: newValue,
            reason: reason
        };
        data[config.csrfName] = config.csrfToken;

        $.post(config.baseUrl + config.endpoints.koreksi, data, function (res) {
            if (res.status) {
                showAlert('success', 'Berhasil', res.message);
                form[0].reset();
                loadKoreksiData();
            } else {
                showAlert('error', 'Gagal', res.message);
            }
        }, 'json');
    }

    // Izin Functions
    function submitIzin() {
        const form = document.getElementById('formIzin');
        const formData = new FormData(form);
        formData.append(config.csrfName, config.csrfToken);

        showLoading('Mengirim...');

        $.ajax({
            url: config.baseUrl + '/presensi/izin',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (res) {
                if (res.status) {
                    showAlert('success', 'Berhasil', res.message);
                    form.reset();
                    showTab('home');
                } else {
                    showAlert('error', 'Gagal', res.message);
                }
            },
            error: function () {
                showAlert('error', 'Error', 'Terjadi kesalahan jaringan');
            }
        });
    }

    // Utility Functions
    function playSuccessSound() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;

            const ctx = new AudioContext();
            const now = ctx.currentTime;

            // Simple success chime (C5 - E5 - G5)
            [523.25, 659.25, 783.99].forEach((freq, i) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.type = 'sine';
                osc.frequency.setValueAtTime(freq, now + (i * 0.1));

                gain.gain.setValueAtTime(0.05, now + (i * 0.1));
                gain.gain.exponentialRampToValueAtTime(0.001, now + (i * 0.1) + 0.5);

                osc.start(now + (i * 0.1));
                osc.stop(now + (i * 0.1) + 0.5);
            });
        } catch (e) {
            console.error('Audio play failed', e);
        }
    }

    function showAlert(icon, title, text) {
        Swal.fire({ icon, title, text });
    }

    function showToast(icon, title) {
        Swal.fire({
            toast: true,
            position: 'top',
            icon: icon,
            title: title,
            timer: 3000,
            showConfirmButton: false
        });
    }

    function showLoading(title) {
        Swal.fire({
            title: title || 'Loading...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
    }

    // Public API
    return {
        init: init,
        showTab: showTab,
        openCamera: openCamera,
        closeCamera: closeCamera,
        takeSnapshot: takeSnapshot,
        handleScanClick: handleScanClick,
        detectLocation: detectLocation,
        submitIzin: submitIzin,
        submitKoreksi: submitKoreksi,
        loadHistory: loadHistory,
        loadKoreksiData: loadKoreksiData,
        playSuccessSound: playSuccessSound
    };
})();

// Expose to global for onclick handlers
window.showTab = function (tab) { AbsensiApp.showTab(tab); };
window.openCamera = function () { AbsensiApp.openCamera(); };
window.closeCamera = function () { AbsensiApp.closeCamera(); };
window.takeSnapshot = function () { AbsensiApp.takeSnapshot(); };
window.handleScanClick = function () { AbsensiApp.handleScanClick(); };
window.detectLocation = function () { AbsensiApp.detectLocation(); };
window.submitIzin = function () { AbsensiApp.submitIzin(); };
window.submitKoreksi = function () { AbsensiApp.submitKoreksi(); };
