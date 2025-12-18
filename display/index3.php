<?php
    // File: display/index.php
    
    // --- LOAD DATABASE ---
   $file   = '../db/database.json';
    
    // Default Data (Cadangan jika file rusak/hilang)
    $default_db = [
        'setting' => ['nama' => 'Masjid Al-Ikhlas', 'lokasi' => 'Selamat Datang', 'latitude'=>-6.2, 'longitude'=>106.8, 'timeZone'=>7, 'dst'=>'0'],
        'timer' => ['info'=>5, 'wallpaper'=>10, 'wait_adzan'=>1, 'adzan'=>2, 'sholat'=>10],
        'prayTimesMethod' => '0',
        'prayName' => ['fajr'=>'Subuh','sunrise'=>'Syuruq','dhuhr'=>'Dzuhur','asr'=>'Ashar','maghrib'=>'Maghrib','isha'=>'Isya'],
        'info' => [],
        'running_text' => ['Selamat Datang di Masjid'],
        'iqomah' => [],
        'jumat' => ['active'=>true, 'duration'=>60, 'text'=>'Khutbah'],
        'youtube_display' => ['active'=>'Tidak']
    ];

    $db = $default_db; // Set awal ke default

    // Cek file JSON
    if (file_exists($file)){
        $json_content = file_get_contents($file);
        $decoded_data = json_decode($json_content, true);
        
        // Hanya pakai data file jika JSON valid (tidak null/rusak)
        if($decoded_data !== null){
            $db = $decoded_data;
        }
    }

    
// --- CONFIG VARIABLES ---
    $info_timer         = isset($db['timer']['info']) ? $db['timer']['info'] * 1000 : 5000; 
    $wallpaper_timer    = isset($db['timer']['wallpaper']) ? $db['timer']['wallpaper'] * 1000 : 10000; 
    $wait_adzan_min     = isset($db['timer']['wait_adzan']) ? (int)$db['timer']['wait_adzan'] : 1;

    if(!empty($db['setting']['nama'])) {
        $nama_masjid = $db['setting']['nama'];
    } 
    // 2. Cek di identitas->nama (JSON Lama/Cadangan)
    elseif(!empty($db['identitas']['nama'])) {
        $nama_masjid = $db['identitas']['nama'];
    } 
    // 3. Jika kosong semua, pakai default
    else {
        $nama_masjid = "Masjid Al-Ikhlas";
    }

