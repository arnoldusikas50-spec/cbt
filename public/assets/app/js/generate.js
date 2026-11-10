const cur_url = window.location.href;

if (!localStorage.getItem('garudaCBT.ada_jadwal_ujian') && cur_url.indexOf('dashboard') === -1) {
    window.location.href = base_url + '/dashboard';
} else {
    adaJadwalUjian = localStorage.getItem('garudaCBT.ada_jadwal_ujian');
}

let timerToken, timerUpdate;
let timerTokenRemaining, timerTokenOnGoing;
let forceGenerate = 0;
let timeOffset;

$(document).ready(function () {
    // cek token
    loadTokenFromServer();
});

function loadTokenFromServer() {
    $.ajax({
        url: base_url + "cbttoken/loadtoken",
        type: "GET",
        success: function (response) {
            globalToken = response;
            localStorage.setItem('garudaCBT.token', JSON.stringify(response));
            //console.log('global', globalToken);
            createIntervalToken();
        },
        error: function (xhr, status, error) {
            console.log(xhr);
        }
    });
}

function createIntervalToken() {
    if (timerToken) {
        clearTimeout(timerToken);
        timerToken = null;
    }

    let durasi = Number(globalToken.jarak);
    if (isNaN(durasi) || durasi < 1) durasi = 1; // Safeguard against 0 or negative intervals
    
    const perdetik = 1000;
    const permenit = 60 * perdetik;
    const perjam = 60 * permenit;
    const t_dur = durasi * (1000 * 60);

    // Fix NaN issue by replacing dashes with slashes for better browser compatibility
    let updatedStr = globalToken.updated ? globalToken.updated.replace(/-/g, "/") : "";
    let nowStr = globalToken.now ? globalToken.now.replace(/-/g, "/") : "";

    let updated = new Date(updatedStr);
    let time_c = new Date();
    let time_s = new Date(nowStr);

    // Fallback if parsing fails
    if (isNaN(updated.getTime())) updated = new Date();
    if (isNaN(time_s.getTime())) time_s = new Date();

    let diff = time_s - time_c;

    updateViews();

    function updateViews() {
        // Calculate time diff
        let now = new Date();
        now.setMilliseconds(now.getMilliseconds() + diff);
        var rem = now.getTime() - updated.getTime();
        const t_remaining = t_dur - rem;

        let shouldContinue = false;

        // Logic Calculation
        // Jika Auto=1 DAN (Ada Jadwal Ujian ATAU Kita sedang di halaman token-view)
        const isTokenPage = $('#token-view').length > 0;

        if (globalToken.auto == '1' && (adaJadwalUjian != '0' || isTokenPage)) {
            if (t_remaining < 0) {
                timerTokenRemaining = 'Membuat token...';
                generateToken();
                return; // Stop here, generateToken will restart the process
            } else {
                const r_jam = Math.floor(t_remaining / perjam);
                const r_mnt = Math.floor((t_remaining % perjam) / permenit);
                const r_dtk = Math.floor((t_remaining % permenit) / perdetik);

                timerTokenRemaining = zeroPad(r_jam) + ':' + zeroPad(r_mnt) + ':' + zeroPad(r_dtk);
                shouldContinue = true;
            }
        } else {
            // If manual, maybe show nothing or text
            timerTokenRemaining = "-- : -- : --";
        }

        // --- UPDATE UI SECTION ---
        // Updated logic: Render AFTER calculation

        const viewTimer = $('#interval');
        const infoTimer = $('#info-interval');

        if (globalToken.auto == '1' && (adaJadwalUjian != '0' || isTokenPage)) {
            if (infoTimer.length) infoTimer.removeClass('d-none');
            if (viewTimer.length) {
                viewTimer.removeClass('d-none');
                viewTimer.html(timerTokenRemaining);
            }
        } else {
            if (infoTimer.length) infoTimer.addClass('d-none');
            // if (viewTimer.length) viewTimer.addClass('d-none'); 
        }

        const inputToken = $('#token-input');
        if (inputToken.length) inputToken.val(globalToken.token);

        const viewToken = $('#token-view');
        if (viewToken.length) viewToken.text(globalToken.token);

        // Recursive Loop
        if (shouldContinue) {
            timerToken = setTimeout(updateViews, 1000);
        }
    }
}

function zeroPad(no) {
    return no < 10 ? '0' + no : no;
}

let isRequesting = false;
let lastGenerateTime = 0;

function generateToken() {
    // Safety check: Prevent spamming server (Throttling)
    const now = Date.now();
    if (isRequesting || (now - lastGenerateTime < 5000)) {
        console.log("Throttling generate request...");
        // If throttled, force restart view update loop slowly instead of recursion death
        if (!timerToken) timerToken = setTimeout(createIntervalToken, 2000);
        return;
    }

    isRequesting = true;
    lastGenerateTime = now;

    ajaxcsrf();
    $.ajax({
        url: base_url + "cbttoken/generate",
        type: "POST",
        async: true, // Non-blocking user experience
        cache: false,
        data: {
            data: JSON.stringify(globalToken),
            force: forceGenerate,
            [CSRF_NAME]: CSRF_HASH
        },
        success: function (response) {
            isRequesting = false;
            console.log('tokenResult', response);
            // Check if response has data property (CI4 returns {status:true, data:{...}})
            let newTokenData = response.data || response;

            localStorage.setItem('garudaCBT.token', JSON.stringify(newTokenData));
            globalToken = newTokenData;
            createIntervalToken();
        },
        error: function (xhr, status, error) {
            isRequesting = false;
            console.log(xhr);
            // On error, retry slowly
            if (!timerToken) timerToken = setTimeout(createIntervalToken, 5000);
        }
    });
}