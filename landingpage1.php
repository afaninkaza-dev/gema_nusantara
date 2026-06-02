<?php
include 'koneksi.php';

// ── Ambil data cerita ──────────────────────────────────────────────────────
// Rating tertinggi
$result_rating = $conn->query("
    SELECT cr.*, ROUND(AVG(u.rating), 1) AS avg_rating
    FROM cerita_rakyat cr
    LEFT JOIN ulasan u ON cr.id = u.cerita_id
    GROUP BY cr.id
    ORDER BY avg_rating DESC
    LIMIT 4
");

// Populer bulan ini
$result_populer = $conn->query("
    SELECT cr.*, COUNT(rm.id) AS jumlah_baca, ROUND(AVG(u.rating), 1) AS avg_rating
    FROM cerita_rakyat cr
    LEFT JOIN riwayat_membaca rm ON cr.id = rm.cerita_id
        AND MONTH(rm.waktu) = MONTH(NOW()) AND YEAR(rm.waktu) = YEAR(NOW())
    LEFT JOIN ulasan u ON cr.id = u.cerita_id
    GROUP BY cr.id
    ORDER BY jumlah_baca DESC
    LIMIT 4
");

// Baru ditambahkan
$result_baru = $conn->query("SELECT * FROM cerita_rakyat ORDER BY waktu DESC LIMIT 2");

// Saran pengguna
$result_saran = $conn->query("
    SELECT s.isi_saran, u.nama
    FROM saran s JOIN user u ON s.user_id = u.id
    ORDER BY s.waktu DESC LIMIT 10
");

// Helper ikon — semua onclick redirect masuk.php
function ikonKartu($cerita_id)
{
    return "
        <div class=\"icon-wrapper\">
            <span class=\"material-icons icon-aksi\"
                  onclick=\"event.stopPropagation(); redirectMasuk()\">favorite_border</span>
            <span class=\"material-icons icon-aksi\"
                  onclick=\"event.stopPropagation(); redirectMasuk()\">bookmark_border</span>
        </div>";
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gema Nusantara - Beranda</title>
    <link rel="icon" href="img/logoweb.svg">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        crossorigin="anonymous" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            margin: 0;
            padding-top: 70px;
            font-family: "Poppins", sans-serif;
            background: #FEFDF7;
            color: #000;
        }

        /* ── NAV ── */
        nav {
            background: #FEFDF7;
            font-family: "Inter", sans-serif;
            padding: 15px 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(141, 138, 138, .15);
            position: fixed;
            top: 0; left: 0; right: 0;
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

        .logo img { width: 40px; }

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
            transition: all .3s;
        }

        nav a:hover,
        nav a.active {
            color: #000;
            font-weight: 700;
        }

        .menu-icon {
            display: none;
            font-size: 24px;
            color: #000;
            cursor: pointer;
        }

        /* ── HERO ── */
        .hero {
            min-height: 40vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 60px 20px 20px;
        }

        .hero h2 {
            font-size: 38px;
            font-weight: 300;
            margin: 30px 0 20px;
            text-shadow: 0 10px 15px rgba(0, 0, 0, .4);
        }

        #jelajahi {
            display: block;
            width: fit-content;
            padding: 10px 30px;
            border-radius: 30px;
            font-family: 'Inter';
            font-weight: 700;
            font-size: 17px;
            text-decoration: none;
            color: #fff;
            background: #6D4A37;
            transition: all .3s;
        }

        #jelajahi:hover {
            background: #5a3d2d;
            transform: scale(1.05);
        }

        /* ── CERITA ROW ── */
        .cerita_row {
            margin: 20px 20px 130px;
            display: flex;
            justify-content: center;
            gap: 70px;
        }

        .cerita_row img {
            width: 160px;
            height: 240px;
            border-radius: 5px;
            object-fit: cover;
        }

        .first  { position: relative; top: 0; }
        .second { position: relative; top: 40px; }
        .third  { position: relative; top: 80px; }

        /* ── QUOTE ── */
        .container {
            background: #fff;
            border-radius: 11px;
            padding: 5px;
            overflow: hidden;
            text-align: center;
        }

        .image-container { position: relative; margin-bottom: 20px; }

        .image-container img {
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
            color: #fff;
            font-size: 1.4em;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, .8);
            padding: 20px;
            width: 100%;
            z-index: 10;
        }

        .author { font-style: italic; margin-top: 10px; }

        /* ── KARTU BUKU ── */
        .section { text-align: center; margin: 20px 0; }

        .judul-rating {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 90%;
            max-width: 890px;
            margin: 0 auto 10px;
        }

        .judul-rating h3 { font-size: 26px; font-weight: 600; color: #333; }

        .judul-rating a { font-size: 15px; color: #4b4b4b; text-decoration: none; }

        .buku-card {
            display: grid;
            grid-template-columns: repeat(4, 200px);
            justify-content: center;
            gap: 26px;
            margin: 20px auto 0;
            padding: 0 20px 50px;
        }

        .kotak-buku {
            width: 200px;
            padding: 14px;
            background: #6D4A36;
            color: #F7F4E9;
            display: flex;
            flex-direction: column;
            gap: 9px;
            cursor: pointer;
            transition: transform .2s, box-shadow .2s;
            text-decoration: none;
        }

        .kotak-buku:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, .2);
        }

        .kotak-buku .sampul {
            width: 100%;
            height: 240px;
            object-fit: cover;
        }

        .kotak-buku h2 {
            margin: 0;
            font-weight: 600;
            font-size: 14px;
            height: 55px;
            display: flex;
            align-items: center;
        }

        .kotak-buku hr {
            border: none;
            border-top: 1px solid rgba(247, 244, 233, .4);
        }

        .rating-row {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .rating-number { font-size: 14px; font-weight: 500; margin-right: auto; }

        .icon-wrapper { display: flex; gap: 4px; }

        .icon-aksi {
            font-size: 20px !important;
            cursor: pointer;
            transition: color .2s, transform .2s;
        }

        .icon-aksi:hover { transform: scale(1.2); }

        /* ── BARU DITAMBAHKAN ── */
        #baru_ditambahkan { padding: 30px 190px; }

        .cerita_container {
            display: flex;
            justify-content: space-between;
            gap: 25px;
        }

        .cerita_wrapper {
            position: relative;
            margin-top: 40px;
            width: 48%;
            height: 230px;
            display: flex;
            align-items: flex-end;
            cursor: pointer;
        }

        .coklat {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 80%;
            background: #6D4A37;
            border-radius: 8px;
            z-index: 1;
        }

        .sampul-baru {
            width: 170px;
            height: 250px;
            border-radius: 10px;
            z-index: 2;
            object-fit: cover;
            flex-shrink: 0;
        }

        .cerita_detail {
            z-index: 2;
            padding: 16px 16px 16px 20px;
            color: #F7F4E9;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 8px;
            overflow: hidden;
        }

        .detail_top_baru {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 8px;
        }

        .detail_top_baru h2 {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
        }

        .cerita_detail p {
            font-size: 11px;
            line-height: 1.5;
            text-align: justify;
        }

        /* ── SARAN ── */
        #ulasan {
            margin: 60px 0 120px;
            overflow: hidden;
        }

        #ulasan h2 {
            text-align: center;
            margin: 40px auto 24px;
            font-size: 24px;
            font-weight: 600;
        }

        #saran {
            display: block;
            width: fit-content;
            margin: 0 auto 50px;
            padding: 8px 35px;
            border-radius: 25px;
            font-family: 'Inter';
            font-weight: 700;
            font-size: 17px;
            text-decoration: none;
            color: #FEFDF7;
            background: #6D4A37;
            transition: all .3s;
        }

        #saran:hover { background: #5a3d2d; transform: scale(1.05); }

        .saran_container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 24px;
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px 0;
        }

        .saran_item {
            display: none;
            width: 520px;
            height: 230px;
            padding: 24px 28px;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, .13);
            flex-shrink: 0;
        }

        .saran_item.active { display: block; }

        .saran_top {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 14px;
            padding-bottom: 14px;
            border-bottom: 2px solid #f0f0f0;
        }

        .saran_top .profile { width: 42px; height: 42px; }
        .saran_top h3 { margin: 0; font-size: 16px; font-weight: 600; }

        .isi_saran p {
            font-size: 13px;
            line-height: 1.7;
            text-align: justify;
            color: #333;
        }

        .panah {
            font-size: 38px;
            cursor: pointer;
            user-select: none;
            color: #333;
            width: 40px;
            text-align: center;
        }

        /* ── TOAST ── */
        #toast {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            background: #333;
            color: #fff;
            padding: 10px 24px;
            border-radius: 100px;
            font-size: 13px;
            opacity: 0;
            pointer-events: none;
            transition: opacity .3s;
            z-index: 9999;
        }

        #toast.show { opacity: 1; }

        /* ── FOOTER ── */
        footer {
            background: #FFF5E7;
            font-family: "Inter", sans-serif;
            font-size: 14px;
            margin-top: 60px;
        }

        .footer_top {
            padding: 3em 4%;
            display: flex;
            gap: 50px;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
        }

        #tentang-web { max-width: 420px; }

        #tentang-web p {
            margin-top: 12px;
            line-height: 1.6;
            text-align: justify;
        }

        footer h3 { margin-bottom: 12px; font-size: 15px; color: #6D4A37; }

        footer ul { list-style: none; padding: 0; line-height: 2.2em; }

        footer a { text-decoration: none; color: #000; transition: color .2s; }
        footer a:hover { color: #6D4A37; }

        .footer_bottom {
            padding: 1.5em 4%;
            text-align: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer_bottom hr {
            border: none;
            border-top: 1px solid #dcd3c7;
            margin-bottom: 12px;
        }

        @media (max-width:900px) {
            .menu-icon { display: block; }

            .nav-menu {
                position: absolute;
                top: 65px; right: 0;
                background: #FEFDF7;
                flex-direction: column;
                width: 200px;
                padding: 15px 0;
                gap: 20px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, .2);
                border-radius: 8px;
                margin: 0;
                display: none;
            }

            .nav-menu.active { display: flex; }

            .buku-card { grid-template-columns: repeat(2, 200px); }

            #baru_ditambahkan { padding: 30px 20px; }

            .cerita_container { flex-direction: column; align-items: center; }

            .cerita_wrapper { width: 90%; }
        }
    </style>
</head>

<body>

    <nav>
        <div class="logo" onclick="location.href='landingpage1.php'">
            <img src="img/logoweb.svg" alt="logo">
            <p>Gema<br>Nusantara</p>
        </div>
        <div class="menu-icon" id="menu-icon"><i class="ph ph-list"></i></div>
        <div class="nav-menu" id="nav-menu">
            <a class="active" href="landingpage1.php">Beranda</a>
            <a href="jelajahi_belumlogin.php">Jelajahi</a>
            <img class="profile" src="img/profile.svg" alt="profil" onclick="location.href='masuk.php'"
                style="width:35px;height:35px;border-radius:50%;cursor:pointer;transition:transform .2s;"
                onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'"
                title="Masuk">
        </div>
    </nav>

    <script>
        document.getElementById('menu-icon').addEventListener('click', () => {
            document.getElementById('nav-menu').classList.toggle('active');
        });
    </script>

    <!-- HERO -->
    <section class="hero">
        <h2>Seribu Cerita Dalam Genggamanmu</h2>
        <a id="jelajahi" href="jelajahi_belumlogin.php">Jelajahi</a>
    </section>

    <!-- GAMBAR DEKORATIF -->
    <section style="text-align:center;">
        <div class="cerita_row">
            <img class="first"  src="img/ceritaabo.svg"     alt="buku 1">
            <img class="second" src="img/ceritabawang.svg"  alt="buku 2">
            <img class="third"  src="img/ceritalutung.svg"  alt="buku 3">
            <img class="second" src="img/ceritakeong.svg"   alt="buku 4">
            <img class="first"  src="img/ceritatimun.svg"   alt="buku 5">
        </div>
    </section>

    <!-- QUOTE -->
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

    <!-- RATING TERTINGGI -->
    <section class="section">
        <div class="judul-rating">
            <h3>Rating Tertinggi</h3>
            <a href="jelajahi_belumlogin.php">Lihat Semua &gt;</a>
        </div>
        <div class="buku-card">
            <?php if ($result_rating && $result_rating->num_rows > 0): ?>
                <?php while ($buku = $result_rating->fetch_assoc()): ?>
                    <!-- Klik kartu → redirect masuk.php -->
                    <div class="kotak-buku" onclick="redirectMasuk()">
                        <img class="sampul" src="buku/<?= htmlspecialchars($buku['sampul']) ?>"
                            alt="<?= htmlspecialchars($buku['judul']) ?>">
                        <h2><?= htmlspecialchars($buku['judul']) ?></h2>
                        <hr>
                        <div class="rating-row">
                            <span>⭐</span>
                            <span class="rating-number"><?= $buku['avg_rating'] ?? '-' ?></span>
                            <?= ikonKartu($buku['id']) ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color:#999;grid-column:span 4;">Belum ada data.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- POPULER BULAN INI -->
    <section class="section">
        <div class="judul-rating">
            <h3>Populer Bulan Ini</h3>
            <a href="jelajahi_belumlogin.php">Lihat Semua &gt;</a>
        </div>
        <div class="buku-card">
            <?php if ($result_populer && $result_populer->num_rows > 0): ?>
                <?php while ($buku = $result_populer->fetch_assoc()): ?>
                    <div class="kotak-buku" onclick="redirectMasuk()">
                        <img class="sampul" src="buku/<?= htmlspecialchars($buku['sampul']) ?>"
                            alt="<?= htmlspecialchars($buku['judul']) ?>">
                        <h2><?= htmlspecialchars($buku['judul']) ?></h2>
                        <hr>
                        <div class="rating-row">
                            <span>⭐</span>
                            <span class="rating-number"><?= $buku['avg_rating'] ?? '-' ?></span>
                            <?= ikonKartu($buku['id']) ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color:#999;grid-column:span 4;">Belum ada data.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- BARU DITAMBAHKAN -->
    <section id="baru_ditambahkan">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h2 style="font-size:26px;font-weight:600;">Baru Ditambahkan</h2>
            <a href="jelajahi_belumlogin.php" style="text-decoration:none;color:#4b4b4b;">Lihat Semua &gt;</a>
        </div>
        <div class="cerita_container">
            <?php if ($result_baru && $result_baru->num_rows > 0): ?>
                <?php while ($cerita = $result_baru->fetch_assoc()): ?>
                    <?php $sinopsis = mb_strimwidth($cerita['sinopsis'], 0, 180, "..."); ?>
                    <!-- Klik kartu → redirect masuk.php -->
                    <div class="cerita_wrapper" onclick="redirectMasuk()">
                        <div class="coklat"></div>
                        <img src="buku/<?= htmlspecialchars($cerita['sampul']) ?>" class="sampul-baru"
                            alt="<?= htmlspecialchars($cerita['judul']) ?>">
                        <div class="cerita_detail">
                            <div class="detail_top_baru">
                                <h2><?= htmlspecialchars($cerita['judul']) ?></h2>
                                <?= ikonKartu($cerita['id']) ?>
                            </div>
                            <p><?= htmlspecialchars($sinopsis) ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- SARAN — guest diarahkan login dulu -->
    <section id="ulasan">
        <h2>Belum Menemukan Cerita Rakyat Favoritmu?</h2>
        <!-- Tombol Beri Saran → masuk.php -->
        <a id="saran" href="masuk.php">Beri Saran</a>
        <div class="saran_container">
            <div class="panah" onclick="plusSaran(-1)">‹</div>
            <?php
            $ada = false;
            if ($result_saran && $result_saran->num_rows > 0) {
                $first = true;
                while ($s = $result_saran->fetch_assoc()) {
                    $ada = true;
                    $cls = $first ? 'active' : '';
                    $first = false;
                    echo "<div class='saran_item $cls'>
                        <div class='saran_top'><img class='profile' src='img/profile.svg' alt=''><h3>" . htmlspecialchars($s['nama']) . "</h3></div>
                        <div class='isi_saran'><p>\"" . htmlspecialchars($s['isi_saran']) . "\"</p></div>
                    </div>";
                }
            }
            if (!$ada): ?>
                <div class="saran_item active">
                    <div class="saran_top">
                        <img class="profile" src="img/profile.svg" alt="">
                        <h3>Andi Pratama</h3>
                    </div>
                    <div class="isi_saran">
                        <p>"Aku ada cerita rakyat dari daerahku, yaitu Si Pitung – Pahlawan Betawi."</p>
                    </div>
                </div>
            <?php endif; ?>
            <div class="panah" onclick="plusSaran(1)">›</div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="footer_top">
            <div id="tentang-web">
                <div class="logo"><img src="img/logoweb.svg" alt="">
                    <p>Gema<br>Nusantara</p>
                </div>
                <p>Temukan kembali pesona dongeng masa kecil Anda di Gema Nusantara — rumah digital bagi cerita rakyat
                    yang telah diwariskan turun-temurun.</p>
            </div>
            <div id="navigasi">
                <h3>Navigasi</h3>
                <ul>
                    <li><a href="landingpage1.php">Beranda</a></li>
                    <li><a href="jelajahi_belumlogin.php">Jelajahi</a></li>
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

    <!-- TOAST -->
    <div id="toast"></div>

    <script>
        function redirectMasuk() {
            showToast('Silakan masuk terlebih dahulu');
            setTimeout(() => { window.location.href = 'masuk.php'; }, 1000);
        }

        function showToast(msg) {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.classList.add('show');
            setTimeout(() => t.classList.remove('show'), 2500);
        }

        // Saran slider
        let saranIndex = 1;
        showSaran(saranIndex);
        function plusSaran(n) { showSaran(saranIndex += n); }
        function showSaran(n) {
            const items = document.getElementsByClassName('saran_item');
            if (!items.length) return;
            if (n > items.length) saranIndex = 1;
            if (n < 1) saranIndex = items.length;
            for (const i of items) i.style.display = 'none';
            items[saranIndex - 1].style.display = 'block';
        }
    </script>

</body>
</html>
<?php $conn->close(); ?>