// Ambil Sub Info (Alamat/Lokasi)
    if(!empty($db['setting']['lokasi'])) {
        $sub_info = $db['setting']['lokasi'];
    } elseif(!empty($db['identitas']['alamat'])) {
        $sub_info = $db['identitas']['alamat'];
    } else {
        $sub_info = "Selamat Datang Para Jamaah";
    }
    
    // --- LOAD ASSETS (LOGO & WALLPAPER) ---
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
    <title>Display Masjid - Cinematic Fixed Layout</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700;800&family=Roboto+Condensed:wght@400;700&family=Amiri&family=Playfair+Display:ital@0;1&display=swap" rel="stylesheet">

    <style>
        /* ==================== GLOBAL CONFIG ==================== */
        :root {
            --gold: #f39c12;
            --gold-glow: #ffce54;
            --glass-bg: rgba(20, 20, 20, 0.65);
            --glass-border: rgba(255, 255, 255, 0.15);
            --running-bg: #0b2526; 
        }

        * { box-sizing: border-box; }

        body { 
            margin: 0; padding: 0; width: 100vw; height: 100vh; 
            overflow: hidden; font-family: 'Montserrat', sans-serif; background: #000; 
            color: #fff;
        }

        /* ==================== MODE VIDEO (UPDATED) ==================== */
        
        /* 1. HANYA Sembunyikan Quote Container (Countdown dihapus dari sini) */
        body.mode-video #quote-container {
            opacity: 0;
            visibility: hidden;
            transition: all 0.5s ease;
            height: 0; margin: 0; padding: 0; /* Collapse dimensi agar tidak makan tempat */
        }

        /* 2. Pindahkan Jam ke BAWAH TANGGAL (Pojok Kanan Atas) */
        body.mode-video #center-content {
            top: 130px;       
            bottom: auto;     
            left: auto;       
            right: 50px;      
            
            transform: none;  
            width: auto;      
            align-items: flex-end; /* Ratakan kanan */
            transition: all 0.8s ease;
        }

        /* 3. Ukuran Jam Mode Video */
        body.mode-video #jam {
            font-size: 6rem; 
            text-shadow: 2px 2px 5px rgba(0,0,0,0.9);
            line-height: 1;
        }
        
        body.mode-video #jam span {
            font-size: 2.5rem;
            margin-top: 5px;
        }

        /* 4. [BARU] Atur Tampilan Countdown saat Mode Video */
        /* Agar ukurannya mengecil dan pas di bawah jam */
        body.mode-video #countdown-box {
            display: flex; /* Pastikan tetap flex */
            width: 300px;  /* Lebar lebih kecil */
            height: auto;  /* Tinggi otomatis */
            padding: 15px;
            margin-top: 10px; /* Jarak dari jam */
            background: rgba(0,0,0,0.5); /* Lebih transparan */
            border: 2px solid var(--gold);
        }

        body.mode-video #countdown-box h3 {
            font-size: 1.2rem; /* Judul lebih kecil */
            letter-spacing: 2px;
            margin-bottom: 5px;
        }

        body.mode-video #countdown-box .cd-val {
            font-size: 3.5rem; /* Angka timer lebih kecil */
            text-shadow: 2px 2px 5px #000;
        }

        /* ==================== BACKGROUND LAYER ==================== */
        #bg-layer { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 0; }
        
        #bg-layer::after {
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(to bottom, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0.2) 40%, rgba(0,0,0,0.85) 100%);
            z-index: 1; pointer-events: none;
        }
        
        .carousel, .carousel-inner, .item, .wp-image, .video-wrapper { width: 100%; height: 100%; }
        .wp-image { background-size: cover; background-position: center; transition: transform 20s ease; }
        .item.active .wp-image { transform: scale(1.1); } 
        video { object-fit: cover; width: 100%; height: 100%; }

        /* ==================== HEADER (TOP BAR) ==================== */
        header {
            position: absolute; top: 0; left: 0; width: 100%; height: 120px; z-index: 10;
            display: flex; justify-content: space-between; align-items: center;
            padding: 0 50px;
            background: linear-gradient(to bottom, #000 0%, transparent 100%);
        }

        /* Logo Area */
        .header-left { display: flex; align-items: center; gap: 20px; }
        .logo-img { 
            width: 80px; height: 80px; background-size: contain; background-repeat: no-repeat; 
            filter: drop-shadow(0 0 10px rgba(255,255,255,0.3));
        }
        .masjid-info h1 { font-size: 2rem; margin: 0; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; color: #fff; text-shadow: 2px 2px 4px #000; }
        .masjid-info p { margin: 0; font-size: 1rem; color: var(--gold); letter-spacing: 3px; text-transform: uppercase; font-weight: 600; }

        /* Date Area (DIPERBESAR) */
        .header-right { text-align: right; }
        .date-masehi { 
            font-size: 2rem; /* Diperbesar dari 1.5rem */
            font-weight: 700; color: #fff; text-shadow: 2px 2px 4px #000; 
        }
        .date-hijri { 
            font-family: 'Playfair Display', serif; font-style: italic; color: var(--gold); 
            font-size: 1.6rem; /* Diperbesar dari 1.4rem */
            margin-top: 5px;
        }

        /* ==================== CENTER CONTENT (CLOCK & QUOTES) ==================== */
        #center-content {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -55%);
            z-index: 10; width: 100%; 
            display: flex; flex-direction: column; align-items: center; justify-content: center;
        }

        #jam-wrapper {
            margin-bottom: 25px; 
            position: relative;
            text-align: center;
        }
        #jam {
            font-size: 8rem; font-weight: 700; line-height: 1;
            font-family: 'Roboto Condensed', sans-serif;
            color: #fff;
            text-shadow: 0 10px 30px rgba(0,0,0,0.8);
            letter-spacing: -3px;
            transition: font-size 0.5s ease; /* Smooth transition */
        }
        #jam span { font-size: 3rem; color: var(--gold); font-weight: 400; margin-left: 10px; vertical-align: top; margin-top: 15px; display: inline-block; }

        /* --- QUOTE CONTAINER --- */
        #quote-container {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            width: 100%; max-width: 700px; height: 250px; 
            padding: 0 40px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            transition: all 0.5s;
            overflow: hidden; 
            display: flex; align-items: center; justify-content: center;
        }

        #quote-container .carousel, 
        #quote-container .carousel-inner { width: 100%; height: 100%; }
        #quote-container .item { height: 100%; width: 100%; display: none; }
        #quote-container .item.active {
            display: flex !important; flex-direction: column;
            justify-content: center; align-items: center; 
        }

        .q-arab { 
            font-family: 'Amiri', serif; font-size: 2.2rem; color: var(--gold); 
            margin-bottom: 15px; line-height: 1.4; text-align: center;
            max-height: 140px; overflow: hidden; 
        }
        .q-text { 
            font-family: 'Montserrat', sans-serif; font-size: 1.5rem; color: #eee; 
            line-height: 1.4; font-weight: 400; text-align: center;
        }
        .q-ref { 
            margin-top: 20px; font-size: 1rem; color: #aaa; text-transform: uppercase; 
            letter-spacing: 2px; font-weight: 700; text-align: center;
        }
        .hadist-line { height: 2px; width: 100px; background: var(--gold); margin: 10px auto; opacity: 0.7;}

        /* Countdown Box */
        #countdown-box {
            display: none;
            background: rgba(0,0,0,0.85);
            border: 3px solid var(--gold);
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 0 50px rgba(243, 156, 18, 0.4);
            background-image: url('img/bg-pattern-02.png'); background-size: 100px;
            width: 90%; max-width: 1100px; height: 320px; 
            flex-direction: column; justify-content: center; align-items: center;
        }
        #countdown-box h3 { margin: 0; font-size: 2rem; letter-spacing: 5px; color: #fff; text-transform: uppercase; margin-bottom: 10px; }
        #countdown-box .cd-val { font-size: 8rem; font-weight: bold; color: var(--gold); font-family: 'Roboto Condensed'; line-height: 1; text-shadow: 0 0 20px var(--gold); }

        /* ==================== FOOTER (PRAYER TIMES & RUNNING TEXT) ==================== */
        footer {
            position: absolute; bottom: 0; left: 0; width: 100%; z-index: 20;
            display: flex; flex-direction: column;
        }

        #jadwal-wrapper {
            display: flex; justify-content: center; gap: 15px;
            padding: 0 40px 25px 40px; width: 100%;
        }

        .jadwal-card {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            flex: 1; max-width: 200px;
            text-align: center;
            padding: 15px 10px;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s, background 0.3s;
        }

        .jadwal-card .nama { font-size: 1rem; text-transform: uppercase; color: #bbb; margin-bottom: 5px; font-weight: 600; letter-spacing: 1px; }
        .jadwal-card .waktu { font-size: 2.2rem; font-weight: 700; color: #fff; font-family: 'Roboto Condensed', sans-serif; }

        .jadwal-card.active {
            background: rgba(243, 156, 18, 0.9);
            border: 2px solid #fff;
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(243, 156, 18, 0.5);
        }
        .jadwal-card.active .nama { color: #000; }
        .jadwal-card.active .waktu { color: #000; text-shadow: none; }
        .jadwal-card.active::before {
            content: ''; position: absolute; top:0; left:0; width:100%; height:100%;
            background-image: url('img/bg-pattern-01.png'); background-size: 200px; opacity: 0.1;
        }

        /* Running Text (UKURAN DIPERBESAR) */
        #running-text-container {
            width: 100%; 
            height: 60px; /* Diperbesar dari 50px */
            background: var(--running-bg);
            border-top: 3px solid var(--gold);
            display: flex; align-items: center;
            position: relative;
            background-image: url('img/bg-pattern-01.png'); background-size: 300px; background-blend-mode: soft-light;
        }
        .rt-label {
            background: var(--gold); color: #000; height: 100%; padding: 0 30px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; 
            font-size: 1.8rem; /* Font label lebih besar */
            z-index: 2;
            box-shadow: 5px 0 10px rgba(0,0,0,0.5);
        }
        .rt-content { 
            flex-grow: 1; overflow: hidden; color: #fff; 
            font-size: 2.5rem; /* Font text lebih besar agar terbaca lansia */
            font-weight: 500; 
            line-height: 30px; /* Vertikal center */
        }

        /* ==================== OVERLAYS ==================== */
        .full-screen-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            z-index: 99999; background: #000; display: none;
            background-size: cover; background-position: center;
            flex-direction: column; justify-content: center; align-items: center;
        }
        #display-adzan { background-image: url('img/bg-adzan.png'); }
        #display-sholat { background-image: url('img/bg-sholat.png'); }
        #display-khutbah { background-image: url('img/bg-kubah.png'); }
        #display-syuruq { background-image: url('img/bg-syuruq.png'); }
        
        #display-iqomah { background: #111; background-image: url('img/bg-pattern-02.png'); background-size: 150px; }
        .iqomah-circle {
            width: 35vw; height: 35vw; max-width: 400px; max-height: 400px;
            border: 10px solid var(--gold); border-radius: 50%;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            background: rgba(0,0,0,0.8); box-shadow: 0 0 50px var(--gold);
            animation: pulse 2s infinite;
        }
        .iqomah-title { font-size: 2rem; color: var(--gold); margin-bottom: 10px; letter-spacing: 5px; }
        .iqomah-time { font-size: 8rem; color: #fff; font-family: 'Roboto Condensed'; font-weight: bold; line-height: 0.9; }

        @keyframes pulse { 0% { box-shadow: 0 0 30px var(--gold); } 50% { box-shadow: 0 0 60px var(--gold); } 100% { box-shadow: 0 0 30px var(--gold); } }

        #display-youtube { position:fixed; top:0; left:0; width:100%; height:100%; z-index:99998; background:#000; display:none; }
        iframe { border:0; width:100%; height:100%; }

    </style>
</head>
<body>
    <audio id="adzan-beep" src="img/beep.mp3" preload="auto"></audio>
    <div id="preloader" style="position:fixed; top:0; left:0; width:100%; height:100%; background:#000; z-index:100000;"></div>

    <div id="display-adzan" class="full-screen-overlay"></div>
    <div id="display-sholat" class="full-screen-overlay"></div>
    <div id="display-khutbah" class="full-screen-overlay"></div>
    <div id="display-syuruq" class="full-screen-overlay"></div>
    <div id="display-iqomah" class="full-screen-overlay">
        <div class="iqomah-circle">
            <div class="iqomah-title">IQOMAH</div>
            <div class="iqomah-time" id="iqomah-val">00:00</div>
        </div>
    </div>
    <div id="display-youtube"><iframe src="" allow="autoplay"></iframe></div>

    <div id="bg-layer">
        <div id="main-wallpaper-carousel" class="carousel slide" data-ride="carousel" data-interval="false">
            <div class="carousel-inner"><?=$wallpaper?></div>
        </div>
    </div>

    <header>
        <div class="header-left">
            <div class="logo-img" style="background-image: url('logo/<?=$logo?>');"></div>
            <div class="masjid-info">
                <h1><?= htmlentities($nama_masjid) ?></h1> 
                <p><?= htmlentities($sub_info) ?></p>
            </div>
        </div>
        <div class="header-right">
            <div class="date-masehi" id="tgl">...</div>
            <div class="date-hijri" id="hijri-txt">...</div>
        </div>
    </header>

    <div id="center-content">
        <div id="jam-wrapper">
            <div id="jam">00:00<span>00</span></div>
        </div>

        <div id="quote-container">
            <div id="carousel-quote" class="carousel slide" data-ride="carousel" data-interval="false">
                <div class="carousel-inner">
                    <?php 
                        $idx = 0;
                        if(isset($db['info']) && is_array($db['info'])){
                            foreach($db['info'] as $info){
                                if(isset($info[3]) && $info[3] == 1){
                                    $act = ($idx==0)?'active':'';
                                    echo '<div class="item '.$act.'">';
                                    echo '<div class="q-arab">'.htmlentities($info[0]).'</div>';
                                    echo '<div class="q-text">'.nl2br(htmlentities($info[1])).'</div>';
                                    echo '<div class="hadist-line"></div>';
                                    echo '<div class="q-ref">'.htmlentities($info[2]).'</div>';
                                    echo '</div>';
                                    $idx++;
                                }
                            }
                        }
                        if($idx==0) echo '<div class="item active"><div class="q-text" style="font-size:2rem; font-weight:600;">Lurus dan Rapatkan Shaf</div></div>';
                    ?>
                </div>
            </div>
        </div>

        <div id="countdown-box">
            <h3 id="rc-title">MENUJU ADZAN</h3>
            <div class="cd-val" id="rc-timer">00:00:00</div>
        </div>
    </div>

    <footer>
        <div id="jadwal-wrapper">
            </div>

        <div id="running-text-container">
            <div class="rt-label"><i class="fa fa-info-circle"></i></div>
            <div class="rt-content">
                <marquee>
                    <?php 
                        if(isset($db['running_text'])){
                            foreach($db['running_text'] as $txt){
                                echo '<i class="fa fa-star" style="color:var(--gold); margin:0 15px;"></i> '.htmlentities($txt).' ';
                            }
                        }
                        $ip = gethostbyname(gethostname());
                        if(PHP_OS=='Linux') $ip = trim(exec("/sbin/ifconfig wlan0 | grep 'inet '| cut -d 't' -f2 | cut -d 'n' -f1 | awk '{ print $1}'"));
                        if(isset($db['akses']['pass']) && $db['akses']['pass']=='admin') echo '| IP Admin: http://'.$ip.'/';
                    ?>
                </marquee>
            </div>
        </div>
    </footer>

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
                    // Generate Horizontal Cards HTML
                    html += `<div class="jadwal-card ${activeClass}">
                                <div class="nama">${v}</div>
                                <div class="waktu">${tToday[k]}</div>
                             </div>`;
                    app.checkTrigger(k, v, pTime, now);
                });
                if (!foundNext) {
                    let pTime = moment(tTom.fajr, 'HH:mm').add(1, 'd'); nextName = 'Subuh'; nextTime = pTime;
                }
                if(html) $('#jadwal-wrapper').html(html);
                if (nextTime) app.handleCountdown(nextTime, nextName);
            },

            handleCountdown: function(targetTime, name) {
                // Jangan tampilkan countdown jika ada overlay atau youtube
                if ($('.full-screen-overlay:visible').length > 0 || $('#display-youtube:visible').length > 0) {
                    $('#countdown-box').hide(); return;
                }

                let diff = targetTime.diff(moment(), 'seconds');
                if (diff < 0) { $('#countdown-box').hide(); return; }

                let dur = moment.duration(diff, 'seconds');
                let h = Math.floor(dur.asHours());
                let m = dur.minutes();
                let s = dur.seconds();
                let txt = (h<10?'0'+h:h) + ":" + (m<10?'0'+m:m) + ":" + (s<10?'0'+s:s);

                // Logic Switch Quote / Countdown
                let thresholdSec = waitAdzanMin * 60; 
                if (diff <= thresholdSec) {
                    // Waktu sudah dekat: Tampilkan Countdown
                    if($('#quote-container').is(':visible')) $('#quote-container').hide();
                    
                    // Gunakan flex agar tetap di tengah
                    $('#countdown-box').css('display', 'flex'); 
                    
                    $('#rc-title').text("MENUJU " + name.toUpperCase());
                    $('#rc-timer').text(txt);
                } else {
                    // Masih lama: Tampilkan Quote
                    $('#countdown-box').hide();
                    if($('#quote-container').is(':hidden')) $('#quote-container').fadeIn();
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
                $('.full-screen-overlay').hide();
                $('#display-iqomah').css('display','flex').hide().fadeIn();
                
                let iqomahMin = parseInt(app.db.iqomah[key]) || 10;
                let adzanMin = parseInt(app.db.timer.adzan);
                let endTime = moment(pTimeObj).add(adzanMin + iqomahMin, 'minutes');
                
                clearInterval(app.iqomahInterval);
                app.iqomahInterval = setInterval(function(){
                    let now = moment();
                    let diff = endTime.diff(now, 'seconds');
                    
                    if(diff <= 0){
                        clearInterval(app.iqomahInterval);
                        $('#display-iqomah').fadeOut();
                        app.playAudio(); 
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

                // Fungsi bantu untuk pindah layout
                function toggleVideoMode(isVideo) {
                    if (isVideo) {
                        $('body').addClass('mode-video');
                    } else {
                        $('body').removeClass('mode-video');
                    }
                }

                function next() { wp.carousel('next'); }

                // Event saat slide berganti
                wp.on('slid.bs.carousel', function (e) {
                    let slide = $(e.relatedTarget);
                    let isVideo = slide.attr('data-is-video') === 'true';
                    
                    // Panggil fungsi ganti layout
                    toggleVideoMode(isVideo);

                    clearTimeout(tId);
                    
                    if(isVideo) {
                        let v = slide.find('video')[0];
                        v.currentTime = 0; 
                        v.play();
                        v.onended = function() { wp.carousel('next'); };
                    } else {
                        tId = setTimeout(next, timer);
                    }
                });
                
                // Cek slide pertama saat load
                let first = wp.find('.item.active');
                let isFirstVideo = first.attr('data-is-video') === 'true';
                
                // Set layout awal
                toggleVideoMode(isFirstVideo);

                if(isFirstVideo){
                    first.find('video')[0].play();
                    first.find('video')[0].onended = function() { wp.carousel('next'); };
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