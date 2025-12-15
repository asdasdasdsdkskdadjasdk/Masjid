<?php
    // File: display/index.php
    // var_dump(PHP_OS);
    // die;
    $file   = '../db/database.json';
    if (!file_exists($file)){
        echo "<h1>Jalankan admin terlebih dahulu</h1>";
        die;
    }
    $json   = file_get_contents($file);
    $db     = json_decode($json, true);
    $showDb = $db;
    unset($showDb['akses']);
    
    $info_timer         = $db['timer']['info']      * 1000; //detik
    $wallpaper_timer    = $db['timer']['wallpaper'] * 1000; 
    $adzan_timer        = $db['timer']['adzan']     * 1000 * 60; //menit
    // $iqomah_timer    = $db['timer']['iqomah']    * 1000 * 60;
    $sholat_timer       = $db['timer']['sholat']    * 1000 * 60;
    
    //optional
    $khutbah_jumat      = $db['jumat']['duration']  * 1000 * 60;
    $sholat_tarawih     = $db['tarawih']['duration']    * 1000 * 60;
    
    //Logo
    // nge trik ==> kalo replace file, di display logo yang lama masih kesimpen di cache ==> solusi ganti logo ganti nama file 
    $dirLogo    = 'logo/';
    $filesLogo  = array_diff(scandir($dirLogo),array('.','..','Thumbs.db'));
    $filesLogo  = array_values($filesLogo);//re index
    $logo       = $filesLogo[0];
    
    
    $dir    = 'wallpaper/';
    $files  = array_diff(scandir($dir),array('.','..','Thumbs.db'));
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
            // Jika file adalah GAMBAR
            $html_content = '<div style="background-image: url(wallpaper/'.$v.');"></div>';
        } elseif (in_array($ext_lower, $video_exts)) {
            // Jika file adalah VIDEO
            // Hapus loop, karena JavaScript akan mengontrol putaran penuh
            $html_content = '<div class="video-container"><video muted><source src="wallpaper/'.$v.'" type="video/'.$ext_lower.'"></video></div>';
            $data_video_attr = ' data-is-video="true"'; // Tandai slide ini sebagai video
        }

        if ($html_content !== '') {
            // Tambahkan atribut data-is-video ke div item
            $wallpaper  .= '<div class="item slides '.$active.'"'.$data_video_attr.'>'.$html_content.'</div>';
            $i++;
        }
    }
    // print_r($files);die;
?>


<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Display|Masjid</title>
    <link rel="icon" type="image/png" href="../icon.png"/>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
/* --- KOREKSI UMUM: Menghilangkan margin body --- */
body {
    margin: 0 !important; 
    padding: 0 !important;
    background: #333;
}

.hijri-date {
    font-size: 20px;       /* Ukuran huruf lebih kecil sedikit dari jam */
    color: #f39c12;        /* Warna Kuning Emas/Oranye */
    font-weight: bold;     /* Huruf Tebal */
    margin-top: 5px;       /* Jarak sedikit dari tanggal Masehi */
    text-shadow: 1px 1px 2px #000; /* Bayangan hitam agar terbaca di background terang */
}

.carousel .item video {
    position: absolute;
    top: 50%;
    left: 50%;
    min-width: 100%;
    min-height: 100%;
    width: auto;
    height: auto;
    z-index: 0;
    -ms-transform: translateX(-50%) translateY(-50%);
    -moz-transform: translateX(-50%) translateY(-50%);
    -webkit-transform: translateX(-50%) translateY(-50%);
    transform: translateX(-50%) translateY(-50%);
}

