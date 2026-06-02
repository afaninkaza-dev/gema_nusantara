<?php
include "koneksi.php";

// Handle search
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($search !== '') {
    $like = "%$search%";
    $stmt = $conn->prepare("
        SELECT c.*, ROUND(AVG(u.rating),1) AS avg_rating
        FROM cerita_rakyat c
        LEFT JOIN ulasan u ON c.id = u.cerita_id
        WHERE c.judul LIKE ?
        GROUP BY c.id ORDER BY c.id ASC
    ");
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("
        SELECT c.*, ROUND(AVG(u.rating),1) AS avg_rating
        FROM cerita_rakyat c
        LEFT JOIN ulasan u ON c.id = u.cerita_id
        GROUP BY c.id ORDER BY c.id ASC
    ");
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gema Nusantara - Jelajahi</title>
    <link rel="icon" href="img/logoweb.svg">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        crossorigin="anonymous" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 40px 20px 10px;
        }

        .hero h2 {
            font-size: 36px;
            font-weight: 300;
            margin: 30px 0 24px;
            text-shadow: 0 10px 15px rgba(0, 0, 0, .4);
        }

        .search-bar {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            position: relative;
        }

        .search-bar input[type="text"] {
            padding: 10px 15px;
            border: 1px solid #000;
            border-radius: 20px;
            width: 300px;
            font-family: "Poppins", sans-serif;
            font-size: 14px;
            outline: none;
        }

        .search-bar button {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: #000;
            color: #fff;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        /* ── GRID CERITA ── */
        .cerita-container {
            display: grid;
            grid-template-columns: repeat(4, 230px);
            gap: 50px;
            justify-content: center;
            margin: 60px auto 0;
            padding: 0 40px 120px;
        }

        /* ── KARTU BUKU ── */
        .cerita-wrapper {
            width: 100%;
            padding: 15px;
            background: #6D4A36;
            color: #F7F4E9;
            display: flex;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            transition: transform .2s, box-shadow .2s;
        }

        .cerita-wrapper:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, .2);
        }

        .sampul {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .cerita-wrapper h2 {
            margin: 0;
            font-weight: 600;
            font-size: 15px;
            height: 60px;
            display: flex;
            align-items: center;
        }

        .cerita-wrapper hr {
            margin: 0;
            border: none;
            border-top: 1px solid #F7F4E9;
        }

        .rating-row {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .rating-number {
            font-size: 14px;
            font-weight: 500;
            margin-right: auto;
        }

        .icon-aksi {
            font-size: 20px !important;
            cursor: pointer;
            transition: color .2s, transform .2s;
        }

        .icon-aksi:hover {
            transform: scale(1.2);
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

        footer h3 {
            margin-bottom: 12px;
            font-size: 15px;
            color: #6D4A37;
        }

        footer ul {
            list-style: none;
            padding: 0;
            line-height: 2.2em;
        }

        footer a {
            text-decoration: none;
            color: #000;
            transition: color .2s;
        }

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

        @media (max-width:1100px) {
            .cerita-container { grid-template-columns: repeat(3, 200px); gap: 30px; }
        }

        @media (max-width:750px) {
            .cerita-container { grid-template-columns: repeat(2, 200px); gap: 24px; }
            .hero h2 { font-size: 24px; }
        }

        @media (max-width:480px) {
            .cerita-container { grid-template-columns: repeat(1, 230px); }
        }

        @media (max-width:900px) {
            .menu-icon { display: block; }
            .nav-menu {
                position: absolute;
                top: 65px;
                right: 0;
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
            <a href="landingpage1.php">Beranda</a>
            <a class="active" href="jelajahi_belumlogin.php">Jelajahi</a>
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

    <section class="hero">
        <h2>Menyelami Kisah Tradisi dalam Genggaman Tangan</h2>
        <form class="search-bar" method="GET" action="jelajahi_belumlogin.php">
            <input type="text" name="q" placeholder="Telusuri judul buku"
                value="<?= htmlspecialchars($search) ?>">
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>
    </section>

    <div class="cerita-container">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($data = $result->fetch_assoc()): ?>
                <!-- Klik kartu → redirect ke masuk.php agar login dulu -->
                <div class="cerita-wrapper" onclick="redirectMasuk()">
                    <img class="sampul" src="buku/<?= htmlspecialchars($data['sampul']) ?>"
                        alt="<?= htmlspecialchars($data['judul']) ?>">
                    <h2><?= htmlspecialchars($data['judul']) ?></h2>
                    <hr>
                    <div class="rating-row">
                        <span>⭐</span>
                        <span class="rating-number">
                            <?= $data['avg_rating'] ? number_format($data['avg_rating'], 1) : '0.0' ?>
                        </span>
                        <!-- Ikon suka & simpan → redirect masuk.php -->
                        <span class="material-icons icon-aksi"
                            onclick="event.stopPropagation(); redirectMasuk()">favorite_border</span>
                        <span class="material-icons icon-aksi"
                            onclick="event.stopPropagation(); redirectMasuk()">bookmark_border</span>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="grid-column:1/-1;text-align:center;padding:60px;color:#94A3B8;">
                <p style="font-size:48px;">📚</p>
                <p style="margin-top:12px;font-size:16px;">
                    Cerita "<?= htmlspecialchars($search) ?>" tidak ditemukan.
                </p>
            </div>
        <?php endif; ?>
    </div>

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
    </script>

</body>
</html>
<?php $conn->close(); ?>