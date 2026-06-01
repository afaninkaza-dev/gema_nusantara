<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gema Nusantara - Saran</title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding-top: 70px;
            font-family: "Poppins", sans-serif;
            background-color: #FEFDF7;
            color: #000;
        }

        /* ===== NAVBAR (sama dengan beranda & jelajahi) ===== */
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

        @media (max-width: 900px) {
            .menu-icon {
                display: block;
            }

            .nav-menu {
                position: absolute;
                top: 65px;
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
        }

        /* ===== KONTEN ===== */
        .main-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            max-width: 1200px;
            margin: 40px auto 60px auto;
            padding: 0 4%;
            gap: 50px;
        }

        .content-left {
            flex: 1;
        }

        .saran_header {
            background: #FEFDF7;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
        }

        .saran_header h1 {
            margin: 0;
            font-size: 32px;
            font-weight: 700;
            line-height: 1.4;
            color: #000;
        }

        .alasan_saran h2 {
            margin-left: 40px;
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #000;
        }

        .alasan_saran ul {
            margin-left: 40px;
            list-style-type: disc;
            padding-left: 1.5em;
            line-height: 1.8;
            color: #333;
            font-size: 15px;
        }

        .alasan_saran li {
            margin-bottom: 0.5em;
        }

        .saran_container {
            display: flex;
            flex-direction: column;
            background-color: #F5F5F5;
            width: 450px;
            border-radius: 12px;
            padding: 2.5em 2em;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .saran_container .logo {
            margin-bottom: 20px;
        }

        .saran_container label {
            font-weight: 600;
            margin-bottom: 0.5em;
            margin-top: 1.2em;
            color: #000;
            font-size: 14px;
        }

        .saran_container input,
        .saran_container textarea {
            width: 100%;
            padding: 0.8em 1em;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: "Poppins", sans-serif;
            font-size: 14px;
            box-sizing: border-box;
            background-color: #fff;
            transition: border-color 0.3s;
        }

        .saran_container input::placeholder,
        .saran_container textarea::placeholder {
            font-size: 13px;
            color: #999;
        }

        .saran_container input:focus,
        .saran_container textarea:focus {
            outline: none;
            border-color: #6D4A37;
        }

        .saran_container textarea {
            resize: vertical;
            min-height: 180px;
        }

        .kirim {
            width: 100%;
            padding: 0.9em;
            background-color: #6D4A37;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-family: "Poppins", sans-serif;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 2em;
        }

        .kirim:hover {
            background-color: #5a3a2a;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(109, 74, 55, 0.3);
        }

        /* ===== FOOTER ===== */
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

        #navigasi, #kontak {
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

        @media (max-width: 968px) {
            .main-wrapper {
                flex-direction: column;
                gap: 40px;
            }

            .saran_container {
                width: 100%;
            }

            .footer_top {
                flex-direction: column;
                gap: 30px;
            }
        }
    </style>
</head>

<body>

    <nav>
        <div class="logo">
            <img src="img/logoweb.svg" alt="Logo Gema Nusantara">
            <p>Gema <br> Nusantara</p>
        </div>

        <div class="menu-icon" id="menu-icon">
            <i class="ph ph-list"></i>
        </div>

        <div class="nav-menu" id="nav-menu">
            <a href="landingpage.php">Beranda</a>
            <a href="jelajahi.php">Jelajahi</a>
            <a class="active" href="saran.html">Saran</a>
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

    <div class="main-wrapper">

        <div class="content-left">
            <section class="saran_header">
                <h1>Usul Buku Dengan Hati,<br>Budaya Baca Lestari</h1>
            </section>

            <section class="alasan_saran">
                <h2>Kenapa saranmu sangat penting?</h2>
                <ul>
                    <li>Membantu memperkaya koleksi cerita rakyat</li>
                    <li>Melestarikan cerita rakyat</li>
                    <li>Menjangkau pembaca yang lebih luas</li>
                </ul>
            </section>
        </div>

        <div class="saran_container">
            <div class="logo">
                <img src="img/logoweb.svg" alt="Logo Gema Nusantara">
                <p>Gema <br> Nusantara</p>
            </div>

            <label for="judul">Judul Cerita Rakyat</label>
            <input type="text" id="judul" placeholder="Isi judul cerita rakyat...">

            <label for="deskripsi">Isi Cerita Rakyat</label>
            <textarea id="deskripsi" name="deskripsi_cerita" placeholder="Deskripsikan cerita rakyatnya..."></textarea>

            <button class="kirim">Kirim Saran</button>
        </div>

    </div>

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
                    <li><a href="saran.html">Saran</a></li>
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

</body>

</html>