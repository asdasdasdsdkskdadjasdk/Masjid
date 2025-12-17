<?php
    // File: display/index5.php (Layout Silver - Auto Start)
    
    // --- 1. LOAD DATABASE ---
    $file   = '../db/database.json';
    
    // Default Data
    $default_db = [
        'setting' => ['nama' => 'Masjid Al-Ikhlas', 'lokasi' => 'Indonesia', 'latitude'=>-6.2, 'longitude'=>106.8, 'timeZone'=>7, 'dst'=>'0'],
        'timer' => ['info'=>5, 'wallpaper'=>10, 'wait_adzan'=>2, 'adzan'=>2, 'sholat'=>10],
        'prayTimesMethod' => '0',
        'prayName' => ['fajr'=>'Subuh','sunrise'=>'Syuruq','dhuhr'=>'Dzuhur','asr'=>'Ashar','maghrib'=>'Maghrib','isha'=>'Isya'],
        'info' => [],
        'running_text' => ['Selamat Datang'],
        'iqomah' => [],
        'jumat' => ['active'=>true, 'duration'=>60, 'text'=>'Khutbah Jumat'],
        'youtube_display' => ['active'=>'Tidak']
    ];

    $db = $default_db; 

    if (file_exists($file)){
        $json_content = file_get_contents($file);
        $decoded_data = json_decode($json_content, true);
        if($decoded_data !== null){ $db = array_replace_recursive($default_db, $decoded_data); }
    }
    
    // --- CONFIG VARIABLES ---
    $info_timer         = isset($db['timer']['info']) ? $db['timer']['info'] * 1000 : 5000; 
    $wallpaper_timer    = isset($db['timer']['wallpaper']) ? $db['timer']['wallpaper'] * 1000 : 10000; 
    $wait_adzan_min     = isset($db['timer']['wait_adzan']) ? (int)$db['timer']['wait_adzan'] : 2;
    $nama_masjid        = !empty($db['setting']['nama']) ? $db['setting']['nama'] : "Masjid Raya";
    
    // --- LOAD ASSETS ---
    $dirLogo    = 'logo/';
    $filesLogo  = (is_dir($dirLogo)) ? array_diff(scandir($dirLogo),array('.','..','Thumbs.db')) : [];
    $filesLogo  = array_values($filesLogo);
    $logo       = isset($filesLogo[0]) ? $filesLogo[0] : '';
    
    $dir    = 'wallpaper/';
    $files  = (is_dir($dir)) ? array_diff(scandir($dir),array('.','..','Thumbs.db')) : [];
    $video_exts = ['mp4', 'webm', 'ogg'];
    $wallpaper  = '';
    $i  = 0;
    foreach($files as $v){
        $active = $i==0?'active':'';
        $ext = pathinfo($v, PATHINFO_EXTENSION);
        $ext_lower = strtolower($ext);
        $html_content = '';
        $data_video_attr = '';

        if (in_array($ext_lower, ['jpg', 'jpeg', 'png', 'gif'])) {
            $html_content = '<div class="wp-image" style="background-image: url(wallpaper/'.$v.');"></div>';
        } elseif (in_array($ext_lower, $video_exts)) {
            $html_content = '<div class="video-wrapper"><video muted playsinline><source src="wallpaper/'.$v.'" type="video/'.$ext_lower.'"></video></div>';
            $data_video_attr = ' data-is-video="true"'; 
        }

        if ($html_content !== '') {
            $wallpaper  .= '<div class="item '.$active.'"'.$data_video_attr.'>'.$html_content.'</div>';
            $i++;
        }
    }
    if($wallpaper == '') $wallpaper = '<div class="item active"><div class="wp-image" style="background-color: #105c5d;"></div></div>';
