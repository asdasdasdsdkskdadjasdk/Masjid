<?php
    // File: display/index.php
    $file   = '../db/database.json';
    if (!file_exists($file)){
        // Fallback default
        $db = [
            'timer' => ['info'=>5, 'wallpaper'=>10, 'wait_adzan'=>1, 'adzan'=>2, 'sholat'=>10],
            'prayTimesMethod' => '0',
            'prayTimesAdjust' => [],
            'prayTimesTune' => [],
            'prayName' => ['fajr'=>'Subuh','sunrise'=>'Syuruq','dhuhr'=>'Dzuhur','asr'=>'Ashar','maghrib'=>'Maghrib','isha'=>'Isya'],
            'setting' => ['latitude'=>-6.2, 'longitude'=>106.8, 'timeZone'=>7, 'dst'=>'0'],
            'info' => [],
            'running_text' => ['Selamat Datang'],
            'iqomah' => [],
            'jumat' => ['active'=>true, 'duration'=>60, 'text'=>'Khutbah'],
            'youtube_display' => ['active'=>'Tidak']
        ];
    } else {
        $json   = file_get_contents($file);
        $db     = json_decode($json, true);
    }
    
    // --- VARIABLES ---
    $info_timer         = isset($db['timer']['info']) ? $db['timer']['info'] * 1000 : 5000; 
    $wallpaper_timer    = isset($db['timer']['wallpaper']) ? $db['timer']['wallpaper'] * 1000 : 10000; 
    $wait_adzan_min     = isset($db['timer']['wait_adzan']) ? (int)$db['timer']['wait_adzan'] : 1;
    
    // Logo
    $dirLogo    = 'logo/';
    $filesLogo  = (is_dir($dirLogo)) ? array_diff(scandir($dirLogo),array('.','..','Thumbs.db')) : [];
    $filesLogo  = array_values($filesLogo);
    $logo       = isset($filesLogo[0]) ? $filesLogo[0] : '';
    
    // Wallpaper & Video
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
    if($wallpaper == '') $wallpaper = '<div class="item active"><div class="wp-image" style="background-color: #000;"></div></div>';
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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Roboto+Condensed:wght@400;700&family=Amiri&family=Playfair+Display:ital@0;1&display=swap" rel="stylesheet">

    <style>
        /* ==================== GLOBAL CONFIG ==================== */
        :root {
            --sidebar-width: 380px;
            --gold: #f39c12;
            --cyan: #00e5ff; /* Warna Hijau Tosca untuk Nama Sholat */
            --cyan2: #1DE9B6 ;
            --sidebar-bg: #101010;
            --running-bg: linear-gradient(90deg, #0f3d3e, #105c5d); /* Dark Teal Gradient */
            
        }

        * { box-sizing: border-box; }

        body { 
            margin: 0; padding: 0; width: 100vw; height: 100vh; 
            overflow: hidden; font-family: 'Montserrat', sans-serif; background: #000; 
        }

        /* ==================== OVERLAYS ==================== */
        .full-screen {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            z-index: 99999; background: #000; display: none;
            background-size: cover; background-position: center; background-repeat: no-repeat;
            justify-content: center; align-items: center; flex-direction: column;
        }
        
        #display-adzan { background-image: url('img/bg-adzan.png'); }
        #display-sholat { background-image: url('img/bg-sholat.png'); }
        #display-khutbah { background-image: url('img/bg-kubah.png'); }
        #display-syuruq { background-image: url('img/bg-syuruq.png'); }

        /* IQOMAH OVERLAY */
        #display-iqomah { 
            background-color: #000; 
            background-image: url('img/bg-pattern-02.png'); 
            background-repeat: repeat; background-size: 150px;
        }
        .iqomah-box { text-align: center; z-index: 2; }
        .iqomah-label {
            display: inline-block; color: var(--gold); font-size: 3vw;
            letter-spacing: 5px; text-transform: uppercase;
            border: 3px solid var(--gold); padding: 15px 50px;
            border-radius: 15px; margin-bottom: 20px;
            background: rgba(0,0,0,0.8);
        }
        .iqomah-timer {
            font-family: 'Roboto Condensed', sans-serif;
            font-size: 20vw; color: #fff; line-height: 0.9; font-weight: bold;
            text-shadow: 0 0 30px rgba(255,255,255,0.8);
        }

        .msg-box {
            color: var(--gold); font-weight: 800; font-size: 5vw;
            text-transform: uppercase; padding: 30px; 
            border: 4px solid #fff; border-radius: 20px;
            background: rgba(0,0,0,0.85); text-align: center;
            box-shadow: 0 0 50px #000;
        }

        #display-youtube { position:fixed; top:0; left:0; width:100%; height:100%; z-index:99998; background:#000; display:none; }
        iframe { border:0; width:100%; height:100%; }

        /* ==================== LAYOUT UTAMA ==================== */
        #main-wrapper { display: flex; width: 100%; height: 100%; }

        /* --- SIDEBAR KIRI (PATTERN DIPERJELAS) --- */
        #left-container {
            width: var(--sidebar-width); height: 100%;
            background-color: var(--sidebar-bg);
            
            /* Pattern Background Diperkuat */
            background-image: url('img/bg-pattern-01.png'); 
            background-repeat: repeat; 
            background-size: 350px;
            background-blend-mode: lighten; /* Ubah blend mode agar lebih terlihat */
            opacity: 1; 
            
            border-right: 3px solid #222; 
            display: flex; flex-direction: column;
            z-index: 20; box-shadow: 5px 0 20px rgba(0,0,0,0.9);
        }

        /* JAM & TANGGAL (BESAR) */
        .jam-container { padding: 140px 0 5px 0; text-align: center; }
        #jam { 
            font-size: 6.5em; /* Sangat Besar */
            font-weight: 700; color: #fff; 
            line-height: 0.85; margin-bottom: 5px; 
            text-shadow: 3px 3px 8px #000; 
        }
        #jam span { font-size: 0.4em; color: var(--cyan2); font-weight: 600; margin-left: 5px; }
        
        .tgl-container { padding: 5px 0 15px 0; text-align: center; }
        #tgl { 
            font-size: 1.8em; /* Besar */
            color: #fff; font-weight: 700; margin-bottom: 8px; 
            letter-spacing: 0.5px;
        }
        .hijri { 
            font-size: 1.6em; color: var(--gold); 
            font-family: 'Playfair Display', serif; font-style: italic; 
        }
        
        /* ORNAMEN DIVIDER (DIPERBESAR) */
        .divider { 
            height: 60px; /* Lebih Tinggi */
            width: 90%; margin: 0 auto 10px auto; 
            background: url('img/clock-line.png') no-repeat center; 
            background-size: contain; 
            filter: drop-shadow(0 0 5px rgba(243, 156, 18, 0.5)); /* Glow effect */
        }

        /* --- JADWAL LIST STYLE --- */
        #jadwal { 
            flex-grow: 1; display: flex; flex-direction: column; 
            justify-content: space-evenly; padding: 10px 0; overflow: hidden;
            text-shadow: 0 0 10px rgba(12, 93, 102, 0.7), 
                 0 0 20px rgba(0, 229, 255, 0.5);
        }
        
        .jadwal-item {
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 25px; 
            font-size: 1.8em; /* Font Besar */
            color: #999; 
            border-bottom: 1px solid rgba(255,255,255,0.08);
            border-left: 5px solid transparent; 
            transition: all 0.3s;
            position: relative;
        }
        
        /* WARNA CYAN UNTUK NAMA SHOLAT */
        .jadwal-item .nama { 
            font-weight: 600; 
            font-family: 'Montserrat', sans-serif; 
            color: var(--cyan2); 
        }
        .jadwal-item .waktu { font-family: 'Roboto Condensed', sans-serif; font-weight: 700; color: #fff; letter-spacing: 1px;}
        .jadwal-item .icon-sholat { display: none; } 

        /* STYLE JADWAL AKTIF (TRANSPARAN + BORDER EMAS) */
        .jadwal-item.active { 
            /* Transparan dengan sedikit gelap (Glassy) */
            background: var(--running-bg);
            border-left: 6px solid var(--gold); 
            box-shadow: 0 5px 20px rgba(0,0,0,0.8);
            transform: scale(1.05); /* Zoom Effect */
            z-index: 2;
            border-top: 1px solid rgba(255,255,255,0.1);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        /* Warna Emas saat Aktif */
        .jadwal-item.active .nama, .jadwal-item.active .waktu { color: #fff; text-shadow: 0 0 10px rgba(255,255,255,0.5); }
        /* Icon Sholat di Tengah */
        .jadwal-item.active .icon-sholat { 
            display: block; color: var(--gold); font-size: 0.6em; animation: pulse 2s infinite; 
        }
        @keyframes pulse { 0% { opacity: 0.6; } 50% { opacity: 1; } 100% { opacity: 0.6; } }

        /* --- KANAN (CONTENT) --- */
        #right-container { flex-grow: 1; position: relative; background: #000; overflow: hidden; }

        #main-wallpaper-carousel { position: absolute; top:0; left:0; width:100%; height:100%; z-index: 1; }

        #main-wallpaper-carousel::after {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.4); /* Ubah 0.6 untuk mengatur kegelapan (0.1 - 0.9) */
            z-index: 2; /* Di atas gambar */
            pointer-events: none; /* Agar klik tembus ke bawah jika perlu */
        }
        .carousel-inner, .item, .wp-image { width: 100%; height: 100%; background-size: cover; background-position: center; }
        .video-wrapper { width: 100%; height: 100%; overflow: hidden; position: relative; }
        video { width: 100vw; height: 56.25vw; min-height: 100vh; min-width: 177.77vh; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); }

        /* Quote Layer */
        @keyframes slideInFromRight {
            0% { transform: translateX(50px); opacity: 0; }
            100% { transform: translateX(0); opacity: 1; }
        }
        #quote-layer {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            z-index: 10; padding-bottom: 60px; transition: opacity 0.5s;
        }
        #carousel-quote, #carousel-quote .carousel-inner { height: 100%; width: 100%; }
        #carousel-quote .item { height: 100%; width: 100%; display: none; }
        #carousel-quote .item.active { display: flex !important; justify-content: center; align-items: center; }
        #carousel-quote .item.active .quote-box { animation: slideInFromRight 0.8s ease-out forwards; }

        .quote-box {
            background: rgba(0,0,0,0.7); padding: 40px 60px; border-radius: 15px;
            border-left: 5px solid var(--gold); border-right: 5px solid var(--gold);
            text-align: center; max-width: 80%; margin: 0 auto;
            box-shadow: 0 10px 40px rgba(0,0,0,0.8);
        }
        .q-arab { font-family: 'Amiri', serif; font-size: 2.8em; color: var(--gold); margin-bottom: 10px; line-height: 1.6; }
        .q-text { font-family: 'Playfair Display', serif; font-size: 2em; color: #fff; font-style: italic; line-height: 1.4; margin-bottom: 10px; }
        .q-ref { font-size: 1.1em; color: #bbb; text-transform: uppercase; letter-spacing: 3px; }
        .hadist-line {
            height: 20px; width: 100%; margin: 15px 0;
            background: url('img/hadist-line.png') no-repeat center; background-size: contain;
        }

        /* --- COUNTDOWN --- */
        #right-counter {
            position: absolute; top: 30px; right: 30px; 
            background-color: rgba(0,0,0,0.85);
            /* Pattern 2 */
            background-image: url('img/bg-pattern-02.png');
            background-size: 120px; background-blend-mode: soft-light;
            
            padding: 15px 30px; border-radius: 10px; border: 2px solid var(--gold);
            text-align: center; z-index: 40; display: none; 
            transition: all 0.6s cubic-bezier(0.22, 1, 0.36, 1);
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
        }
        #right-counter h3 { margin: 0; font-size: 16px; color: #fff; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 5px; }
        #right-counter .cd-val { font-size: 32px; color: var(--gold); font-weight: bold; font-family: 'Roboto Condensed'; }

        #right-counter.enlarged {
            top: 50%; left: 50%; transform: translate(-50%, -50%);
            width: 70%; padding: 60px; background-color: rgba(0,0,0,0.95);
            border: 6px solid var(--gold); z-index: 100;
            box-shadow: 0 0 100px rgba(243, 156, 18, 0.6);
        }
        #right-counter.enlarged h3 { font-size: 4em; margin-bottom: 30px; }
        #right-counter.enlarged .cd-val { font-size: 10em; line-height: 1; text-shadow: 0 0 30px var(--gold); }

        #logo-float {
            position: absolute; top: 30px; right: 30px; width: 90px; height: 90px;
            background-size: contain; background-repeat: no-repeat; z-index: 30;
            filter: drop-shadow(0 2px 5px #000); transition: opacity 0.3s;
        }

        /* --- RUNNING TEXT (MENGAMBANG) --- */
        #running-text {
            position: absolute; 
            bottom: 10px; /* NAIK SEDIKIT (MENGAMBANG) */
            left: 0; width: 100%; height: 60px;
            
            background: var(--running-bg); /* Hijau Gradasi */
            border-top: 3px solid var(--gold);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            
            z-index: 50; display: flex; align-items: center;
            color: #fff; font-size: 26px; font-weight: bold;
            box-shadow: 0 -5px 15px rgba(0,0,0,0.5); /* Shadow agar terlihat mengambang */
        }
        
        /* ORNAMEN KOTAK KUNING + PATTERN */
        .ornamen-box {
            width: 50px; height: 100%; background: var(--gold);
            position: absolute; left: 0; top: 0; z-index: 55;
            display: flex; justify-content: center; align-items: center;
            box-shadow: 5px 0 15px rgba(0,0,0,0.3);
            
            /* Pattern tambahan di kotak kuning */
            background-image: url('img/pattern01.png');
            background-size: 50px; background-repeat: repeat;
            background-blend-mode: soft-light;
        }
        
        .ornamen-icon {
            width: 30px; height: 30px;
            /* Ikon Pattern/Bintang */
            background: url('img/image_9cc2bb.png') no-repeat center; 
            background-size: contain;
        }
        
        #running-text marquee { margin-left: 80px; width: calc(100% - 80px); }

    </style>