/* --- KOREKSI POSISI DAN UKURAN YOUTUBE FINAL --- */
#display-youtube {
    position: fixed; 
    top: 0; 
    
    /* LEFT: Geser kontainer lebih ke kanan (350px lebar jadwal + 10px spasi) */
    left: 400px; 
    
    /* WIDTH: Kontainer mengambil sisa lebar yang tersisa di viewport */
    /* Menggunakan 360px agar ada 10px spasi dari kanan jadwal (350px) */
    width: calc(100% - 400px); 
    
    /* TINGGI: 100% Viewport dikurangi tinggi running text (70px) */
    height: calc(100vh - 80px); 
    
    z-index: 10; 
    background-color: transparent; /* PENTING: Ubah latar belakang menjadi transparan */
    
    /* Flexbox: Rata kanan */
    display: flex;
    justify-content: flex-end; 
    align-items: center; 
    
    padding-left: 0; /* Hapus padding yang berlebihan */
    background-color: #333;
}

#display-youtube iframe {
    /* IFRAME: Atur agar mengecil sedikit */
    /* Anda ingin video terlihat 10px lebih kecil dari batas kiri kontainer, 
       tapi rata kanan. Kurangi 10px dari 100%. */
    width: 100%; 
    height: 100%;
    display: block;
}

/* Mengatur kotak wallpaper agar tidak full screen */
#main-wallpaper-carousel {
    background: #333; /* Warna background jika video loading */
    position: fixed;
    top: 0;
    
    /* Geser ke kanan supaya tidak menimpa Jadwal Sholat */
    left: 400px;            
    
    /* Lebar sisa (100% - Lebar Jadwal) */
    width: calc(100% - 400px); 
    
    /* Tinggi dikurangi Running Text (biasanya 50px atau 70px) */
    height: calc(100vh - 80px); 
    
    z-index: 0;
    overflow: hidden;
    background: #000; /* Warna background jika video loading */
}

/* Memastikan item carousel mengikuti ukuran kotak induknya */
.carousel-inner, .item, .item.active {
    height: 100% !important;
    width: 100% !important;
}

/* Mengatur Video agar pas (tidak zoom terlalu besar) */
.video-container {
    width: 100%;
    height: 100%;
    position: relative;
    overflow: hidden;
    background: #333; /* Ganti jadi abu-abu tua */
}

/* Ganti style video yang lama dengan yang ini */
.video-container video, 
.carousel .item video {
    width: 100% !important;
    height: 100% !important;
    position: absolute;
    top: 0;
    left: 0;
    
    /* Gunakan 'cover' agar full kotak (terpotong dikit), 
       atau 'contain' agar video utuh (ada bar hitam) */
    object-fit: cover; 
    
    /* Hapus transform lama jika ada */
    transform: none !important; 
}

/* --- PENGATURAN RUNNING TEXT --- */
#running-text {
    position: fixed;
    bottom: 15px;

    left: 400px;            

    width: calc(100% - 400px); 
    
    height: 55px; 
    
    /* Warna Background (Abu-abu tua sesuai tema) */
    
    /* Warna Teks */
    color: #ffffff;   
    
    /* Agar teks berada di tumpukan paling atas */
    z-index: 20;      
    
    /* Perataan Teks */
    display: flex;
    align-items: top; /* Teks rata tengah secara vertikal */
    
    /* Ukuran Font */
    font-size: 30px;  
    font-weight: bold;
    
}

/* Pastikan marquee memenuhi kotak */
#running-text marquee {
    width: 100%;
    margin: 0;
    line-height: 50px; /* Samakan dengan height di atas */
}

/* --- TAMPILAN KHUSUS SYURUQ --- */
#display-syuruq {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 99999; /* Paling atas menutupi semuanya */
    background-color: #000;
    background-image: url('img/bg-syuruq.png'); /* File gambar sesuai request */
    background-size: cover; /* Agar full screen dan zoom bagus */
    background-repeat: no-repeat;
    background-position: center;
    display: none; /* Default sembunyi */
}
</style>
</head>