?>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Display Masjid</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700;800&family=Oswald:wght@400;500;700&family=Amiri&family=Playfair+Display:ital@0;1&display=swap" rel="stylesheet">

    <style>
        /* ==================== LAYOUT SILVER CSS ==================== */
        :root {
            --theme-green: #105c5d;
            --gold: #d4af37; 
            --bar-bg: #0c1b2e;
            --text-dark: #111; 
        }

        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; width: 100vw; height: 100vh; overflow: hidden; font-family: 'Montserrat', sans-serif; background: #000; }

        #main-container { display: flex; flex-direction: column; width: 100%; height: 100%; }

        /* --- BAGIAN ATAS (85%) --- */
        #top-section { flex: 85; display: flex; width: 100%; position: relative; }

        /* 1. VISUAL AREA (75%) */
        #visual-area {
            flex: 75; 
            position: relative; 
            overflow: hidden; 
            background: #000;
        }
        
        .carousel, .carousel-inner, .item, .wp-image, .video-wrapper { width: 100%; height: 100%; }
        .wp-image { background-size: cover; background-position: center; transition: transform 20s ease; }
        .item.active .wp-image { transform: scale(1.1); }
        video { object-fit: cover; width: 100%; height: 100%; }
        
        #visual-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.1) 100%);
            z-index: 1; pointer-events: none;
        }

        #float-logo {
            position: absolute; top: 30px; left: 30px; z-index: 20;
            display: flex; align-items: center; gap: 15px;
        }
        #float-logo img { width: 80px; height: 80px; object-fit: contain; filter: drop-shadow(0 2px 5px rgba(0,0,0,0.8)); }
        #float-logo h1 { font-family: 'Oswald', sans-serif; font-size: 2rem; color: #fff; text-shadow: 2px 2px 4px #000; margin: 0; text-transform: uppercase; }

        #quote-wrapper {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            width: 80%; text-align: center; z-index: 10;
        }
        .q-arab { font-family: 'Amiri', serif; font-size: 3.5rem; color: var(--gold); margin-bottom: 20px; text-shadow: 2px 2px 8px #000; line-height: 1.5; }
        .q-text { font-size: 2.2rem; line-height: 1.4; font-weight: 700; text-shadow: 2px 2px 8px #000; color: #fff; }
        .q-ref { margin-top: 20px; font-size: 1.2rem; color: #ccc; text-transform: uppercase; letter-spacing: 2px; text-shadow: 1px 1px 5px #000; }
        .hadist-line-img { display: block; margin: 10px auto; width: 40%; filter: drop-shadow(0 0 5px #000); }

        /* 2. SIDEBAR KANAN */
        #sidebar-right {
            flex: 25;
            background-color: #c0c0c0; 
            background-image: url('img/bg-pattern-01.png');
            background-repeat: repeat;
            background-size: 300px; 
            background-blend-mode: multiply;
            color: var(--text-dark);
            display: flex; flex-direction: column; align-items: center; justify-content: center; 
            border-left: 2px solid #999;
            padding: 10px;
            position: relative;
        }

        .date-box { text-align: center; margin-bottom: 5vh; }
        .date-masehi { font-size: 1.5vw; font-weight: 800; color: #111; text-transform: uppercase; line-height: 1.2; }
        .date-hijri { font-size: 1.2vw; font-style: italic; color: #105c5d; font-family: 'Playfair Display', serif; margin-bottom: 5px; font-weight: bold; }
        
        .clock-wrapper { margin-bottom: 5vh; text-align: center; }
        .big-clock { 
            font-size: 7vw; font-family: 'Oswald', sans-serif; font-weight: 700; color: #000; 
            line-height: 0.9; letter-spacing: -3px; text-shadow: 1px 1px 0px rgba(255,255,255,0.5); 
        }
        
        .next-prayer-box { 
            width: 100%; text-align: center; padding: 2vh 0; 
            border-top: 2px solid #888; border-bottom: 2px solid #888; 
            background: rgba(255,255,255,0.3); 
        }
        .next-label { font-size: 1.2vw; color: #333; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 0; }
        .next-countdown { 
            font-size: 6vw; font-weight: 700; color: #0b4041; 
            font-family: 'Oswald', sans-serif; line-height: 1; text-shadow: 1px 1px 0px rgba(255,255,255,0.3); 
        }
        .next-countdown.blink-red { color: #d00000; animation: blink 1s infinite; }
        @keyframes blink { 50% { opacity: 0.2; } }

        /* --- BAGIAN BAWAH (JADWAL HORIZONTAL) --- */
        #bottom-bar {
            flex: 15; background: var(--bar-bg);
            display: flex; border-top: 5px solid var(--gold);
        }
        .schedule-item {
            flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;
            border-right: 1px solid rgba(255,255,255,0.1); color: #fff;
        }
        .sch-name { font-size: 1.4vw; text-transform: uppercase; color: #aaa; margin-bottom: 5px; font-weight: 600; }
        .sch-time { font-size: 2.5vw; font-family: 'Oswald', sans-serif; font-weight: 700; }
        
        .schedule-item.active { background: var(--theme-green); box-shadow: inset 0 0 30px rgba(0,0,0,0.3); }
        .schedule-item.active .sch-name { color: var(--gold); font-weight: 800; }
        .schedule-item.active .sch-time { color: var(--gold); transform: scale(1.1); }

        /* FOOTER */
        #footer-marquee { height: 50px; background: #fff; display: flex; align-items: center; font-size: 1.5rem; font-weight: 700; text-transform: uppercase; border-top: 2px solid #ccc; }
        .rt-separator { margin: 0 20px; color: var(--theme-green); font-size: 1.5rem; }

        body.mode-video #quote-wrapper { display: none !important; }
        body.mode-video #float-logo { display: none !important; }

        /* Overlays */
        .full-screen-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999; background: #000; display: none; flex-direction: column; justify-content: center; align-items: center; background-size: cover; }
        #display-adzan { background-image: url('img/bg-adzan.png'); }
        #display-sholat { background-image: url('img/bg-sholat.png'); }
        #display-khutbah { background-image: url('img/bg-kubah.png'); }
        #display-syuruq { background-image: url('img/bg-syuruq.png'); }
        
        #display-iqomah { background: #111; }
        .iqomah-circle { width: 400px; height: 400px; border: 10px solid var(--gold); border-radius: 50%; display: flex; flex-direction: column; justify-content: center; align-items: center; background: rgba(0,0,0,0.8); }
        .iqomah-time { font-size: 8rem; color: #fff; font-family: 'Oswald'; }
        
        /* Youtube Layer */
        #display-youtube { position:fixed; top:0; left:0; width:100%; height:100%; z-index:99998; background:#000; display:none; }
        #display-youtube iframe { border:0; width:100%; height:100%; }
    </style>
</head>
<body>
    <audio id="adzan-beep" src="img/beep.mp3" preload="auto"></audio>
    
    <div id="display-adzan" class="full-screen-overlay"></div>
    <div id="display-sholat" class="full-screen-overlay"></div>
    <div id="display-khutbah" class="full-screen-overlay"></div>
    <div id="display-syuruq" class="full-screen-overlay"></div>
    <div id="display-youtube"><iframe src="" allow="autoplay"></iframe></div>
    <div id="display-iqomah" class="full-screen-overlay">
        <div class="iqomah-circle"><div style="font-size:2.5rem; color:var(--gold);">IQOMAH</div><div class="iqomah-time" id="iqomah-val">00:00</div></div>
    </div>

    <div id="main-container">
        
        <div id="top-section">
            <div id="visual-area">
                <div id="bg-layer" style="width:100%; height:100%;">
                    <div id="main-wallpaper-carousel" class="carousel slide" data-ride="carousel" data-interval="false">
                        <div class="carousel-inner"><?=$wallpaper?></div>
                    </div>
                    <div id="visual-overlay"></div>
                </div>

                <div id="float-logo">
                    <img src="logo/<?=$logo?>" alt="Logo">
                    <h1><?= $nama_masjid ?></h1>
                </div>

                <div id="quote-wrapper">
                    <div id="carousel-quote" class="carousel slide" data-ride="carousel" data-interval="false">
                        <div class="carousel-inner">
                            <?php 
                                $idx = 0;
                                if(isset($db['info']) && is_array($db['info'])){
                                    foreach($db['info'] as $info){
                                        if(isset($info[3]) && $info[3] == "1"){
                                            $act = ($idx==0)?'active':'';
                                            echo '<div class="item '.$act.'">';
                                            echo '<div class="q-arab">'.htmlentities($info[0]).'</div>';
                                            echo '<img src="img/hadist-line.png" class="hadist-line-img">';
                                            echo '<div class="q-text">'.nl2br(htmlentities($info[1])).'</div>';
                                            echo '<div class="q-ref">'.htmlentities($info[2]).'</div>';
                                            echo '</div>';
                                            $idx++;
                                        }
                                    }
                                }
                                if($idx==0) echo '<div class="item active"><div class="q-text" style="font-size:3rem;">Selamat Datang</div></div>';
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div id="sidebar-right">
                <div class="date-box">
                    <div class="date-hijri" id="hijri-txt">...</div>
                    <div class="date-masehi" id="tgl">...</div>
                </div>

                <div class="clock-wrapper">
                    <div class="big-clock">
                        <span id="digital-h">00</span>:<span id="digital-m">00</span>
                    </div>
                </div>

                <div class="next-prayer-box">
                    <div class="next-label" id="rc-title">MENUJU ADZAN</div>
                    <div class="next-countdown" id="rc-timer">--:--</div>
                </div>
            </div>
        </div>

        <div id="bottom-bar">
            </div>

        <div id="footer-marquee">
            <marquee>
                <?php 
                    if(isset($db['running_text'])){
                        foreach($db['running_text'] as $txt){
                            echo '<span class="rt-separator">•</span> '.htmlentities($txt).' ';
                        }
                    }
                ?>
            </marquee>
        </div>
    </div>

    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/moment-with-locales.js"></script>
    <script src="js/PrayTimes.js"></script>

    <script>
        // =========================================================
        // LOGIC DARI REFERENCE CODE (DENGAN TAMPILAN SILVER)
        // =========================================================
        moment.locale('id'); 
        var format = '24h';
        var appDB = <?= json_encode($db) ?>; 
        
        var lat = parseFloat(appDB.setting.latitude);
        var lng = parseFloat(appDB.setting.longitude);
        var timeZone = parseInt(appDB.setting.timeZone);
        var dst = appDB.setting.dst;
        var waitAdzanMin = parseInt(appDB.timer.wait_adzan);

        if(typeof prayTimes !== 'undefined'){
            if(appDB.prayTimesMethod == '0'){
                let adj = {};
                for(let k in appDB.prayTimesAdjust){ if(appDB.prayTimesAdjust[k]) adj[k] = appDB.prayTimesAdjust[k]; }
                prayTimes.adjust(adj);
            } else { prayTimes.setMethod(appDB.prayTimesMethod); }
            let tune = {};
            for(let k in appDB.prayTimesTune){ if(appDB.prayTimesTune[k] != 0) tune[k] = appDB.prayTimesTune[k]; }
            prayTimes.tune(tune);
        }

        function writeIslamicDate(adjustment) {
            var iMonthNames = ["Muharram", "Safar", "Rabi'ul Awal", "Rabi'ul Akhir", "Jumadil Awal", "Jumadil Akhir", "Rajab", "Sya'ban", "Ramadhan", "Syawal", "Zulqa'dah", "Zulhijjah"];
            var today = new Date(); var adjustmili = 1000 * 60 * 60 * 24 * adjustment; today = new Date(today.getTime() + adjustmili);
            var day = today.getDate(); var month = today.getMonth(); var year = today.getFullYear(); var m = month + 1; var y = year;
            if (m < 3) { y -= 1; m += 12; }
            var a = Math.floor(y / 100.); var b = 2 - a + Math.floor(a / 4.);
            if (y < 1583) b = 0; if (y == 1582) { if (m > 10) b = -10; if (m == 10) { b = 0; if (day > 4) b = -10; } }
            var jd = Math.floor(365.25 * (y + 4716)) + Math.floor(30.6001 * (m + 1)) + day + b - 1524;
            var iyear = 10631. / 30.; var epochastro = 1948084; var shift1 = 8.01 / 60.;
            var z = jd - epochastro; var cyc = Math.floor(z / 10631.); z = z - 10631 * cyc;
            var j = Math.floor((z - shift1) / iyear); var iy = 30 * cyc + j; z = z - Math.floor(j * iyear + shift1); var im = Math.floor((z + 28.5001) / 29.5); 
            if (im == 13) im = 12; var id = z - Math.floor(29.5001 * im - 29); return id + " " + iMonthNames[im - 1] + " " + iy + " H";
        }

        var app = {
            db: appDB,
            audio: document.getElementById('adzan-beep'),
            iqomahInterval: null,
            
            init: function() {
                $('#preloader').fadeOut(); // Hapus preloader jika ada
                setInterval(app.tick, 1000);
                app.tick(); 
                $('#carousel-quote').carousel({ interval: <?=$info_timer?>, pause: false });
                app.startWallpaper(<?=$wallpaper_timer?>);
            },

            tick: function() {
                let now = moment();
                // Update Layout Silver Elements
                $('#digital-h').text(now.format('HH'));
                $('#digital-m').text(now.format('mm'));
                $('#tgl').text(now.format('dddd, DD MMMM YYYY'));
                $('#hijri-txt').text(writeIslamicDate(-1));

                let tToday = prayTimes.getTimes(new Date(), [lat, lng], timeZone, dst, format);
                let tTom = prayTimes.getTimes(moment().add(1, 'd').toDate(), [lat, lng], timeZone, dst, format);
                
                let html = ''; let nextName = ''; let nextTime = null; let foundNext = false;
                
                // Urutan kunci jadwal
                let keys = ['fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha'];

                keys.forEach(function(k) {
                    let v = appDB.prayName[k]; 
                    if (!v) return;

                    let timeStr = tToday[k];
                    let pTime = moment(timeStr, 'HH:mm');
                    let activeClass = '';
                    
                    if (!foundNext && now.isBefore(pTime)) {
                        activeClass = 'active'; foundNext = true; nextName = v; nextTime = pTime;
                    }

                    // GENERATE HTML LAYOUT SILVER
                    html += `<div class="schedule-item ${activeClass}">
                                <div class="sch-name">${v}</div>
                                <div class="sch-time">${timeStr}</div>
                             </div>`;
                    
                    app.checkTrigger(k, v, pTime, now);
                });

                if (!foundNext) {
                    let pTime = moment(tTom.fajr, 'HH:mm').add(1, 'd'); nextName = 'Subuh'; nextTime = pTime;
                }
                
                $('#bottom-bar').html(html);

                if (nextTime) app.handleCountdown(nextTime, nextName);
            },

            handleCountdown: function(targetTime, name) {
                if ($('.full-screen-overlay:visible').length > 0 || $('#display-youtube:visible').length > 0) {
                     return;
                }

                let diff = targetTime.diff(moment(), 'seconds');
                if (diff < 0) return;

                let dur = moment.duration(diff, 'seconds');
                let h = Math.floor(dur.asHours());
                let m = dur.minutes();
                let s = dur.seconds();
                let txt = (h<10?'0'+h:h) + ":" + (m<10?'0'+m:m) + ":" + (s<10?'0'+s:s);

                $('#rc-title').text("MENUJU " + name.toUpperCase());
                $('#rc-timer').text(txt);

                let thresholdSec = waitAdzanMin * 60; 

                // LOGIKA MERAH/BESAR DI SILVER LAYOUT
                if (diff <= thresholdSec) {
                    $('#rc-timer').addClass('blink-red'); 
                } else {
                    $('#rc-timer').removeClass('blink-red');
                }
            },

            checkTrigger: function(key, name, pTimeObj, nowObj) {
                let nowStr = nowObj.format('HH:mm:ss');
                let pTimeStr = pTimeObj.format('HH:mm:ss');

                if (nowStr === pTimeStr) {
                    if(key === 'sunrise') {
                        app.overlay('display-syuruq');
                    } else {
                        // ADZAN
                        app.overlay('display-adzan');
                        app.playAudio(); 

                        let adzanDur = app.db.timer.adzan * 60000;
                        setTimeout(function(){
                            // JUMAT
                            if(key === 'dhuhr' && nowObj.format('d') == 5 && app.db.jumat.active) {
                                app.overlay('display-khutbah');
                            } else {
                                // IQOMAH
                                app.startIqomahTimer(key, pTimeObj); 
                            }
                        }, adzanDur);
                    }
                }
            },

            startIqomahTimer: function(key, pTimeObj){
                $('.full-screen-overlay').hide();
                $('#display-iqomah').css('display','flex').hide().fadeIn();
                
                let iqomahMin = 10;
                if(app.db.iqomah && app.db.iqomah[key]) iqomahMin = parseInt(app.db.iqomah[key]);
                
                let adzanMin = parseInt(app.db.timer.adzan);
                let endTime = moment(pTimeObj).add(adzanMin + iqomahMin, 'minutes');
                
                clearInterval(app.iqomahInterval);
                app.iqomahInterval = setInterval(function(){
                    let now = moment();
                    let diff = endTime.diff(now, 'seconds');
                    
                    if(diff <= 0){
                        clearInterval(app.iqomahInterval);
                        $('#display-iqomah').fadeOut();
                        
                        // SHOLAT
                        app.playAudio(); 
                        app.overlay('display-sholat');
                    } else {
                        let dur = moment.duration(diff, 'seconds');
                        let m = dur.minutes(); let s = dur.seconds();
                        $('#iqomah-val').text( (m<10?'0'+m:m) + ":" + (s<10?'0'+s:s) );
                    }
                }, 1000);
            },

            overlay: function(id) {
                $('.full-screen-overlay').hide();
                app.stopYoutube();
                let el = $('#'+id);
                el.css('display','flex').hide().fadeIn();
                
                let ms = 60000; 
                if(id=='display-adzan') ms = app.db.timer.adzan * 60000;
                if(id=='display-khutbah') ms = app.db.jumat.duration * 60000;
                if(id=='display-sholat') ms = app.db.timer.sholat * 60000;
                
                if(id !== 'display-adzan' && id !== 'display-iqomah'){
                    setTimeout(() => {
                        el.fadeOut();
                        if(id=='display-sholat') app.checkYoutube();
                    }, ms);
                }
            },

            playAudio: function() {
                // Sesuai request: LOGIKA PERSIS REFERENSI (Tanpa Button Handler)
                app.audio.loop = true; 
                app.audio.currentTime = 0;
                // Coba play, catch error jika diblokir browser
                app.audio.play().catch(e=>console.log("Audio Autoplay Blocked (Need Interaction):", e));
                
                setTimeout(function(){
                    app.audio.pause();
                    app.audio.loop = false;
                    app.audio.currentTime = 0;
                }, 10000);
            },

            startWallpaper: function(timer) {
                let wp = $('#main-wallpaper-carousel');
                let tId;
                function next() { wp.carousel('next'); }

                wp.on('slid.bs.carousel', function (e) {
                    let slide = $(e.relatedTarget);
                    let isVideo = slide.attr('data-is-video') === 'true';
                    clearTimeout(tId);
                    
                    if(isVideo) {
                        $('body').addClass('mode-video');
                        let v = slide.find('video')[0];
                        v.currentTime = 0; v.play();
                        v.onended = function() { wp.carousel('next'); };
                    } else {
                        $('body').removeClass('mode-video');
                        tId = setTimeout(next, timer);
                    }
                });
                
                let first = wp.find('.item.active');
                if(first.length && first.attr('data-is-video') === 'true'){
                    $('body').addClass('mode-video');
                    first.find('video')[0].play();
                    first.find('video')[0].onended = function() { wp.carousel('next'); };
                } else {
                    tId = setTimeout(next, timer);
                }
            },
            
            checkYoutube: function(){
                let yt = app.db.youtube_display;
                if(yt && yt.active === 'Ya' && yt.link){
                    let url = "https://www.youtube.com/embed/" + yt.link + "?autoplay=1&controls=0&mute=1";
                    $('#display-youtube iframe').attr('src', url);
                    $('#display-youtube').fadeIn();
                    setTimeout(app.stopYoutube, yt.duration * 60000);
                }
            },
            stopYoutube: function(){
                $('#display-youtube').fadeOut();
                $('#display-youtube iframe').attr('src', '');
            }
        };

        $(document).ready(function() { app.init(); });
    </script>
</body>
</html>