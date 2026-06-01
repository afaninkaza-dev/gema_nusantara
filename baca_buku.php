<?php
include "koneksi.php";
session_start();

$bab_id = isset($_GET['bab']) ? (int) $_GET['bab'] : 0;
if ($bab_id <= 0) {
    header("Location: jelajahi.php");
    exit;
}

// Ambil data bab sekarang + info cerita
$sql = "SELECT b.*, c.judul as judul_cerita, c.id as cerita_id, c.sampul 
        FROM bab b 
        JOIN cerita_rakyat c ON b.cerita_id = c.id 
        WHERE b.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $bab_id);
$stmt->execute();
$bab = $stmt->get_result()->fetch_assoc();
if (!$bab) {
    header("Location: jelajahi.php");
    exit;
}

$cerita_id = $bab['cerita_id'];

// Ambil semua bab untuk navigasi prev/next
$sql_all = "SELECT id, judul_bab FROM bab WHERE cerita_id = ? ORDER BY id ASC";
$stmt_all = $conn->prepare($sql_all);
$stmt_all->bind_param("i", $cerita_id);
$stmt_all->execute();
$semua_bab = $stmt_all->get_result()->fetch_all(MYSQLI_ASSOC);

// Cari posisi bab sekarang
$posisi = null;
foreach ($semua_bab as $i => $b) {
    if ($b['id'] == $bab_id) {
        $posisi = $i;
        break;
    }
}
$prev_bab = $posisi > 0 ? $semua_bab[$posisi - 1] : null;
$next_bab = $posisi < count($semua_bab) - 1 ? $semua_bab[$posisi + 1] : null;

// Ambil ulasan dengan pagination
$per_page = 3;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

$sql_ulasan = "SELECT u.*, usr.nama, usr.foto 
               FROM ulasan u 
               JOIN users usr ON u.user_id = usr.id 
               WHERE u.cerita_id = ? 
               ORDER BY u.created_at DESC 
               LIMIT ? OFFSET ?";
$stmt_ul = $conn->prepare($sql_ulasan);
$stmt_ul->bind_param("iii", $cerita_id, $per_page, $offset);
$stmt_ul->execute();
$ulasan_list = $stmt_ul->get_result()->fetch_all(MYSQLI_ASSOC);

// Hitung total ulasan untuk pagination
$sql_count = "SELECT COUNT(*) as total FROM ulasan WHERE cerita_id = ?";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param("i", $cerita_id);
$stmt_count->execute();
$total_ulasan = $stmt_count->get_result()->fetch_assoc()['total'];
$total_page = ceil($total_ulasan / $per_page);