<body>
    <audio id="adzan-beep" src="img/beep.mp3" preload="auto"></audio>

    <div id="preloader">
      <div id="status">&nbsp;</div>
    </div> 
    
    
    <div id="full-screen-clock" style="display:none"></div>
    <div id="count-down" class="full-screen" style="display:none">
        <div class="counter">
            <h1>COUNTER</h1>
            <div class="hh">00<span>JAM</span></div>
            <div class="ii">00<span>MENIT</span></div>
            <div class="ss">00<span>DETIK</span></div>
        </div>
    </div>
    <div id="display-adzan" class="full-screen" style="display:none"><div></div></div>
    <div id="display-sholat" class="full-screen" style="display:none"></div>
    <div id="display-khutbah" class="full-screen" style="display:none"><div></div></div>
    
    <div id="display-syuruq"></div>
    
    <div id="display-youtube" style="display:none">
        <iframe width="100%" height="100%" src="" frameborder="0" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>
    </div>
    
    <div id="main-wallpaper-carousel" class="carousel fade-carousel slide" data-ride="carousel" data-interval="false">
      <div class="overlay"></div>
      <div class="carousel-inner"><?=$wallpaper?></div> 
    </div>
    
    <div id="left-container">
        <div id="jam"></div>
        <div id="tgl"></div>
        <div id="jadwal"></div>
    </div>
    
    <div id="right-counter" style="display:none">
        <div class="counter">
            <h1>COUNTER</h1>
            <div class="hh">19<span>JAM</span></div>
            <div class="ii">25<span>MENIT</span></div>
            <div class="ss">45<span>DETIK</span></div>
        </div>
    </div>
    <div id="right-container">
        <div id="quote">
            <div class="carousel quote-carousel slide" data-ride="carousel" data-interval="<?=$info_timer?>" data-pause="null">
              <div class="carousel-inner">
                <?php 
                $i=0;
                foreach($db['info'] as $k => $v){
                    if($v[3]){
                        echo '
                        <div class="item slides '.($i==0?'active':'').'">
                          <div class="hero">        
                            <hgroup>
                                <div class="text1">'.htmlentities($v[0]).'</div>        
                                <div class="text2">'.nl2br(htmlentities($v[1])).'</div>        
                                <div class="text3">'.htmlentities($v[2]).'</div>
                            </hgroup>
                          </div>
                        </div>';
                        $i++;
                    }
                }
                ?>
              </div> 
            </div>
        </div>
        <div id="logo" style="background-image: url(logo/<?=$logo?>);"></div>
        <div id="running-text">
            <div class="item">
                <marquee>
                <?php 
                    foreach($db['running_text'] as $k => $v){
                        echo '<i class="fa fa-square-o" aria-hidden="true"></i> '.htmlentities($v);
                    }
                    // $ip  = gethostbyname(php_uname('n'));    // PHP < 5.3.0
                    $ip     = gethostbyname(gethostname());     // PHP >= 5.3.0 ==> di linux keluar 127.0.0.1
                    if(PHP_OS=='Linux'){
                        //raspi 3
                        // $command="/sbin/ifconfig wlan0 | grep 'inet addr:' | cut -d: -f2 | awk '{ print $1}'";//raspi pake wlan0 jadi hotspot
                        // $ip = exec ($command);
                        
                        //raspi 4
                        $command="/sbin/ifconfig wlan0 | grep 'inet '| cut -d 't' -f2 | cut -d 'n' -f1 | awk '{ print $1}'";//raspi pake wlan0 jadi hotspot
                        $ip = trim(exec ($command));
                    }
                    if($db['akses']['pass']=='admin'){
                        echo '<i class="fa fa-square-o" aria-hidden="true"></i> Konek ke wifi (SSID: DisplayMasjid, password: 12345678)';
                        echo '<i class="fa fa-square-o" aria-hidden="true"></i> Alamat admin http://'.$ip.'/';
                        echo '<i class="fa fa-square-o" aria-hidden="true"></i> Default akses user : admin, password : admin';
                        echo '<i class="fa fa-square-o" aria-hidden="true"></i> Silakan mengganti password admin untuk menghilangkan tulisan ini';
                    }
                ?>
                </marquee>
            </div>
        </div>
    </div>
    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/moment-with-locales.js"></script>
    <script src="js/PrayTimes.js"></script>
    <script src="js/jquery.marquee.js"></script>
    <script>
        // PENTING: Paksa Locale Inggris
        moment.locale('en'); 

        // --- FUNGSI HIJRIYAH ---
        function writeIslamicDate(adjustment) {
            var iMonthNames = ["Muharram", "Safar", "Rabi'ul Awal", "Rabi'ul Akhir", "Jumadil Awal", "Jumadil Akhir", "Rajab", "Sya'ban", "Ramadhan", "Syawal", "Zulqa'dah", "Zulhijjah"];
            var today = new Date();
            var adjustmili = 1000 * 60 * 60 * 24 * adjustment; 
            var todaymili = today.getTime() + adjustmili;
            today = new Date(todaymili);
            
            var day = today.getDate();
            var month = today.getMonth();
            var year = today.getFullYear();
            var m = month + 1;
            var y = year;
            if (m < 3) { y -= 1; m += 12; }
            
            var a = Math.floor(y / 100.);
            var b = 2 - a + Math.floor(a / 4.);
            if (y < 1583) b = 0;
            if (y == 1582) {
                if (m > 10) b = -10;
                if (m == 10) { b = 0; if (day > 4) b = -10; }
            }
            var jd = Math.floor(365.25 * (y + 4716)) + Math.floor(30.6001 * (m + 1)) + day + b - 1524;
            var iyear = 10631. / 30.;
            var epochastro = 1948084;
            var shift1 = 8.01 / 60.;
            var z = jd - epochastro;
            var cyc = Math.floor(z / 10631.);
            z = z - 10631 * cyc;
            var j = Math.floor((z - shift1) / iyear);
            var iy = 30 * cyc + j; 
            z = z - Math.floor(j * iyear + shift1);
            var im = Math.floor((z + 28.5001) / 29.5); 
            if (im == 13) im = 12;
            var id = z - Math.floor(29.5001 * im - 29); 
            return id + " " + iMonthNames[im - 1] + " " + iy + " H";
        }
        
        var format = '24h';
        <?php
            echo "var lat = ".$db['setting']['latitude'].";\n";
            echo "var lng = ".$db['setting']['longitude'].";\n";
            echo "var timeZone = ".$db['setting']['timeZone'].";\n";
            echo "var dst = ".$db['setting']['dst'].";\n";
            
            $prayTimesAdjust = [];
            if($db['prayTimesMethod']=='0'){
                foreach($db['prayTimesAdjust'] as $k => $v){
                    if($v!='') $prayTimesAdjust[$k]=$v;
                }
                echo "var prayTimesAdjust = $.parseJSON('".stripslashes(str_replace("`","\\`",json_encode($prayTimesAdjust)))."');\n";
                echo "prayTimes.adjust(prayTimesAdjust);\n"; 
            } else {
                echo "prayTimes.setMethod('".$db['prayTimesMethod']."');\n";
            }
            
            $prayTimesTune = [];
            foreach($db['prayTimesTune'] as $k => $v){
                if($v!='0') $prayTimesTune[$k]=$v;
            }
            if(count($prayTimesTune)>0){
                echo "var prayTimesTune = $.parseJSON('".stripslashes(str_replace("`","\\`",json_encode($prayTimesTune)))."');\n";
                echo "prayTimes.tune(prayTimesTune);\n"; 
            }
        ?>
        
        var app ={
            db  : $.parseJSON(`<?=stripslashes(str_replace("`","\\`",json_encode($showDb)))?>`),
            cekDb   : false,
            tglHariIni      : '',
            tglBesok        : '',
            jadwalHariIni   : {},
            jadwalBesok     : {},
            timer           : false,
            sholatTimer     : false,
            youtubeTimer    : false,
            adzanTimer      : false,
            countDownTimer  : false,
            khutbahTimer    : false,
            nextPrayCount   : 0,
            fajr : '', sunrise: '', dhuhr : '', asr : '', maghrib : '', isha : '',
            
            // --- AUDIO INIT ---
            audio : new Audio('img/beep.mp3'),
            
            initialize  : function(){
                app.timer   = setInterval(function(){app.cekPerDetik()},1000);
                $('#preloader').delay(350).fadeOut('slow');
            },
            cekPerDetik : function(){
                if(!app.tglHariIni || moment().format('YYYY-MM-DD') != moment(app.tglHariIni).format('YYYY-MM-DD')){
                    app.tglHariIni  = moment();
                    app.tglBesok    = moment().add(1,'days');
                    app.jadwalHariIni   = app.getJadwal(moment(app.tglHariIni).toDate());
                    app.jadwalBesok     = app.getJadwal(moment(app.tglBesok).toDate());
                    
                    app.fajr    = moment(app.jadwalHariIni.fajr,'HH:mm');
                    app.sunrise = moment(app.jadwalHariIni.sunrise,'HH:mm');
                    app.dhuhr   = moment(app.jadwalHariIni.dhuhr,'HH:mm');
                    app.asr     = moment(app.jadwalHariIni.asr,'HH:mm');
                    app.maghrib = moment(app.jadwalHariIni.maghrib,'HH:mm');
                    app.isha    = moment(app.jadwalHariIni.isha,'HH:mm');
                }
                app.showJadwal();
                app.displaySchedule();
                
                $.ajax({  
                    type    : "POST",  
                    url     : "../proses.php",
                    dataType: "json",
                    data    : {id:'changeDbCheck'}
                }).done(function(dt){
                    if(app.cekDb==false) app.cekDb = dt.data;
                    else if(app.cekDb !== dt.data) location.reload();
                });
            },
            getJadwal   : function(jadwalDate){
                let times = prayTimes.getTimes(jadwalDate, [lat, lng], timeZone, dst, format);
                return times;
            },
            showJadwal  : function(){
                let jamSekarang = moment();
                let jamDelay    = moment().subtract(5,'minutes');
                let jadwal  = '';
                
                let hari    = app.db.dayName[jamSekarang.format("dddd")]; 
                let bulan   = app.db.monthName[jamSekarang.format("MMMM")];
                if(!hari) hari = jamSekarang.format("dddd");
                if(!bulan) bulan = jamSekarang.format("MMMM");

                $('#jam').html(jamSekarang.format("HH.mm[<div>]ss[</div>]"));
                
                // --- TANGGAL HIJRIYAH ---
                let hijriDate = writeIslamicDate(-1); 
                $('#tgl').html(jamSekarang.format("["+hari+"], DD ["+bulan+"] YYYY") + "<div class='hijri-date'>" + hijriDate + "</div>");
                
                if($('.full-screen').is(":visible")){
                    $('#full-screen-clock').html(jamSekarang.format("[<i class='fa fa-clock-o''></i>&nbsp;&nbsp;]HH:mm"));
                    $('#full-screen-clock').slideDown();
                } else $('#full-screen-clock').slideUp();
                
                let jadwalDipake = app.jadwalHariIni;
                let jadwalPlusIcon  = '';
                
                if(jamDelay > app.isha){
                    jadwalDipakeapp = app.jadwalBesok;
                    jadwalPlusIcon  = '<span><i class="fa fa-plus" aria-hidden="true"></i></span>';
                }
                
                $.each(app.db.prayName, function(k,v) {
                    if (k === 'sunrise' && !app.sunrise) return true;
                    let css = '';
                    if (k === 'fajr' && (jamDelay < app.fajr || jamDelay >= app.isha)) { css = 'active'; } 
                    else if (k === 'sunrise' && jamDelay >= app.fajr && jamDelay < app.dhuhr) { css = 'active'; } 
                    else if (k === 'dhuhr' && jamDelay >= app.dhuhr && jamDelay < app.asr) { css = 'active'; } 
                    else if (k === 'asr' && jamDelay >= app.asr && jamDelay < app.maghrib) { css = 'active'; } 
                    else if (k === 'maghrib' && jamDelay >= app.maghrib && jamDelay < app.isha) { css = 'active'; } 
                    else if (k === 'isha' && jamDelay >= app.isha) { css = 'active'; }
                    let timeValue = jadwalDipake[k] || ''; 
                    jadwal += '<div class="row ' + css + '"><div class="col-xs-5">' + v + '</div><div class="col-xs-7">' + timeValue + jadwalPlusIcon + '</div></div>';
                });
                $('#jadwal').html(jadwal);
            },
            
            // --- FUNGSI BEEP ---
            playBeep: function() {
                app.audio.loop = true; 
                app.audio.play().then(() => {
                    setTimeout(function() {
                        app.audio.pause();
                        app.audio.currentTime = 0;
                        app.audio.loop = false;
                    }, 10000); 
                }).catch((e) => console.log("Gagal bunyi: " + e));
            },

            // --- DISPLAY SCHEDULE (SYURUQ + AZAN) ---
            displaySchedule: function(){
                let waitAdzan       = moment().add(app.db.timer.wait_adzan,'minutes').format('YYYY-MM-DD HH:mm:ss');
                let jamSekarang     = moment();
                let jamSekarangStr  = jamSekarang.format('YYYY-MM-DD HH:mm:ss');
                
                $.each(app.db.prayName, function(k,v) {
                    
                    // A. FARDHU
                    if (k !== 'sunrise') { 
                        let t = moment(app[k]); 
                        let jadwal = t.format('YYYY-MM-DD HH:mm:ss');
                        let iqomah_duration = app.db.iqomah[k] || 10; 
                        let stIqomah = t.add(app.db.timer.adzan, 'minutes').format('YYYY-MM-DD HH:mm:ss');
                        let enIqomah = moment(stIqomah, 'YYYY-MM-DD HH:mm:ss').add(iqomah_duration, 'minutes');
                        
                        if(waitAdzan == jadwal) {
                            app.runRightCountDown(app[k],'Menuju '+v);
                        }
                        else if(jadwal == jamSekarangStr) {
                            // AZAN TIBA
                            app.playBeep();
                            app.showDisplayAdzan(v); 
                        }
                        else if(stIqomah == jamSekarangStr){
                            if(moment().format('dddd')=='Friday' && app.db.jumat.active && k=='dhuhr'){
                                app.showDisplayKhutbah();
                            } else {
                                app.runFullCountDown(enIqomah,'IQOMAH',true); 
                            }
                        }
                    } 
                    
                    // B. SYURUQ
                    else {
                        let sunrise_time = moment(app.sunrise); 
                        let jadwal_syuruq = sunrise_time.format('YYYY-MM-DD HH:mm:ss');
                        
                        // Countdown
                        if (waitAdzan == jadwal_syuruq) {
                            app.runRightCountDown(sunrise_time, 'Menuju Syuruq');
                        }
                        
                        // Waktu Tiba (Diff)
                        let diff = jamSekarang.diff(sunrise_time, 'seconds');
                        if (diff >= 0 && diff <= 2 && $('#display-syuruq').is(':hidden')) {
                            console.log("SYURUQ TIBA!");
                            
                            // Matikan Youtube
                            $('#display-youtube').fadeOut();
                            $('#display-youtube iframe').attr('src', '');
                            app.youtubeTimer = false;

                            // Bunyi & Gambar
                            app.playBeep(); 
                            $('#display-syuruq').fadeIn();

                            // Matikan 1 Menit
                            setTimeout(function(){
                                $('#display-syuruq').fadeOut();
                                $('#quote').fadeIn(); 
                            }, 60000); 
                        }
                    }
                });
            },
            
            getNextPray : function(){
                let jamSekarang     = moment();
                let nextPray        = 'fajr';
                let jadwalDipake    = false;
                if(jamSekarang > app.isha){
                    jadwalDipake    = moment(app.jadwalBesok[nextPray],'HH:mm').add(1,'Day');
                } else{
                    $.each(app.db.prayName, function(k,v){
                        if(jamSekarang < app[k]){
                            nextPray    = k;
                            return false;
                        }
                    });
                    jadwalDipake    = moment(app.jadwalHariIni[nextPray],'HH:mm');
                }
                return { 'pray' : nextPray, 'date' : jadwalDipake };
            },
            showCountDownNextPray   : function(){
                let nextPray        = app.getNextPray();
                if (app.countDownTimer) return;
                app.nextPrayCount   = 0;
                app.countDownTimer  = setInterval(function(){
                    let t   = app.countDownCalculate(nextPray.date);
                    $('#right-counter .counter>h1').html('Menuju '+app.db.prayName[nextPray.pray]);
                    $('#right-counter .counter>.hh').html(t.hours+'<span>'+app.db.timeName.Hours+'</span>');
                    $('#right-counter .counter>.ii').html(t.minutes+'<span>'+app.db.timeName.Minutes+'</span>');
                    $('#right-counter .counter>.ss').html(t.seconds+'<span>'+app.db.timeName.Seconds+'</span>');
                    $('#right-counter').slideDown();
                    $('#quote').hide();
                    
                    app.nextPrayCount++;
                    if (app.nextPrayCount >= 30) { 
                        clearInterval(app.countDownTimer);
                        app.countDownTimer  = false;
                        $('#right-counter').fadeOut();
                        $('#quote').fadeIn();
                    }
                },1000);
            },
            showDisplayAdzan    : function(prayName){
                if (!app.adzanTimer){
                    $('#display-adzan>div').text(prayName);
                    $('#display-adzan').show();
                    app.adzanTimer  = setTimeout(function(){
                        $('#display-adzan').fadeOut();
                        app.adzanTimer  = false;
                    },(app.db.timer.adzan * 60 * 1000)+1500);
                }
            },
            showDisplayKhutbah  : function(){
                if (!app.khutbahTimer){
                    $('#display-khutbah>div').text(app.db.jumat.text);
                    $('#display-khutbah').show();
                    app.khutbahTimer    = setTimeout(function(){
                        app.khutbahTimer    = false;
                        app.showDisplaySholat();
                        $('#display-khutbah').fadeOut();
                    },app.db.jumat.duration * 60 * 1000);
                }
            },
            showDisplayYoutube: function(){
                let youtube_data = app.db.youtube_display;
                let video_link = youtube_data.link;
                if (youtube_data.active && video_link) {
                    if (!app.youtubeTimer) {
                        let embed_url = "https://www.youtube.com/embed/" + video_link + "?autoplay=1&controls=0&mute=1&loop=1";
                        $('#quote').hide(); 
                        $('#display-youtube iframe').attr('src', embed_url);
                        $('#display-youtube').show(); 
                        app.youtubeTimer = setTimeout(function(){
                            $('#display-youtube').fadeOut();
                            $('#display-youtube iframe').attr('src', ''); 
                            app.youtubeTimer = false;
                            $('#quote').show(); 
                            app.showCountDownNextPray(); 
                        }, youtube_data.duration * 60 * 1000); 
                    }
                } else {
                    app.showCountDownNextPray();
                }
            },
            showDisplaySholat   : function(){
                if (!app.sholatTimer){ 
                    let jamSekarang     = moment();
                    let duration        = (jamSekarang > app.isha && app.db.tarawih.active)?app.db.tarawih.duration:app.db.timer.sholat;
                    let delay_youtube   = app.db.youtube_display.delay_after_sholat || 0; 
                    $('#display-sholat').show();
                    app.sholatTimer     = setTimeout(function(){
                        $('#display-sholat').fadeOut();
                        app.sholatTimer     = false;
                        if (delay_youtube > 0) {
                            setTimeout(function() { app.showDisplayYoutube(); }, delay_youtube * 60 * 1000); 
                        } else {
                            app.showDisplayYoutube(); 
                        }
                    },duration * 60 * 1000);
                }
            },
            runFullCountDown: function(jam,title,runDisplaySholat){
                if (app.countDownTimer) return;
                app.countDownTimer  = setInterval(function(){
                    let t   = app.countDownCalculate(jam);
                    $('#count-down .counter>h1').html(title);
                    $('#count-down .counter>.hh').html(t.hours+'<span>'+app.db.timeName.Hours+'</span>');
                    $('#count-down .counter>.ii').html(t.minutes+'<span>'+app.db.timeName.Minutes+'</span>');
                    $('#count-down .counter>.ss').html(t.seconds+'<span>'+app.db.timeName.Seconds+'</span>');
                    $('#count-down').fadeIn();
                    
                    if(t.distance==5){
                        app.audio.play().catch(e => console.log(e));
                    }
                    if (t.distance < 1) {
                        clearInterval(app.countDownTimer);
                        app.countDownTimer  = false;
                        $('#count-down').fadeOut();
                        if(runDisplaySholat){
                            app.showDisplaySholat();
                        }
                    }
                },1000);
            },
            runRightCountDown   : function(jam,title){
                if (app.countDownTimer) return;
                app.countDownTimer  = setInterval(function(){
                    let t   = app.countDownCalculate(jam);
                    $('#right-counter .counter>h1').html(title);
                    $('#right-counter .counter>.hh').html(t.hours+'<span>'+app.db.timeName.Hours+'</span>');
                    $('#right-counter .counter>.ii').html(t.minutes+'<span>'+app.db.timeName.Minutes+'</span>');
                    $('#right-counter .counter>.ss').html(t.seconds+'<span>'+app.db.timeName.Seconds+'</span>');
                    $('#right-counter').slideDown();
                    $('#quote').hide();
                    
                    if (t.distance < 1) {
                        clearInterval(app.countDownTimer);
                        app.countDownTimer  = false;
                        $('#right-counter').fadeOut();
                        $('#quote').fadeIn();
                    }
                },1000);
            },
            countDownCalculate(jam){
                let jamSekarang = moment();
                let distance    = Math.round(jam.diff(jamSekarang, 'seconds', true)) ;
                let hours = Math.floor((distance % (60 * 60 * 24)) / (60 * 60));
                let minutes = Math.floor((distance % (60 * 60)) / 60);
                let seconds = Math.floor((distance % 60));
                hours   = (hours>=0     && hours<10)    ?'0'+hours:hours;
                minutes = (minutes>=0   && minutes<10)  ?'0'+minutes:minutes;
                seconds = (seconds>=0   && seconds<10)  ?'0'+seconds:seconds;
                return  {
                    'distance'  : distance,
                    'hours'     : hours,
                    'minutes'   : minutes,
                    'seconds'   : seconds
                };
            }
        }
        app.initialize();

        var wallpaper_timer_ms = <?=$info_timer?>; 
        var carousel_element = $('#main-wallpaper-carousel');
        var current_slide_timer;

        function startSlideTimer(duration) {
            clearTimeout(current_slide_timer);
            current_slide_timer = setTimeout(function() {
                carousel_element.carousel('next'); 
            }, duration);
        }

        function handleSlideChange(e) {
            var next_slide = $(e.relatedTarget);
            var is_video = next_slide.attr('data-is-video');
            if (is_video === 'true') {
                carousel_element.carousel('pause'); 
                var video = next_slide.find('video')[0];
                if (video) {
                    clearTimeout(current_slide_timer); 
                    video.volume = 0; 
                    $(video).off('ended.videoControl'); 
                    $(video).on('ended.videoControl', function() {
                        carousel_element.carousel('next'); 
                    });
                    video.play();
                }
                // HIDE INFO SAAT VIDEO JALAN
                $('#quote').fadeOut();
            } else {
                startSlideTimer(<?=$wallpaper_timer?>); 
                // SHOW INFO SAAT GAMBAR MUNCUL
                $('#quote').fadeIn();
            }
        }
        carousel_element.on('slid.bs.carousel', handleSlideChange);
        var activeSlide = carousel_element.find('.item.active')[0];
        if (activeSlide) {
            handleSlideChange({relatedTarget: activeSlide}); 
        } else {
             startSlideTimer(<?=$wallpaper_timer?>);
        }
    </script>
</body>
</html>