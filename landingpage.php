<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing Page</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding-top: 50px;
            font-family: "Poppins", sans-serif;
            background-color: #FEFDF7;
            color: #000;
        }

        nav {
            background-color: #FEFDF7;
            font-family: "Inter", sans-serif;
            padding: 15px 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(141, 138, 138, 0.15);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 1000;
            box-sizing: border-box;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .logo img {
            width: 40px;
            height: auto;
        }

        .logo p {
            font-size: 13px;
            line-height: 19px;
            font-weight: 700;
            color: #6D4A37;
            margin: 0;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-left: auto;
            margin-right: 30px;
        }

        nav a {
            text-decoration: none;
            font-weight: 500;
            color: #918d8a;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        nav a:hover,
        nav a.active {
            color: #000;
            font-weight: 700;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        nav .profile {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        nav .profile:hover {
            transform: scale(1.1);
        }

        .menu-icon {
            display: none;
            font-size: 24px;
            color: #000;
            cursor: pointer;
        }

        .cerita_row img {
            width: 170px;
            height: 250px;
            border-radius: 5px;
            object-fit: cover;
        }

        .cerita_row {
            margin: 20px 20px 140px 20px;
            display: flex;
            justify-content: center;
            gap: 80px;
        }

        .first {
            position: relative;
            top: 0;
        }

        .second {
            position: relative;
            top: 40px;
        }

        .third {
            position: relative;
            top: 80px;
        }

        .hero {
            min-height: 50vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-family: 'Poppins';
            color: #000;
            text-align: center;
            padding: 60px 20px 20px 20px;
        }

        .hero h2 {
            font-size: 40px;
            font-weight: 300;
            margin-top: 30px;
            margin-bottom: 20px;
            font-family: 'poppins';
            text-shadow: 0px 10px 15px rgba(0, 0, 0, 0.5);
        }

        .hero button {
            padding: 12px 20px;
            background: #6D4A36;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            color: #fff;
            font-size: 18px;
            transition: 0.3s;
            transform: scale(1.1);
        }

        .hero button:hover {
            background: #6D4A36;
        }

        .section {
            text-align: center;
            margin: 20px 0;
        }

        .section h3 {
            font-size: 28px;
            margin-bottom: 20px;
        }

        .section p {
            max-width: 700px;
            margin: 0 auto 40px;
        }

        .container {
            background-color: #ffff;
            border-radius: 11px;
            padding: 5px;
            overflow: hidden;
            text-align: center;
        }

        .image-container {
            position: relative;
            margin-bottom: 20px;
        }

        .image-container img {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 1408px;
            height: 350px;
            object-fit: cover;
            border-radius: 10px;
            display: block;
        }

        .quote {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 1.5em;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
            padding: 20px;
            box-sizing: border-box;
            width: 100%;
            z-index: 10;
        }

        .author {
            font-style: italic;
            margin-top: 10px;
        }

        .container-buku {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
        }

        .buku {
            width: 150px;
            border-radius: 5px;
            overflow: auto;
        }

        .buku img {
            width: 100%;
            height: auto;
            display: block;
        }

        .buku-card {
            display: grid;
            grid-template-columns: repeat(4, 200px);
            justify-content: center;
            gap: 30px;
            margin: 30px auto 0;
            padding: 0 20px 60px;
        }

        @media (max-width: 900px) {

            .menu-icon {
                display: block;
            }

            .nav-menu {
                position: absolute;
                top: 100px;
                right: 0;
                background: #FEFDF7;
                flex-direction: column;
                width: 200px;
                padding: 15px 0;
                gap: 20px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
                border-radius: 8px;
                margin: 0;
                display: none;
            }

            .nav-menu.active {
                display: flex;
            }

            .nav-menu img.profile {
                margin-top: 10px;
            }

            .cerita-container {
                grid-template-columns: repeat(2, 200px);
                gap: 80px;
                padding: 0 40px 150px;
            }
        }

        .judul-rating {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 90%;
            max-width: 890px;
            margin: 0 auto 10px;
        }

        .judul-rating h3 {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin: 0;
        }

        .judul-rating a {
            font-size: 16px;
            color: #4b4b4b;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .kotak-buku {
            width: 200px;
            padding: 15px;
            border-radius: 0;
            background-color: #6D4A36;
            color: #F7F4E9;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .kotak-buku h2 {
            margin: 0;
            font-weight: 600;
            font-size: 15px;
            height: 60px;
            display: flex;
            align-items: center;
        }

        .kotak-buku hr {
            border: none;
            border-top: 1px solid #F7F4E9;
            margin: 0;
        }

        .sampul {
            align-items: center;
            width: 100%;
            height: 250px;
            border-radius: 0;
            object-fit: cover;
        }

        .rating {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .star {
            width: 20px;
            height: 20px;
        }

        .rating-number {
            font-size: 16px;
            font-weight: 500;
            margin-right: auto;
        }

        #jelajahi {
            display: block;
            width: fit-content;
            margin: 0 auto 60px;
            padding: 6px 30px;
            border-radius: 25px;
            font-family: 'Inter';
            font-weight: 700;
            letter-spacing: 0.5px;
            font-size: 18px;
            text-decoration: none;
            color: white;
            background-color: #6D4A37;
            transition: all 0.3s ease;
        }

        #baru_ditambahkan {
            font-family: 'Poppins';
            padding: 40px 150px;
            background-color: transparent;
            justify-content: center;
        }

        #baru_ditambahkan .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        #baru_ditambahkan .header h2 {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin: 0;
        }

        #baru_ditambahkan .header a {
            font-size: 16px;
            color: #4b4b4b;
            text-decoration: none;
        }

        .cerita_container {
            display: flex;
            justify-content: space-between;
        }

        .cerita_wrapper {
            position: relative;
            width: 450px;
            height: 300px;
            display: flex;
            align-items: end;
            justify-content: start;
        }

        .coklat {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 82%;
            background-color: #6D4A37;
            border-radius: 8px;
            z-index: 1;
        }

        .cerita_wrapper .sampul {
            width: 180px;
            height: 280px;
            border-radius: 10px;
            position: relative;
            z-index: 2;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            left: 10px;
            bottom: 10px;
            object-fit: cover;
        }

        .cerita_detail {
            position: relative;
            z-index: 2;
            padding: 20px 20px 20px 30px;
            color: white;
            text-align: justify;
        }

        .detail_top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 10px;
            border-bottom: 1.4px solid white;
            margin-bottom: 20px;
        }

        .detail_top h2 {
            font-size: 18px;
            font-weight: 600;
        }

        .cerita_detail p {
            margin-bottom: 15px;
            font-size: 11px;
            line-height: 1.5;
        }

        #ulasan {
            margin: 80px 0px 170px 0px;
            overflow: hidden;
        }

        #ulasan h2 {
            text-align: center;
            margin: 50px auto 30px auto;
            font-size: 26px;
            font-weight: 600;
        }

        #saran {
            display: block;
            width: fit-content;
            margin: 0 auto 80px;
            padding: 8px 35px;
            border-radius: 25px;
            font-family: 'Inter';
            font-weight: 700;
            letter-spacing: 0.5px;
            font-size: 18px;
            text-decoration: none;
            color: #FEFDF7;
            background-color: #6D4A37;
            transition: all 0.3s ease;
        }

        #saran:hover {
            background-color: #5a3d2d;
            transform: scale(1.05);
        }

        .saran_container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 30px;
            position: relative;
            padding: 20px 0;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Modifikasi untuk sistem slider ulasan aktif */
        .saran_item {
            display: none;
            width: 550px;
            height: 250px;
            padding: 25px 30px;
            border-radius: 15px;
            background-color: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        .saran_item.active {
            display: block;
        }

        .saran_top {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .saran_top .profile {
            width: 45px;
            height: 45px;
        }

        .saran_top h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #000;
        }

        .isi_saran p {
            margin: 0;
            font-size: 14px;
            line-height: 1.7;
            text-align: justify;
            color: #333;
        }

        .panah_kiri,
        .panah_kanan {
            font-size: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            user-select: none;
            color: black;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            transition: 0.3s;
        }


        footer {
            background-color: #FFF5E7;
            font-family: "Inter", sans-serif;
            font-size: 14px;
            margin-top: 80px;
        }

        .footer_top {
            padding: 4em 4%;
            display: flex;
            gap: 50px;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
        }

        #tentang-web {
            max-width: 450px;
        }

        #tentang-web p {
            margin-top: 15px;
            line-height: 1.6;
            text-align: justify;
        }

        #navigasi,
        #kontak {
            min-width: 150px;
        }

        footer h3 {
            margin-bottom: 15px;
            font-size: 16px;
            color: #6D4A37;
        }

        footer ul {
            list-style-type: none;
            padding: 0;
            line-height: 2.2em;
        }

        footer a {
            text-decoration: none;
            color: #000;
            transition: color 0.2s;
        }

        footer a:hover {
            color: #6D4A37;
        }

        .footer_bottom {
            padding: 1.5em 4%;
            text-align: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer_bottom hr {
            border: none;
            border-top: 1px solid #dcd3c7;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <nav>
        <div class="logo">
            <img src="img/logoweb.svg" alt="logo web">
            <p>Gema <br>Nusantara</p>
        </div>

        <div class="menu-icon" id="menu-icon">
            <i class="ph ph-list"></i>
        </div>

        <div class="nav-menu" id="nav-menu">
            <a class="active" href="landingpage.php">Beranda</a>
            <a href="jelajahi.php">Jelajahi</a>
            <a href="saran.php">Saran</a>
            <a href="settingakun_baru.php"><img class="profile" src="img/profile22.svg" alt="user profile"></a>
        </div>
    </nav>
    <script>
        const menuIcon = document.getElementById("menu-icon");
        const navMenu = document.getElementById("nav-menu");

        menuIcon.addEventListener("click", () => {
            navMenu.classList.toggle("active");
        });
    </script>
    <section class="hero" id="home">
        <h2>Seribu Cerita Dalam Genggamanmu</h2>
        <a id="jelajahi" href="jelajahi.php" style="color: white;">Jelajahi</a>
    </section>
    <section class="section">
        <div class="cerita_row">
            <img class="first" src="img/ceritaabo.svg" alt="buku 1">
            <img class="second" src="img/ceritabawang.svg" alt="buku 2">
            <img class="third" src="img/ceritalutung.svg" alt="buku 3">
            <img class="second" src="img/ceritakeong.svg" alt="buku 4">
            <img class="first" src="img/ceritatimun.svg" alt="buku 5">
        </div>
    </section>
    <section class="section">
        <div class="container">
            <div class="image-container">
                <img src="img/quotes.svg" alt="Rak Buku">
                <div class="quote">
                    "Aku rela di penjara asalkan bersama buku, karena dengan buku aku bebas."
                    <div class="author">- Mohammad Hatta</div>
                </div>
            </div>
        </div>
    </section>
    <section class="section">
        <div class="judul-rating">
            <h3>Rating Tertinggi</h3>
            <a href="jelajahi.php">Lihat Semua ></a>
        </div>
        <div class="buku-card">
            <div class="kotak-buku">
                <img class="sampul" src="img/ceritalutung.svg" alt="Gambar Cerita Lutung Kasarung">
                <h2>Lutung Kasarung</h2>
                <hr>
                <div class="rating">
                    <span class="star">⭐</span>
                    <span class="rating-number">4.8</span>
                    <span class="material-icons" onclick="location.href='dashboardsuka_user.php'; return false;">favorite_border</span>
                    <span class="material-icons" onclick="location.href='dashboardsimpan.php'; return false;">bookmark_border</span>
                </div>
            </div>
            <div class="kotak-buku">
                <img class="sampul" src="img/ceritatimun.svg" alt="Gambar Cerita Timun Mas">
                <h2>Timun Mas</h2>
                <hr>
                <div class="rating">
                    <span class="star">⭐</span>
                    <span class="rating-number">4.7</span>
                    <span class="material-icons" onclick="location.href='dashboardsuka_user.php'; return false;">favorite_border</span>
                    <span class="material-icons" onclick="location.href='dashboardsimpan.php'; return false;">bookmark_border</span>
                </div>
            </div>
            <div class="kotak-buku">
                <img class="sampul" src="img/ceritakeong.svg" alt="Gambar Cerita Keong Mas">
                <h2>Keong Mas</h2>
                <hr>
                <div class="rating">
                    <span class="star">⭐</span>
                    <span class="rating-number">4.6</span>
                    <span class="material-icons" onclick="location.href='dashboardsuka_user.php'; return false;">favorite_border</span>
                    <span class="material-icons" onclick="location.href='dashboardsimpan.php'; return false;">bookmark_border</span>
                </div>
            </div>
            <div class="kotak-buku">
                <img class="sampul" src="img/ceritatodilaling.svg" alt="Gambar Cerita To Dilaling">
                <h2>To Dilaling</h2>
                <hr>
                <div class="rating">
                    <span class="star">⭐</span>
                    <span class="rating-number">4.5</span>
                    <span class="material-icons" onclick="location.href='dashboardsuka_user.php'; return false;">favorite_border</span>
                    <span class="material-icons" onclick="location.href='dashboardsimpan.php'; return false;">bookmark_border</span>
                </div>
            </div>
        </div>
    </section>
    <section class="section">
        <div class="judul-rating">
            <h3>Populer Bulan Ini</h3>
            <a href="jelajahi.php">Lihat Semua ></a>
        </div>
        <div class="buku-card">
            <div class="kotak-buku">
                <img class="sampul" src="img/ceritarawa.svg" alt="Gambar Cerita Rawa Pening">
                <h2>Rawa Pening</h2>
                <hr>
                <div class="rating">
                    <span class="star">⭐</span>
                    <span class="rating-number">4.4</span>
                    <span class="material-icons" onclick="location.href='dashboardsuka_user.php'; return false;">favorite_border</span>
                    <span class="material-icons" onclick="location.href='dashboardsimpan.php'; return false;">bookmark_border</span>
                </div>
            </div>
            <div class="kotak-buku">
                <img class="sampul" src="img/ceritatodilaling.svg" alt="Gambar Cerita To Dilaling">
                <h2>To Dilaling</h2>
                <hr>
                <div class="rating">
                    <span class="star">⭐</span>
                    <span class="rating-number">4.5</span>
                    <span class="material-icons" onclick="location.href='dashboardsuka_user.php'; return false;">favorite_border</span>
                    <span class="material-icons" onclick="location.href='dashboardsimpan.php'; return false;">bookmark_border</span>
                </div>
            </div>
            <div class="kotak-buku">
                <img class="sampul" src="img/ceritabawang.svg" alt="Gambar Cerita Bawang Putih">
                <h2>Bawang Putih dan Bawang Merah</h2>
                <hr>
                <div class="rating">
                    <span class="star">⭐</span>
                    <span class="rating-number">4.5</span>
                    <span class="material-icons" onclick="location.href='dashboardsuka_user.php'; return false;">favorite_border</span>
                    <span class="material-icons" onclick="location.href='dashboardsimpan.php'; return false;">bookmark_border</span>
                </div>
            </div>
            <div class="kotak-buku">
                <img class="sampul" src="img/ceritaabo.svg" alt="Gambar Cerita Abo">
                <h2>Abo Mamongkuroit dan Tulap Si Raksasa</h2>
                <hr>
                <div class="rating">
                    <span class="star">⭐</span>
                    <span class="rating-number">4.4</span>
                    <span class="material-icons" onclick="location.href='dashboardsuka_user.php'; return false;">favorite_border</span>
                    <span class="material-icons" onclick="location.href='dashboardsimpan.php'; return false;">bookmark_border</span>
                </div>
            </div>
        </div>
    </section>
    <section id="baru_ditambahkan">
        <div class="header">
            <h2>Baru Ditambahkan</h2>
            <a href="jelajahi.php">Lihat Semua ></a>
        </div>
        <div class="cerita_container">
            <div class="cerita_wrapper">
                <div class="coklat"></div>
                <img src="img/ceritarawa.svg" alt="Rawa Pening" class="sampul">
                <div class="cerita_detail">
                    <div class="detail_top">
                        <h2>Rawa Pening</h2>
                        <span class="material-icons" onclick="location.href='dashboardsuka_user.php'; return false;">favorite_border</span>
                        <span class="material-icons" onclick="location.href='dashboardsimpan.php'; return false;">bookmark_border</span>
                    </div>
                    <p>
                        Cerita mengisahkan tentang Baro Klinting, seekor naga, anak dari Endang Sawitri, putri Kepala
                        Desa Ngasem. Karena sebuah kutukan, Endang Sawitri harus mengandung dan melahirkan seorang anak
                        berwujud naga seorang diri. Baro Klinting pun pergi ke Gunung Telomoyo untuk bertapa demi ...
                    </p>
                </div>
            </div>

            <div class="cerita_wrapper">
                <div class="coklat"></div>
                <img src="img/ceritadanau.svg" alt="Danau Lipan" class="sampul">
                <div class="cerita_detail">
                    <div class="detail_top">
                        <h2>Danau Lipan</h2>
                        <span class="material-icons" onclick="location.href='dashboardsuka_user.php'; return false;">favorite_border</span>
                        <span class="material-icons" onclick="location.href='dashboardsimpan.php'; return false;">bookmark_border</span>
                    </div>
                    <p>
                        Saat Ratu Aji Bidara Putih dilamar oleh Raja Tiongkok, dia segera mengutus menterinya untuk
                        menyelidiki raja itu. Perdana Menteri menyelinap ke kapal kerajaan Tiongkok dan mendengar
                        bunyi-bunyi aneh dari kamar raja. Dia pun melapor pada Ratu Aji Bidara Putih. Mendengar laporan
                        menterinya...
                    </p>
                </div>
            </div>
        </div>
    </section>
    <section id="ulasan">
        <h2>Belum Menemukan Cerita Rakyat Favoritmu?</h2>
        <a id="saran" href="saran.php">Beri Saran</a>

        <div class="saran_container">
            <!-- PANAH KIRI -->
            <div class="panah_kiri" onclick="plusSaran(-1)">
                <span>‹</span>
            </div>

            <!-- ITEM 1 -->
            <div class="saran_item active">
                <div class="saran_top">
                    <img class="profile" src="img/profile.svg" alt="User Icon">
                    <h3>Andi Pratama</h3>
                </div>
                <div class="isi_saran">
                    <p>"Aku ada cerita rakyat dari daerahku, yaitu “Si Pitung, Pahlawan Betawi” Cerita ini mengisahkan
                        tentang pahlawan lokal yang berani melawan ketidakadilan dan penjajahan demi melindungi rakyat
                        kecil.."</p>
                </div>
            </div>

            <!-- ITEM 2 -->
            <div class="saran_item">
                <div class="saran_top">
                    <img class="profile" src="img/profile.svg" alt="User Icon">
                    <h3>Haru</h3>
                </div>
                <div class="isi_saran">
                    <p>"Aku ada saran nih Kisah yang mirip Cinderella tapi versi Nusantara. Intinya, cerita ini
                        mengisahkan tentang dua saudara tiri yang memperlihatkan perbedaan sikap antara kebaikan dan
                        kejahatan. Bawang Putih yang baik hati, sabar, dan jujur sedangkan Bawang Merah yang cemburu dan
                        jahat."</p>
                </div>
            </div>

            <!-- ITEM 3 -->
            <div class="saran_item">
                <div class="saran_top">
                    <img class="profile" src="img/profile.svg" alt="User Icon">
                    <h3>Wila Wulandari</h3>
                </div>
                <div class="isi_saran">
                    <p>"Banyakin cerita rakyat asal usul suku pulau dong, kayak cerita 'Danau Toba' yang ada legenda
                        Toba juga menjadi pulau volkanik bahkan sampai masuk ke daftar UNESCO."</p>
                </div>
            </div>

            <!-- PANAH KANAN -->
            <div class="panah_kanan" onclick="plusSaran(1)">
                <span>›</span>
            </div>
        </div>
    </section>
    <footer>
        <div class="footer_top">
            <div id="tentang-web">
                <div class="logo">
                    <img src="img/logoweb.svg" alt="Logo Gema Nusantara">
                    <p>Gema <br> Nusantara</p>
                </div>
                <p>Temukan kembali pesona dongeng masa kecil Anda di Gema Nusantara, rumah digital bagi cerita rakyat
                    yang telah diwariskan turun-temurun, dikemas dalam format yang mudah dinikmati kapan saja. Mari
                    bersama-sama melestarikan budaya Indonesia dan berbagi kearifan leluhur kepada generasi mendatang.
                </p>
            </div>
            <div id="navigasi">
                <h3>Navigasi</h3>
                <ul>
                    <li><a href="landingpage.php">Beranda</a></li>
                    <li><a href="jelajahi.php">Jelajahi</a></li>
                    <li><a href="saran.php">Saran</a></li>
                    <li><a href="riwayat.php">Riwayat</a></li>
                </ul>
            </div>
            <div id="kontak">
                <h3>Kontak Kami</h3>
                <ul>
                    <li><a href="https://www.instagram.com/">Instagram</a></li>
                    <li><a href="https://www.youtube.com/">Youtube</a></li>
                    <li><a href="https://www.tiktok.com/">Tiktok</a></li>
                    <li><a href="https://www.twitter.com/">Twitter</a></li>
                </ul>
            </div>
        </div>
        <div class="footer_bottom">
            <hr>
            <p>Copyright @Gema Nusantara 2026</p>
        </div>
    </footer>

    <!-- JAVASCRIPT SLIDER BARU UNTUK SARAN/ULASAN -->
    <script>
        let saranIndex = 1;
        showSaran(saranIndex);

        function plusSaran(n) {
            showSaran(saranIndex += n);
        }

        function showSaran(n) {
            let i;
            let items = document.getElementsByClassName("saran_item");
            if (items.length === 0) return;

            if (n > items.length) { saranIndex = 1 }
            if (n < 1) { saranIndex = items.length }

            for (i = 0; i < items.length; i++) {
                items[i].style.display = "none";
            }
            items[saranIndex - 1].style.display = "block";
        }
    </script>
</body>

</html>