function bintang($r)
{
    return str_repeat('★', (int) $r) . str_repeat('☆', 5 - (int) $r);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="baca_cerita.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@400;500;600&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            margin: 0;
            padding-top: 70px;
            font-family: "Poppins", sans-serif;
            background-color: #FEFDF7;
            color: #000;
        }

        nav {
            background-color: #FEFDF7;
            font-family: 'Inter', sans-serif;
            padding: 0.5em 1em;
            display: flex;
            align-items: center;
            box-shadow: 0 0 10px #8d8a8a;
            position: fixed;
            top: 0; left: 0; right: 0;
            width: 100%;
            z-index: 1000;
            box-sizing: border-box;
        }

        .logo { width: 50px; display: flex; align-items: center; margin-right: auto; cursor: pointer; gap: 8px; }
        .logo p { font-size: 12px; line-height: 1em; font-weight: 600; color: #6D4A37; margin: 0; }
        nav .profile:hover { transform: scale(1.1); }
        nav a { text-decoration: none; font-weight: 500; color: #918d8a; font-size: 16px; margin-right: 2em; transition: all 0.3s ease; }
        nav a:hover, nav a.active { color: #000; font-weight: 700; }
        nav .profile { width: 30px; height: 30px; cursor: pointer; }

        .nav-menu { display: flex; align-items: center; gap: 20px; }
        .menu-icon { display: none; font-size: 25px; cursor: pointer; }

        #cerita {
            display: flex;
            flex-direction: column;
            margin: 0;
            padding: 20px 150px;
            position: relative;
        }

        .sampul_container {
            position: relative;
            width: 100%;
            display: flex;
            justify-content: center;
            margin-bottom: -100px;
            z-index: 2;
        }

        .sampul { width: 300px; height: auto; }

        .cerita_wrapper {
            background-color: #fff;
            box-shadow: 0 0 10px rgba(141,138,138,0.3);
            border-radius: 8px;
            padding: 120px 60px 40px;
            position: relative;
            z-index: 1;
        }

        .isi_cerita { margin-bottom: 30px; }

        .isi_cerita h1 {
            text-align: center;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #ddd;
        }

        .isi_cerita h2 {
            font-size: 20px;
            font-weight: 600;
            margin: 30px 0 20px;
        }

        .isi_teks p, .isi_cerita p {
            text-align: justify;
            line-height: 1.8;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .buttons {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 50px;
            padding: 30px 0;
            border-top: 2px solid #ddd;
        }

        .button {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 40px;
            background-color: #6D4A37;
            border-radius: 100px;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
        }

        .button p { margin: 0; }
        .button:hover { background-color: #5a3a2a; transform: scale(1.05); }

        .ulasan_header { display: flex; align-items: center; margin-bottom: 30px; }
        .ulasan_header .coklat { width: 5px; height: 30px; background-color: #6D4A37; margin-right: 15px; border-radius: 2px; }
        .ulasan_header h2 { font-size: 24px; font-weight: 600; margin: 0; }

        .ulasan_container { display: flex; flex-direction: column; gap: 25px; margin-bottom: 30px; }

        .ulasan { border-bottom: 2px solid #ddd; padding-bottom: 20px; }

        .ulasan_top { display: flex; align-items: center; gap: 15px; margin-bottom: 5px; }
        .ulasan_top .profile { width: 30px; height: 30px; border-radius: 50%; object-fit: cover; }

        .ulasan_info { display: flex; flex-direction: column; gap: 5px; }
        .ulasan_info h3 { font-size: 16px; font-weight: 600; margin: 0; }

        .rating { color: #FFD700; font-size: 20px; }

        .isi_ulasan p { text-align: justify; line-height: 1.6; font-size: 14px; margin: 0 0 5px; color: #333; }
        .isi_ulasan .tanggal { font-size: 12px; color: #999; margin-top: 10px; }

        .ulasan_bottom { display: flex; flex-direction: column; }

        .pagination { margin: 10px 0 20px; }
        .pagination a { color: black; float: left; padding: 8px 16px; text-decoration: none; transition: background-color .3s; }
        .pagination a.active { background-color: #6D4A37; color: white; }
        .pagination a:hover:not(.active) { background-color: #ddd; }

        .ulasan_button { display: flex; justify-content: center; margin-top: 30px; }
        .ulasan_button button {
            padding: 12px 60px;
            background-color: #6D4A37;
            color: white;
            border: none;
            border-radius: 100px;
            font-size: 16px;
            font-weight: 500;
            font-family: 'Poppins';
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .ulasan_button button:hover { background-color: #5a3a2a; transform: scale(1.05); }

        footer { background-color: #FFF5E7; font-family: "Inter"; font-size: 14px; }
        .footer_top { padding: 3em 4em; display: flex; gap: 150px; }
        .footer_bottom { padding: 0.5em 4em; text-align: center; }
        .footer_bottom hr { border-top: 1.2px solid #000; }
        #tentang-web, #navigasi, #kontak { max-width: 25em; }
        footer ul { list-style-type: none; padding-inline-start: 0; line-height: 2em; }
        footer a { text-decoration: none; color: #000; }

        @media (max-width: 900px) {
            #cerita { padding: 20px 20px; }
            .cerita_wrapper { padding: 120px 20px 30px; }
            .menu-icon { display: block; }
            .nav-menu {
                display: none;
                position: absolute; top: 60px; right: 0;
                background: #FEFDF7;
                flex-direction: column;
                width: 200px; padding: 15px 0; gap: 20px;
                box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            }
            .nav-menu.active { display: flex; }
        }
    </style>
    <title><?= htmlspecialchars($bab['judul_bab']) ?> — <?= htmlspecialchars($bab['judul_cerita']) ?></title>
</head>

<body>
    <header>
        <nav>
            <div class="logo">
                <img src="img/logoweb.svg" alt="logo web">
                <p>Gema <br>Nusantara</p>
            </div>
            <div class="menu-icon" id="menu-icon">
                <i class="ph ph-list"></i>
            </div>
            <div class="nav-menu" id="nav-menu">
                <a href="landingpage.html">Beranda</a>
                <a class="active" href="jelajahi.html">Jelajahi</a>
                <a href="saran.html">Saran</a>
                <a href="settingakun_baru.html">
                    <img class="profile" src="img/profile22.svg" alt="user profile">
                </a>
            </div>
        </nav>
    </header>

    <section id="cerita">
        <!-- SAMPUL -->
        <div class="sampul_container">
            <img class="sampul" src="buku/<?= htmlspecialchars($bab['sampul']) ?>"
                alt="Sampul <?= htmlspecialchars($bab['judul_cerita']) ?>">
        </div>

        <div class="cerita_wrapper">
            <!-- ISI BAB -->
            <div class="isi_cerita">
                <h1><?= htmlspecialchars($bab['judul_cerita']) ?></h1>
                <h2><?= htmlspecialchars($bab['judul_bab']) ?></h2>
                <div class="isi_teks">
                    <?= $bab['isi'] /* isi sudah HTML dari editor, langsung echo */ ?>
                </div>
            </div>

            <!-- TOMBOL PREV / NEXT -->
            <div class="buttons">
                <?php if ($prev_bab): ?>
                    <a href="baca_buku.php?bab=<?= $prev_bab['id'] ?>" class="button">
                        <p>◀</p>
                        <p><?= htmlspecialchars($prev_bab['judul_bab']) ?></p>
                    </a>
                <?php else: ?>
                    <a href="detailbuku.php?id=<?= $cerita_id ?>" class="button">
                        <p>◀</p>
                        <p>Detail Cerita</p>
                    </a>
                <?php endif; ?>

                <?php if ($next_bab): ?>
                    <a href="baca_buku.php?bab=<?= $next_bab['id'] ?>" class="button">
                        <p><?= htmlspecialchars($next_bab['judul_bab']) ?></p>
                        <p>▶</p>
                    </a>
                <?php else: ?>
                    <div class="button" style="opacity:0.4;cursor:default;">
                        <p>Bab Terakhir</p>
                        <p>▶</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ULASAN -->
            <div class="ulasan_section">
                <div class="ulasan_header">
                    <div class="coklat"></div>
                    <h2>Ulasan</h2>
                </div>

                <div class="ulasan_container">
                    <?php if (!empty($ulasan_list)): ?>
                        <?php foreach ($ulasan_list as $u): ?>
                            <div class="ulasan">
                                <div class="ulasan_top">
                                    <img class="profile"
                                        src="<?= $u['foto'] ? 'uploads/' . htmlspecialchars($u['foto']) : 'img/profile.svg' ?>"
                                        alt="User">
                                    <h3><?= htmlspecialchars($u['nama']) ?></h3>
                                    <div class="rating"><?= bintang($u['rating']) ?></div>
                                </div>
                                <div class="isi_ulasan">
                                    <p><?= htmlspecialchars($u['komentar']) ?></p>
                                    <p class="tanggal"><?= date('Y - m - d', strtotime($u['created_at'])) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color:#94A3B8;font-size:14px;">Belum ada ulasan untuk cerita ini.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- PAGINATION -->
            <div class="ulasan_bottom">
                <div class="pagination">
                    <a href="?bab=<?= $bab_id ?>&page=<?= max(1, $page - 1) ?>">&laquo;</a>

                    <?php for ($p = 1; $p <= $total_page; $p++): ?>
                        <a href="?bab=<?= $bab_id ?>&page=<?= $p ?>" class="<?= $p == $page ? 'active' : '' ?>">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>

                    <a href="?bab=<?= $bab_id ?>&page=<?= min($total_page, $page + 1) ?>">&raquo;</a>
                </div>

                <div class="ulasan_button">
                    <button id="open_ulasan">Beri Ulasan</button>
                </div>
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
                    yang telah diwariskan turun-temurun, dikemas dalam format yang mudah dinikmati kapan saja.</p>
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
            <p>Copyright @Gema Nusantara 2025</p>
        </div>
    </footer>

    <script>
        // Menu mobile
        const menuIcon = document.getElementById("menu-icon");
        const navMenu = document.getElementById("nav-menu");
        menuIcon.addEventListener("click", () => navMenu.classList.toggle("active"));
    </script>
</body>

</html>