</head>
<body>
    <audio id="adzan-beep" src="img/beep.mp3" preload="auto"></audio>
    <div id="preloader" style="position:fixed; top:0; left:0; width:100%; height:100%; background:#000; z-index:100000;"></div>

    <div id="display-adzan" class="full-screen"></div>
    <div id="display-sholat" class="full-screen"></div>
    <div id="display-khutbah" class="full-screen"></div>
    <div id="display-syuruq" class="full-screen"></div>
    <div id="display-youtube"><iframe src="" allow="autoplay"></iframe></div>

    <div id="display-iqomah" class="full-screen">
        <div class="iqomah-box">
            <div class="iqomah-label">IQOMAH</div>
            <div class="iqomah-timer" id="iqomah-val">00:00</div>
        </div>
    </div>

    <div id="main-wrapper">
        <div id="left-container">
            <div class="jam-container"><div id="jam">00:00<span>00</span></div></div>
            <div class="tgl-container"><div id="tgl">...</div><div class="hijri" id="hijri-txt">...</div></div>
            <div class="divider"></div> 
            <div id="jadwal"></div> 
        </div>

        <div id="right-container">
            <div id="main-wallpaper-carousel" class="carousel slide" data-ride="carousel" data-interval="false">
                <div class="carousel-inner"><?=$wallpaper?></div>
            </div>

            <div id="quote-layer">
                <div id="carousel-quote" class="carousel slide" data-ride="carousel" data-interval="false">
                    <div class="carousel-inner">
                        <?php 
                            $idx = 0;
                            if(isset($db['info']) && is_array($db['info'])){
                                foreach($db['info'] as $info){
                                    if(isset($info[3]) && $info[3] == 1){
                                        $act = ($idx==0)?'active':'';
                                        echo '<div class="item '.$act.'"><div class="quote-box">';
                                        echo '<div class="q-arab">'.htmlentities($info[0]).'</div>';
                                        echo '<div class="q-text">'.nl2br(htmlentities($info[1])).'</div>';
                                        echo '<div class="hadist-line"></div>';
                                        echo '<div class="q-ref">'.htmlentities($info[2]).'</div>';
                                        echo '</div></div>';
                                        $idx++;
                                    }
                                }
                            }
                            if($idx==0) echo '<div class="item active"><div class="quote-box"><h3>Selamat Datang di Masjid</h3></div></div>';
                        ?>
                    </div>
                </div>
            </div>

            <div id="logo-float" style="background-image: url('logo/<?=$logo?>');"></div>

            <div id="right-counter">
                <h3 id="rc-title">MENUJU AZAN</h3>
                <div class="cd-val" id="rc-timer">00:00:00</div>
            </div>

            <div id="running-text">
                <div class="ornamen-box"><div class="ornamen-icon"></div></div>
                <marquee>
                    <?php 
                        if(isset($db['running_text'])){
                            foreach($db['running_text'] as $txt){
                                echo '<i class="fa fa-star" style="color:var(--gold); margin:0 15px;"></i> '.htmlentities($txt).' ';
                            }
                        }
                        $ip = gethostbyname(gethostname());
                        if(PHP_OS=='Linux') $ip = trim(exec("/sbin/ifconfig wlan0 | grep 'inet '| cut -d 't' -f2 | cut -d 'n' -f1 | awk '{ print $1}'"));
                        if(isset($db['akses']['pass']) && $db['akses']['pass']=='admin') echo '| SSID: DisplayMasjid (Pass: 12345678) | IP Admin: http://'.$ip.'/';
                    ?>
                </marquee>
            </div>
        </div>
    </div>

    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/moment-with-locales.js"></script>
    <script src="js/PrayTimes.js"></script>

    <script>
        moment.locale('id'); 
        var format = '24h';
        var appDB = <?= json_encode($db) ?>; 
        
        var lat = parseFloat(appDB.setting.latitude) || -6.2;
        var lng = parseFloat(appDB.setting.longitude) || 106.8;
        var timeZone = parseInt(appDB.setting.timeZone) || 7;
        var dst = appDB.setting.dst || '0';
        var waitAdzanMin = parseInt(appDB.timer.wait_adzan) || 1;

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
                $('#preloader').fadeOut();
                setInterval(app.tick, 1000);
                app.tick(); 
                $('#carousel-quote').carousel({ interval: <?=$info_timer?>, pause: false });
                app.startWallpaper(<?=$wallpaper_timer?>);
            },

            tick: function() {
                let now = moment();
                $('#jam').html(now.format('HH:mm') + '<span>' + now.format('ss') + '</span>');
                $('#tgl').text(now.format('dddd, DD MMMM YYYY'));
                $('#hijri-txt').text(writeIslamicDate(-1));

                if(typeof prayTimes === 'undefined') return;
                let tToday = prayTimes.getTimes(new Date(), [lat, lng], timeZone, dst, format);
                let tTom = prayTimes.getTimes(moment().add(1, 'd').toDate(), [lat, lng], timeZone, dst, format);
                
                let html = ''; let nextName = ''; let nextTime = null; let foundNext = false;

                $.each(app.db.prayName, function(k, v) {
                    if (k === 'sunrise' && !tToday.sunrise) return; 
                    let pTime = moment(tToday[k], 'HH:mm');
                    let activeClass = '';
                    if (!foundNext && now.isBefore(pTime)) {
                        activeClass = 'active'; foundNext = true; nextName = v; nextTime = pTime;
                    }
                    // Generate List (Icon hanya muncul jika Active)
                    html += `<div class="jadwal-item ${activeClass}">
                                <div class="nama">${v}</div>
                                ${activeClass === 'active' ? '<div class="icon-sholat"><i class="fa fa-certificate"></i></div>' : ''}
                                <div class="waktu">${tToday[k]}</div>
                             </div>`;
                    app.checkTrigger(k, v, pTime, now);
                });
                if (!foundNext) {
                    let pTime = moment(tTom.fajr, 'HH:mm').add(1, 'd'); nextName = 'Subuh'; nextTime = pTime;
                }
                if(html) $('#jadwal').html(html);
                if (nextTime) app.handleCountdown(nextTime, nextName);
            },

            handleCountdown: function(targetTime, name) {
                // 1. Cek jika overlay Full Screen atau Youtube aktif, sembunyikan counter kecil
                if ($('.full-screen:visible').length > 0 || $('#display-youtube:visible').length > 0) {
                    $('#right-counter').hide(); return;
                }

                let diff = targetTime.diff(moment(), 'seconds');
                if (diff < 0) { $('#right-counter').hide(); return; }

                let dur = moment.duration(diff, 'seconds');
                let h = Math.floor(dur.asHours());
                let m = dur.minutes();
                let s = dur.seconds();
                let txt = (h<10?'0'+h:h) + ":" + (m<10?'0'+m:m) + ":" + (s<10?'0'+s:s);

                $('#rc-title').text("MENUJU " + name.toUpperCase());
                $('#rc-timer').text(txt);
                $('#right-counter').show();

                let thresholdSec = waitAdzanMin * 60; 

                // --- LOGIKA UTAMA PERBAIKAN ---
                if (diff <= thresholdSec) {
                    // MODE HITUNG MUNDUR (MERAH/BESAR)
                    if (!$('#right-counter').hasClass('enlarged')) {
                        $('#right-counter').addClass('enlarged');
                        $('#quote-layer').fadeOut(); 
                        $('#logo-float').fadeOut();
                    }
                } else {
                    // MODE NORMAL
                    
                    // Cek apakah slide yang aktif sekarang adalah VIDEO
                    let isVideo = $('#main-wallpaper-carousel .item.active').attr('data-is-video') === 'true';

                    if ($('#right-counter').hasClass('enlarged')) {
                        $('#right-counter').removeClass('enlarged');
                        
                        // HANYA Munculkan info jika BUKAN video
                        if(!isVideo) $('#quote-layer').fadeIn(); 
                        
                        $('#logo-float').fadeIn();
                    }
                    
                    // SAFETY NET (PENGAMAN):
                    // Cek setiap detik. 
                    // Jika BUKAN video tapi info tertutup -> Tampilkan
                    if(!isVideo && $('#quote-layer').is(':hidden')) {
                        $('#quote-layer').fadeIn();
                    }
                    // Jika SEDANG video tapi info nongol -> Sembunyikan (Ini perbaikan utamanya)
                    if(isVideo && $('#quote-layer').is(':visible')) {
                        $('#quote-layer').fadeOut();
                    }

                    if($('#logo-float').is(':hidden')) $('#logo-float').fadeIn();
                }
            },

            checkTrigger: function(key, name, pTimeObj, nowObj) {
                let nowStr = nowObj.format('HH:mm:ss');
                let pTimeStr = pTimeObj.format('HH:mm:ss');

                if (nowStr === pTimeStr) {
                    if(key === 'sunrise') app.overlay('display-syuruq');
                    else {
                        app.overlay('display-adzan');
                        let adzanDur = app.db.timer.adzan * 60000;
                        setTimeout(function(){
                            if(key === 'dhuhr' && nowObj.format('d') == 5 && app.db.jumat.active) {
                                app.overlay('display-khutbah');
                            } else {
                                app.startIqomahTimer(key, pTimeObj); 
                            }
                        }, adzanDur);
                    }
                    app.playAudio();
                }
            },

            startIqomahTimer: function(key, pTimeObj){
                $('.full-screen').hide();
                $('#display-iqomah').css('display','flex').hide().fadeIn();
                
                let iqomahMin = parseInt(app.db.iqomah[key]) || 10;
                let adzanMin = parseInt(app.db.timer.adzan);
                let endTime = moment(pTimeObj).add(adzanMin + iqomahMin, 'minutes');
                
                clearInterval(app.iqomahInterval);
                app.iqomahInterval = setInterval(function(){
                    let now = moment();
                    let diff = endTime.diff(now, 'seconds');
                    
                    // --- LOGIKA SAAT WAKTU IQOMAH HABIS ---
                    if(diff <= 0){
                        clearInterval(app.iqomahInterval);
                        $('#display-iqomah').fadeOut();
                        
                        // 1. Bunyikan Beep Tanda Masuk Sholat
                        app.playAudio(); 
                        
                        // 2. Tampilkan Overlay Luruskan Shaf
                        app.overlay('display-sholat');
                    } else {
                        let dur = moment.duration(diff, 'seconds');
                        let m = dur.minutes();
                        let s = dur.seconds();
                        $('#iqomah-val').text( (m<10?'0'+m:m) + ":" + (s<10?'0'+s:s) );
                    }
                }, 1000);
            },

            overlay: function(id) {
                $('.full-screen').hide();
                app.stopYoutube();
                let el = $('#'+id);
                el.css('display','flex').hide().fadeIn();
                
                let ms = 60000; // Default 1 menit (jika data kosong)
                
                // 1. Ambil Durasi Azan dari Database
                if(id=='display-adzan') ms = app.db.timer.adzan * 60000;
                
                // 2. Ambil Durasi Khutbah Jumat dari Database
                if(id=='display-khutbah') ms = app.db.jumat.duration * 60000;
                
                // 3. PERBAIKAN: Ambil Durasi Sholat dari Database
                if(id=='display-sholat') ms = app.db.timer.sholat * 60000;
                
                // Eksekusi Timer Penutup (Kecuali Adzan & Iqomah yang punya timer sendiri)
                if(id !== 'display-adzan' && id !== 'display-iqomah'){
                    setTimeout(() => {
                        el.fadeOut();
                        // Setelah overlay sholat tutup, cek apakah ada video Youtube jadwalnya?
                        if(id=='display-sholat') app.checkYoutube();
                    }, ms);
                }
            },

            playAudio: function() {
                app.audio.loop = true; 
                app.audio.currentTime = 0;
                app.audio.play().catch(e=>console.log(e));
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
                        $('#quote-layer').fadeOut(); 
                        let v = slide.find('video')[0];
                        v.currentTime = 0; v.play();
                        v.onended = function() { wp.carousel('next'); };
                    } else {
                        if(!$('#right-counter').hasClass('enlarged')) {
                            $('#quote-layer').fadeIn();
                        }
                        tId = setTimeout(next, timer);
                    }
                });
                
                let first = wp.find('.item.active');
                if(first.attr('data-is-video') === 'true'){
                    first.find('video')[0].play();
                    first.find('video')[0].onended = function() { wp.carousel('next'); };
                    $('#quote-layer').hide();
                } else {
                    tId = setTimeout(next, timer);
                }
            },
            
            checkYoutube: function(){
                let yt = app.db.youtube_display;
                if(yt.active && yt.link){
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