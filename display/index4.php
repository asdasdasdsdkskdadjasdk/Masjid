<?php
    // File: display/index5.php (Updated Color #105c5d & Pattern Fix)
    
    // --- 1. LOAD DATABASE ---
    $file   = '../db/database.json';
    
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

    $db = $default_db; 

    if (file_exists($file)){
        $json_content = file_get_contents($file);
        $decoded_data = json_decode($json_content, true);
        if($decoded_data !== null){ $db = $decoded_data; }
    }
    
    // --- CONFIG VARIABLES ---
    $info_timer         = isset($db['timer']['info']) ? $db['timer']['info'] * 1000 : 5000; 
    $wallpaper_timer    = isset($db['timer']['wallpaper']) ? $db['timer']['wallpaper'] * 1000 : 10000; 
    $wait_adzan_min     = isset($db['timer']['wait_adzan']) ? (int)$db['timer']['wait_adzan'] : 1;

    // --- AMBIL NAMA MASJID ---
    $nama_masjid = "Masjid Al-Ikhlas";
    if(!empty($db['setting']['nama'])) { $nama_masjid = $db['setting']['nama']; } 
    elseif(!empty($db['identitas']['nama'])) { $nama_masjid = $db['identitas']['nama']; } 

    $sub_info = "Selamat Datang Para Jamaah";
    if(!empty($db['setting']['lokasi'])) { $sub_info = $db['setting']['lokasi']; } 
    elseif(!empty($db['identitas']['alamat'])) { $sub_info = $db['identitas']['alamat']; }
    
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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700;800&family=Oswald:wght@400;700&family=Amiri&family=Playfair+Display:ital@0;1&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-dark: #050505;
            --sidebar-bg: #101010;
            --gold: #d4af37; 
            /* --- UPDATE WARNA DISINI --- */
            --theme-green: #105c5d;      /* Warna Utama Baru */
            --theme-green-dark: #0b4041; /* Versi lebih gelap untuk border/bg */
            --text-white: #ffffff;
            --text-gray: #bbbbbb;
        }

        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; width: 100vw; height: 100vh; overflow: hidden; font-family: 'Montserrat', sans-serif; background: var(--bg-dark); color: var(--text-white); }

        #container { display: flex; width: 100%; height: 100%; }

        /* --- VISUAL AREA (LEFT - 75%) --- */
        #visual-area {
            flex: 75; 
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        #bg-layer { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 0; }
        .carousel, .carousel-inner, .item, .wp-image, .video-wrapper { width: 100%; height: 100%; }
        .wp-image { background-size: cover; background-position: center; transition: transform 20s ease; }
        .item.active .wp-image { transform: scale(1.1); }
        video { object-fit: cover; width: 100%; height: 100%; }

        #visual-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(to top, rgba(0,0,0,0.95) 0%, rgba(0,0,0,0.5) 100%);
            z-index: 1; pointer-events: none;
        }

        /* Header Kiri Atas */
        #top-left-header {
            position: absolute; top: 40px; left: 50px; z-index: 25;
            display: flex; align-items: center; gap: 20px;
        }
        #top-left-header .logo-img { 
            width: 90px; height: 90px; flex-shrink: 0;
            background-size: contain; background-repeat: no-repeat; 
            filter: drop-shadow(0 2px 5px rgba(0,0,0,0.8));
        }
        #top-left-header .masjid-text { text-align: left; }
        #top-left-header .masjid-title { 
            font-family: 'Oswald', sans-serif; font-size: 2.2rem; font-weight: 700; 
            color: var(--gold); text-transform: uppercase; line-height: 1; margin: 0; 
            text-shadow: 2px 2px 5px #000;
        }
        #top-left-header .masjid-addr { 
            font-size: 1.2rem; color: #ddd; margin: 0; margin-top: 5px; 
            text-shadow: 1px 1px 3px #000;
        }

        /* Quote */
        #quote-wrapper {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            width: 85%; text-align: center; z-index: 10;
        }
        .q-arab { font-family: 'Amiri', serif; font-size: 3rem; color: var(--gold); margin-bottom: 15px; text-shadow: 2px 2px 8px #000; line-height: 1.6; }
        
        .hadist-line-img {
            display: block;
            margin: 10px auto 20px auto;
            max-width: 300px;
            height: auto;
            filter: drop-shadow(0 0 5px #000);
        }

        .q-text { font-size: 2rem; line-height: 1.5; font-weight: 600; text-shadow: 2px 2px 8px #000; color: #fff; }
        .q-ref { margin-top: 25px; font-size: 1.2rem; color: #ccc; text-transform: uppercase; letter-spacing: 2px; text-shadow: 1px 1px 5px #000; font-weight: 700; }

        /* Countdown Box */
        #countdown-box {
            display: none;
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            z-index: 15;
            background-color: rgba(0, 0, 0, 0.9);
            background-image: url('img/bg-pattern-02.png'); 
            background-size: 100px;
            
            border: 3px solid var(--gold);
            border-radius: 20px;
            padding: 40px 60px;
            text-align: center;
            flex-direction: column; align-items: center;
            box-shadow: 0 0 50px rgba(212, 175, 55, 0.4);
        }
        #countdown-box h3 { margin: 0; font-size: 2rem; letter-spacing: 5px; color: #fff; text-transform: uppercase; margin-bottom: 10px; text-shadow: 2px 2px 5px #000; }
        #countdown-box .cd-val { font-size: 7rem; font-weight: 700; color: var(--gold); font-family: 'Oswald', sans-serif; line-height: 1; text-shadow: 3px 3px 10px #000; }

        /* --- RUNNING TEXT (WARNA BARU) --- */
        #running-text-container {
            position: absolute; bottom: 0; left: 0; width: 100%; height: 70px;
            /* Background Menggunakan RGB dari #105c5d (R:16, G:92, B:93) */
            background: rgba(16, 92, 93, 0.9); 
            border-top: 3px solid var(--theme-green); 
            display: flex; align-items: center; z-index: 20;
        }
        .rt-label {
            width: 80px; height: 100%; 
            background: var(--theme-green-dark); /* Warna lebih gelap */
            display: flex; align-items: center; justify-content: center;
            box-shadow: 5px 0 10px rgba(0,0,0,0.3); z-index: 2;
        }
        /* Filter icon label */
        .rt-label img { filter: brightness(0) invert(1); }

        .rt-content { 
            flex-grow: 1; overflow: hidden; color: #fff; 
            font-size: 2rem; font-weight: 600; line-height: 70px; 
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5); 
            display: flex; align-items: center; 
        }
        /* Style untuk gambar pattern01.png di dalam teks berjalan */
        .rt-pattern-icon {
            height: 28px; 
            width: auto; 
            margin: 0 15px;
            filter: brightness(0) invert(1); /* Putih */
            vertical-align: middle;
            display: inline-block; /* Pastikan display inline-block */
        }


        /* --- SIDEBAR (RIGHT - 25%) --- */
        #info-sidebar {
            flex: 25;
            background-color: var(--sidebar-bg);
            background-image: url('img/bg-pattern-01.png');
            background-repeat: repeat;
            background-size: 350px;
            background-blend-mode: lighten;
            opacity: 1;
            
            border-left: 2px solid #333;
            display: flex;
            flex-direction: column;
            padding: 10px; 
            z-index: 30;
            box-shadow: -10px 0 40px rgba(0,0,0,0.9);
            justify-content: flex-start; 
        }

        /* ANALOG CLOCK */
        .clock-container {
            flex-grow: 0; display: flex; flex-direction: column; align-items: center;
            margin-bottom: 5px; margin-top: 30px; 
        }

        .analog-clock {
            width: 220px; height: 220px; border-radius: 50%;
            border: 5px solid var(--gold);
            background: radial-gradient(circle, #222 0%, #000 100%);
            position: relative;
            box-shadow: 0 0 25px rgba(212, 175, 55, 0.2);
        }
        .analog-clock::after { 
            content: ''; position: absolute; width: 12px; height: 12px;
            background: var(--gold); border-radius: 50%;
            top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10;
        }
        
        .analog-clock .num {
            position: absolute; inset: 12px; text-align: center;
            transform: rotate(calc(30deg * var(--i)));
            color: #fff; font-family: 'Oswald', sans-serif; font-weight: 700; font-size: 1.2rem;
        }
        .analog-clock .num b { display: inline-block; transform: rotate(calc(-30deg * var(--i))); color: var(--gold); }
        
        .hand { position: absolute; bottom: 50%; left: 50%; transform-origin: bottom center; border-radius: 5px; z-index: 5; }
        .hand.hour { width: 6px; height: 55px; background: #fff; transform: translateX(-50%); }
        .hand.minute { width: 4px; height: 80px; background: #ccc; transform: translateX(-50%); }
        .hand.second { width: 2px; height: 90px; background: var(--gold); transform: translateX(-50%); z-index: 6; }

        /* Tanggal Besar */
        .date-masehi { 
            font-size: 2.2rem; font-weight: 800; margin-top: 25px; color: #fff; text-align: center; letter-spacing: 1px; line-height: 1.1;
            text-shadow: 2px 2px 5px #000;
        }
        .date-hijri { 
            font-family: 'Playfair Display', serif; font-style: italic; color: var(--gold); 
            font-size: 1.7rem; margin-top: 8px; text-align: center; 
        }

        .clock-line-img {
            display: block; margin: 5px auto 5px auto; width: 60%; height: auto;
            opacity: 0.8; filter: drop-shadow(0 2px 5px #000);
        }

        /* --- JADWAL LIST (WARNA BARU) --- */
        #jadwal-wrapper {
            flex-grow: 1; display: flex; flex-direction: column; 
            gap: 5px; padding-top: 5px; justify-content: flex-start;
        }
        .jadwal-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 5px 15px; 
            background: rgba(0, 0, 0, 0.6); 
            border-left: 4px solid transparent;
            border-radius: 6px;
            transition: all 0.3s;
        }
        .jadwal-row .nama { font-size: 1.6rem; font-weight: 700; text-transform: uppercase; color: #bbb; }
        .jadwal-row .waktu { font-size: 2.8rem; font-weight: 800; font-family: 'Oswald', sans-serif; color: #fff; letter-spacing: 1px; }

        /* State Aktif Hijau Baru */
        .jadwal-row.active {
            background: var(--theme-green); /* Tint warna baru */
            border-left: 4px solid var(--theme-green); 
            box-shadow: 0 5px 15px rgba(16, 92, 93, 0.2);
            transform: scale(1.02); 
        }
        .jadwal-row.active .nama { color: #FFF; } 
        .jadwal-row.active .waktu { color: #FFF; } 

        /* Video Mode */
        body.mode-video #quote-wrapper { display: none !important; }

        /* Overlays */
        .full-screen-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 99999; background: #000; display: none; background-size: cover; background-position: center; flex-direction: column; justify-content: center; align-items: center; }
        #display-adzan { background-image: url('img/bg-adzan.png'); }
        #display-sholat { background-image: url('img/bg-sholat.png'); }
        #display-khutbah { background-image: url('img/bg-kubah.png'); }
        #display-syuruq { background-image: url('img/bg-syuruq.png'); }
        #display-iqomah { background: #111; background-image: url('img/bg-pattern-02.png'); background-size: 150px; }
        .iqomah-circle { width: 400px; height: 400px; border: 10px solid var(--gold); border-radius: 50%; display: flex; flex-direction: column; justify-content: center; align-items: center; background: rgba(0,0,0,0.8); box-shadow: 0 0 50px var(--gold); animation: pulse 2s infinite; }
        .iqomah-title { font-size: 2.5rem; color: var(--gold); margin-bottom: 10px; letter-spacing: 5px; }
        .iqomah-time { font-size: 8rem; color: #fff; font-family: 'Oswald'; font-weight: bold; }
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
        <div class="iqomah-circle"><div class="iqomah-title">IQOMAH</div><div class="iqomah-time" id="iqomah-val">00:00</div></div>
    </div>
    <div id="display-youtube"><iframe src="" allow="autoplay"></iframe></div>

    <div id="container">
        
        <div id="visual-area">
            <div id="bg-layer">
                <div id="main-wallpaper-carousel" class="carousel slide" data-ride="carousel" data-interval="false">
                    <div class="carousel-inner"><?=$wallpaper?></div>
                </div>
                <div id="visual-overlay"></div>
            </div>

            <div id="top-left-header">
                <div class="logo-img" style="background-image: url('logo/<?=$logo?>');"></div>
                <div class="masjid-text">
                    <div class="masjid-title"><?php echo htmlentities($nama_masjid); ?></div>
                    <div class="masjid-addr"><?php echo htmlentities($sub_info); ?></div>
                </div>
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
                                        
                                        // HADIST LINE IMAGE
                                        echo '<img src="img/hadist-line.png" class="hadist-line-img" alt="line">';
                                        
                                        echo '<div class="q-text">'.nl2br(htmlentities($info[1])).'</div>';
                                        echo '<div class="q-ref">'.htmlentities($info[2]).'</div>';
                                        echo '</div>';
                                        $idx++;
                                    }
                                }
                            }
                            if($idx==0) echo '<div class="item active"><div class="q-text" style="font-size:2.5rem; font-weight:700;">Lurus dan Rapatkan Shaf</div></div>';
                        ?>
                    </div>
                </div>
            </div>

            <div id="countdown-box">
                <h3 id="rc-title">MENUJU ADZAN</h3>
                <div class="cd-val" id="rc-timer">00:00:00</div>
            </div>

            <div id="running-text-container">
                <div class="rt-label">
                    <img src="img/pattern01.png" alt="icon" style="height: 40px; width: auto;">
                </div>
                <div class="rt-content">
                    <marquee>
                        <?php 
                            if(isset($db['running_text'])){
                                foreach($db['running_text'] as $txt){
                                    // PEMBATAS TEKS MENGGUNAKAN pattern01.png
                                    // Pastikan file 'pattern01.png' ada di dalam folder 'display/img/'
                                    echo '<img src="img/pattern01.png" class="rt-pattern-icon" alt="separator"> '.htmlentities($txt).' ';
                                }
                            }
                            $ip = gethostbyname(gethostname());
                            if(PHP_OS=='Linux') $ip = trim(exec("/sbin/ifconfig wlan0 | grep 'inet '| cut -d 't' -f2 | cut -d 'n' -f1 | awk '{ print $1}'"));
                            if(isset($db['akses']['pass']) && $db['akses']['pass']=='admin') echo '| IP Admin: http://'.$ip.'/';
                        ?>
                    </marquee>
                </div>
            </div>
        </div>

        <aside id="info-sidebar">
            <div class="clock-container">
                <div class="analog-clock">
                    <span class="num" style="--i:1;"><b>1</b></span>
                    <span class="num" style="--i:2;"><b>2</b></span>
                    <span class="num" style="--i:3;"><b>3</b></span>
                    <span class="num" style="--i:4;"><b>4</b></span>
                    <span class="num" style="--i:5;"><b>5</b></span>
                    <span class="num" style="--i:6;"><b>6</b></span>
                    <span class="num" style="--i:7;"><b>7</b></span>
                    <span class="num" style="--i:8;"><b>8</b></span>
                    <span class="num" style="--i:9;"><b>9</b></span>
                    <span class="num" style="--i:10;"><b>10</b></span>
                    <span class="num" style="--i:11;"><b>11</b></span>
                    <span class="num" style="--i:12;"><b>12</b></span>

                    <div class="hand hour" id="hand-hour"></div>
                    <div class="hand minute" id="hand-min"></div>
                    <div class="hand second" id="hand-sec"></div>
                </div>
                
                <div class="date-masehi" id="tgl">...</div>
                <div class="date-hijri" id="hijri-txt">...</div>
                
                <img src="img/clock-line.png" class="clock-line-img" alt="line">
            </div>

            <div id="jadwal-wrapper">
                </div>
        </aside>

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
                
                // Update Analog Clock
                const seconds = now.seconds();
                const minutes = now.minutes();
                const hours = now.hours();
                
                const secDeg = ((seconds / 60) * 360);
                const minDeg = ((minutes / 60) * 360) + ((seconds/60)*6);
                const hourDeg = ((hours / 12) * 360) + ((minutes/60)*30);

                $('#hand-sec').css('transform', `translateX(-50%) rotate(${secDeg}deg)`);
                $('#hand-min').css('transform', `translateX(-50%) rotate(${minDeg}deg)`);
                $('#hand-hour').css('transform', `translateX(-50%) rotate(${hourDeg}deg)`);

                // Update Text Date
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
                    html += `<div class="jadwal-row ${activeClass}">
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

                let thresholdSec = waitAdzanMin * 60; 
                if (diff <= thresholdSec) {
                    if($('#quote-wrapper').is(':visible')) $('#quote-wrapper').hide();
                    $('#countdown-box').css('display', 'flex'); 
                    $('#rc-title').text("MENUJU " + name.toUpperCase());
                    $('#rc-timer').text(txt);
                } else {
                    $('#countdown-box').hide();
                    if(!$('body').hasClass('mode-video')) {
                        if($('#quote-wrapper').is(':hidden')) $('#quote-wrapper').fadeIn();
                    }
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

                function toggleVideoMode(isVideo) {
                    if (isVideo) {
                        $('body').addClass('mode-video');
                        $('#quote-wrapper').fadeOut();
                    } else {
                        $('body').removeClass('mode-video');
                        if($('#countdown-box').is(':hidden')) $('#quote-wrapper').fadeIn();
                    }
                }

                function next() { wp.carousel('next'); }

                wp.on('slid.bs.carousel', function (e) {
                    let slide = $(e.relatedTarget);
                    let isVideo = slide.attr('data-is-video') === 'true';
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
                
                let first = wp.find('.item.active');
                let isFirstVideo = first.attr('data-is-video') === 'true';
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