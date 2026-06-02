<?php
session_start();
include "koneksi.php";
$user_id = $_SESSION['id'] ?? 0;
$logged_in = $user_id > 0;

// Ambil ID cerita dari URL
$id_cerita = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id_cerita <= 0) {
    header("Location: jelajahi.php");
    exit;
}

// Ambil data cerita lengkap
$sql_cerita = "SELECT * FROM cerita_rakyat WHERE id = ?";
$stmt_c = $conn->prepare($sql_cerita);
$stmt_c->bind_param("i", $id_cerita);
$stmt_c->execute();
$cerita = $stmt_c->get_result()->fetch_assoc();
if (!$cerita) {
    header("Location: jelajahi.php");
    exit;
}

// FIX: Ambil foto profil navbar dari database
$_nav_foto = 'img/profile.jpg';
if ($logged_in) {
    $_q_foto = $conn->prepare("SELECT foto FROM user WHERE id = ?");
    $_q_foto->bind_param("i", $user_id);
    $_q_foto->execute();
    $_nav_user = $_q_foto->get_result()->fetch_assoc();
    if (!empty($_nav_user['foto']) && file_exists($_nav_user['foto'])) {
        $_nav_foto = $_nav_user['foto'];
    }
}

// Ambil bab pertama (untuk tombol Baca Sekarang)
$sql_bab1 = "SELECT id FROM bab WHERE cerita_id = ? ORDER BY id ASC LIMIT 1";
$stmt_b = $conn->prepare($sql_bab1);
$stmt_b->bind_param("i", $id_cerita);
$stmt_b->execute();
$bab_pertama = $stmt_b->get_result()->fetch_assoc();

// Hitung jumlah bab
$sql_jbab = "SELECT COUNT(*) as total FROM bab WHERE cerita_id = ?";
$stmt_jb = $conn->prepare($sql_jbab);
$stmt_jb->bind_param("i", $id_cerita);
$stmt_jb->execute();
$jumlah_bab = $stmt_jb->get_result()->fetch_assoc()['total'];

// Ambil ulasan
$sql_ul = "SELECT u.*, usr.nama, usr.foto FROM ulasan u 
           JOIN user usr ON u.user_id = usr.id 
           WHERE u.cerita_id = ? ORDER BY u.waktu DESC";
$stmt_ul = $conn->prepare($sql_ul);
$stmt_ul->bind_param("i", $id_cerita);
$stmt_ul->execute();
$ulasan_list = $stmt_ul->get_result()->fetch_all(MYSQLI_ASSOC);

// Hitung rata-rata rating & distribusi
$distribusi = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
$total_rating = 0;
foreach ($ulasan_list as $u) {
    $total_rating += $u['rating'];
    if (isset($distribusi[$u['rating']]))
        $distribusi[$u['rating']]++;
}
$jumlah_ulasan = count($ulasan_list);
$rata_rata = $jumlah_ulasan > 0 ? round($total_rating / $jumlah_ulasan, 1) : 0;

function bintang($r)
{
    return str_repeat('★', (int) $r) . str_repeat('☆', 5 - (int) $r);
}

// Cek status suka & simpan milik user
$sudah_suka = false;
$sudah_simpan = false;
if ($logged_in) {
    $cs = $conn->prepare("SELECT id FROM suka WHERE user_id = ? AND cerita_id = ?");
    $cs->bind_param("ii", $user_id, $id_cerita);
    $cs->execute();
    $sudah_suka = $cs->get_result()->num_rows > 0;

    $ck = $conn->prepare("SELECT id FROM simpan WHERE user_id = ? AND cerita_id = ?");
    $ck->bind_param("ii", $user_id, $id_cerita);
    $ck->execute();
    $sudah_simpan = $ck->get_result()->num_rows > 0;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gema Nusantara</title>
    <link rel="icon" href="img/logoweb.svg">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        crossorigin="anonymous" />
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-datalabels/2.2.0/chartjs-plugin-datalabels.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            padding-top: 70px;
            font-family: "Poppins", sans-serif;
            background-color: #FEFDF7;
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

        .logo img {
            width: 40px;
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
            transition: all .3s;
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
            object-fit: cover;
            cursor: pointer;
            transition: transform .2s;
        }

        nav .profile:hover {
            transform: scale(1.1);
        }

        .profile-dropdown {
            position: relative;
            display: inline-flex;
            align-items: center;
        }

        .profile-dropdown .dropdown-menu {
            display: none;
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .15);
            min-width: 180px;
            padding: 8px 0;
            z-index: 2000;
        }

        .profile-dropdown .dropdown-menu.open {
            display: block;
        }

        .dropdown-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 18px;
            font-size: 14px;
            font-weight: 500;
            color: #333;
            text-decoration: none;
            transition: background .15s;
            font-family: 'Poppins', sans-serif;
        }

        .dropdown-menu a:hover {
            background: #f5f0ed;
            color: #6D4A37;
        }

        .dropdown-menu a.keluar {
            color: #C0392B;
        }

        .dropdown-menu a.keluar:hover {
            background: #fdf0ee;
        }

        .dropdown-menu hr {
            border: none;
            border-top: 1px solid #eee;
            margin: 4px 0;
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
                box-shadow: 0 4px 10px rgba(0, 0, 0, .2);
                border-radius: 8px;
                margin: 0;
                display: none;
            }

            .nav-menu.active {
                display: flex;
            }
        }

        /* ── DETAIL TOP ── */
        .detail_top {
            background-color: #FFF5E7;
            display: flex;
            flex-direction: row;
            padding: 75px 0px 0px 125px;
            gap: 50px;
        }

        #sampul {
            width: 260px;
            height: 350px;
            border-radius: 5px;
            object-fit: cover;
        }

        .detail {
            display: flex;
            flex-direction: column;
        }

        .detail h2 {
            font-size: 25px;
            font-weight: 700;
        }

        .detail p {
            font-size: 15px;
            font-weight: 500;
        }

        .judul_cerita {
            font-size: 35px;
            font-weight: 500;
        }

        .update p,
        .actions_detail {
            font-size: 14px;
            font-weight: 400;
            margin: 0;
        }

        .detail h2,
        .detail p {
            margin: 0;
        }

        .asal_daerah {
            font-size: 16px;
            font-weight: 600;
        }

        .isi_rating,
        .update,
        .detail_bab,
        .detail_baca {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .top_rating {
            display: flex;
            flex-direction: column;
        }

        .top_rating .rating {
            font-size: 35px;
            color: #FFD700;
            margin: 0;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: auto 0px;
        }

        .button {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 24px;
            background-color: #6D4A37;
            border-radius: 100px;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 15px;
            font-weight: 500;
            text-decoration: none;
        }

        .button:hover {
            background-color: #5a3a2a;
            transform: scale(1.05);
        }

        /* ── SINOPSIS & ULASAN ── */
        .sinopsis,
        .ulasan {
            padding: 50px 200px 0px 125px;
        }

        .sinopsis_header,
        .ulasan_header {
            display: flex;
            align-items: center;
        }

        .coklat {
            width: 5px;
            height: 30px;
            background-color: #6D4A37;
            margin-right: 15px;
            border-radius: 2px;
        }

        .sinopsis_header h2,
        .ulasan_header h2 {
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }

        .sinopsis p,
        .ulasan_header p {
            text-align: justify;
            font-size: 14px;
        }

        .ulasan_rangkuman {
            display: flex;
            flex-direction: row;
        }

        .jumlah_rating {
            width: 500px;
        }

        .grafik_detail {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .ulasan_button {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }

        .ulasan_button button {
            padding: 8px 50px;
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

        .ulasan_button button:hover {
            background-color: #5a3a2a;
            transform: scale(1.05);
        }

        #popup {
            display: none;
            justify-content: center;
            align-items: center;
            width: fit-content;
            height: fit-content;
            margin: auto;
            padding: 5px;
            background-color: #fff;
            gap: 10px;
            z-index: 2;
        }

        #close_ulasan {
            position: relative;
            top: 10px;
            left: 0px;
            cursor: pointer;
            color: #000;
        }

        #popup.active {
            display: flex;
            flex-direction: column;
            box-shadow: 0 0 10px #8d8a8a;
        }

        #popup.active h2 {
            font-size: 20px;
            font-weight: 500;
            margin: 0px;
        }

        .beri_rating {
            display: flex;
            flex-direction: row;
            padding: 5px 10px;
            gap: 2px;
            border: 1px solid #8d8a8a;
            border-radius: 6px;
        }

        .ulasan_container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-bottom: 30px;
            padding: 0px;
        }

        .ulasan_container .ulasan {
            border-bottom: 2px solid #ddd;
            padding: 0px;
            padding-bottom: 10px;
        }

        .ulasan_container :first-child {
            margin-top: 10px;
        }

        .ulasan_top {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .ulasan_top .profile {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            margin-bottom: 10px;
        }

        .ulasan_info {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .ulasan_info h3 {
            font-size: 12px;
            font-weight: 600;
            margin: 0;
        }

        .rating {
            color: #FFD700;
            font-size: 25px;
        }

        .isi_ulasan p {
            text-align: justify;
            font-size: 13px;
            color: #333;
            margin: 5px;
        }

        .isi_ulasan .tanggal {
            font-size: 10px;
            color: #999;
            margin-top: 10px;
        }

        .notif {
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 16px;
        }

        .notif_sukses {
            background: #d4edda;
            color: #1a5c2a;
            border: 1px solid #b7dfc4;
        }

        .notif_error {
            background: #fde8e8;
            color: #8b1a1a;
            border: 1px solid #f5c6c6;
        }

        #star_container {
            border: none;
            padding: 0;
            gap: 4px;
        }

        .star {
            font-size: 30px;
            color: #ccc;
            cursor: pointer;
            transition: color 0.15s, transform 0.1s;
            user-select: none;
        }

        .star.aktif,
        .star.hover {
            color: #FFD700;
            transform: scale(1.15);
        }

        #popup form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            width: 100%;
        }

        #popup textarea {
            width: 100%;
        }

        #popup .kirim_ulasan {
            padding: 8px 30px;
            background: #6D4A37;
            color: #fff;
            border: none;
            border-radius: 100px;
            font-size: 15px;
            font-family: 'Poppins';
            cursor: pointer;
            transition: background 0.2s;
        }

        #popup .kirim_ulasan:hover {
            background: #5a3a2a;
        }

        .icon-action {
            transition: color 0.2s, transform 0.2s;
            cursor: pointer;
        }

        .icon-action:hover {
            transform: scale(1.15);
        }

        .aktif-suka {
            color: #E74C3C !important;
            font-variation-settings: 'FILL' 1;
        }

        .aktif-simpan {
            color: #6D4A37 !important;
            font-variation-settings: 'FILL' 1;
        }

        #toast {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%) translateY(20px);
            background: #333;
            color: #fff;
            padding: 10px 22px;
            border-radius: 20px;
            font-size: 13px;
            opacity: 0;
            transition: opacity .3s, transform .3s;
            pointer-events: none;
            z-index: 9999;
        }

        #toast.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        footer {
            background-color: #FFF5E7;
            font-family: "Inter", sans-serif;
            font-size: 14px;
        }

        .footer_top {
            padding: 3em 4%;
            display: flex;
            gap: 50px;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
        }

        #tentang-web {
            max-width: 420px;
        }

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
            margin-bottom: 12px;
        }

        a {
            text-decoration: none;
            color: #000;
        }
    </style>
</head>

<body>
    <nav>
        <div class="logo" onclick="location.href='landingpage.php'">
            <img src="img/logoweb.svg" alt="logo">
            <p>Gema<br>Nusantara</p>
        </div>
        <div class="menu-icon" id="menu-icon"><i class="ph ph-list"></i></div>
        <div class="nav-menu" id="nav-menu">
            <a href="landingpage.php">Beranda</a>
            <a class="active" href="jelajahi.php">Jelajahi</a>
            <a href="saran.php">Saran</a>
            <?php if ($logged_in): ?>
                <div class="profile-dropdown">
                    <!-- FIX: pakai $_nav_foto dari database, bukan hardcode -->
                    <img class="profile" src="<?= htmlspecialchars($_nav_foto) ?>" alt="profil" id="profileBtn"
                        onclick="toggleDropdown()">
                    <div class="dropdown-menu" id="profileDropdown">
                        <a href="settingakun_baru.php">
                            <i style="font-size:16px;width:18px;text-align:center;" class="fas fa-user"></i> Profil
                        </a>
                        <hr>
                        <a href="masuk.php" class="keluar">
                            <i style="font-size:16px;width:18px;text-align:center;" class="fas fa-sign-out-alt"></i> Keluar
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="masuk.php">Masuk</a>
            <?php endif; ?>
        </div>
    </nav>

    <script>
        document.getElementById('menu-icon').addEventListener('click', () => {
            document.getElementById('nav-menu').classList.toggle('active');
        });
        function toggleDropdown() {
            document.getElementById('profileDropdown').classList.toggle('open');
        }
        document.addEventListener('click', function (e) {
            const btn = document.getElementById('profileBtn');
            const dd = document.getElementById('profileDropdown');
            if (dd && btn && !btn.contains(e.target) && !dd.contains(e.target))
                dd.classList.remove('open');
        });
    </script>

    <section class="detail_top">
        <img id="sampul" src="buku/<?= htmlspecialchars($cerita['sampul']) ?>"
            alt="Sampul <?= htmlspecialchars($cerita['judul']) ?>">

        <div class="detail">
            <h2><span class="judul_cerita"><?= htmlspecialchars($cerita['judul']) ?></span></h2>
            <div class="update">
                <span class="material-symbols-outlined icon-jam">schedule</span>
                <p>Terakhir diperbarui: <?= date('d - m - Y', strtotime($cerita['waktu'] ?? 'now')) ?></p>
            </div>
            <h2><span class="asal_daerah">Asal Daerah: <?= htmlspecialchars($cerita['asal_daerah']) ?></span></h2>
            <div class="top_rating">
                <div class="isi_rating">
                    <h2><?= $rata_rata ?></h2>
                    <p><span class="rating"><?= bintang($rata_rata) ?></span></p>
                </div>
                <p><span class="actions_detail"><?= $jumlah_ulasan ?> Ulasan</span></p>
            </div>
            <div class="detail_bab">
                <h2><?= $jumlah_bab ?></h2>
                <p>Bab</p>
            </div>
            <div class="detail_baca">
                <h2><?= htmlspecialchars($cerita['jumlah_baca'] ?? 0) ?></h2>
                <p>Dibaca</p>
            </div>
            <div class="actions">
                <?php if ($bab_pertama): ?>
                    <a href="baca_buku.php?bab=<?= $bab_pertama['id'] ?>" class="button">Baca Sekarang</a>
                <?php else: ?>
                    <span class="button" style="opacity:0.5;cursor:default;">Belum Ada Bab</span>
                <?php endif; ?>
                <span class="material-symbols-outlined icon-action <?= $sudah_suka ? 'aktif-suka' : '' ?>" id="btn-suka"
                    data-id="<?= $id_cerita ?>"
                    title="<?= $logged_in ? ($sudah_suka ? 'Hapus dari Suka' : 'Suka') : 'Masuk untuk menyukai' ?>"
                    onclick="toggleAksi('suka')"
                    style="<?= $sudah_suka ? "color:#E74C3C;font-variation-settings:'FILL' 1" : '' ?>">favorite</span>
                <span class="material-symbols-outlined icon-action <?= $sudah_simpan ? 'aktif-simpan' : '' ?>"
                    id="btn-simpan" data-id="<?= $id_cerita ?>"
                    title="<?= $logged_in ? ($sudah_simpan ? 'Hapus dari Simpanan' : 'Simpan') : 'Masuk untuk menyimpan' ?>"
                    onclick="toggleAksi('simpan')"
                    style="<?= $sudah_simpan ? "color:#6D4A37;font-variation-settings:'FILL' 1" : '' ?>">bookmark</span>
                <span class="material-symbols-outlined icon-action" title="Bagikan" onclick="bagikan()">share</span>
            </div>
        </div>
    </section>

    <section class="sinopsis">
        <div class="sinopsis_header">
            <div class="coklat"></div>
            <h2>Sinopsis</h2>
        </div>
        <p><?= nl2br(htmlspecialchars($cerita['sinopsis'])) ?></p>
    </section>

    <section class="ulasan">
        <div class="ulasan_header">
            <div class="coklat"></div>
            <h2>Ulasan</h2>
        </div>

        <?php if (isset($_GET['sukses'])): ?>
            <div class="notif notif_sukses">✅ Ulasan berhasil dikirim!</div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="notif notif_error">❌ Terjadi kesalahan. Pastikan rating dan ulasan sudah diisi.</div>
        <?php endif; ?>

        <div class="ulasan_rangkuman">
            <div class="jumlah_rating">
                <canvas id="grafikRating"></canvas>
            </div>

            <hr class="pembatas">

            <div class="grafik_detail">
                <h2><?= $rata_rata ?>/5</h2>
                <div class="rating"><?= bintang($rata_rata) ?></div>
                <p><span class="actions_detail"><?= $jumlah_ulasan ?> Ulasan</span></p>
                <div class="ulasan_button">
                    <button id="open_ulasan">Beri Ulasan</button>
                </div>

                <div id="popup">
                    <span id="close_ulasan">x</span>
                    <h2>Tulis Ulasan</h2>
                    <p>Ceritakan kesan dan pengalamanmu membaca <?= htmlspecialchars($cerita['judul']) ?></p>
                    <?php if (!$logged_in): ?>
                        <p style="color:#C0392B;font-size:13px;text-align:center;">
                            <a href="masuk.php" style="color:#6D4A37;font-weight:600;">Masuk</a> terlebih dahulu untuk
                            memberi ulasan.
                        </p>
                    <?php else: ?>
                        <form method="POST" action="kirim_ulasan.php" id="form_ulasan">
                            <input type="hidden" name="cerita_id" value="<?= $id_cerita ?>">
                            <input type="hidden" name="rating" id="input_rating" value="0">
                            <div class="beri_rating" id="star_container">
                                <span class="star" data-value="1">☆</span>
                                <span class="star" data-value="2">☆</span>
                                <span class="star" data-value="3">☆</span>
                                <span class="star" data-value="4">☆</span>
                                <span class="star" data-value="5">☆</span>
                            </div>
                            <small id="rating_error" style="color:#C0392B;display:none;">Pilih rating terlebih
                                dahulu.</small>
                            <textarea id="deskripsi" name="isi_ulasan" placeholder="Tulis ulasanmu disini..."
                                required></textarea>
                            <button type="submit" class="kirim_ulasan">Kirim Ulasan</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="ulasan_container">
            <?php if (!empty($ulasan_list)): ?>
                <?php foreach ($ulasan_list as $u): ?>
                    <div class="ulasan">
                        <div class="ulasan_top">
                            <!-- FIX: foto profil ulasan yang benar -->
                            <?php $foto_u = (!empty($u['foto']) && file_exists($u['foto'])) ? $u['foto'] : 'img/profile.jpg'; ?>
                            <img class="profile" src="<?= htmlspecialchars($foto_u) ?>" alt="<?= htmlspecialchars($u['nama']) ?>">
                            <h3><?= htmlspecialchars($u['nama']) ?></h3>
                            <div class="rating"><?= bintang($u['rating']) ?></div>
                        </div>
                        <div class="isi_ulasan">
                            <p><?= htmlspecialchars($u['isi_ulasan']) ?></p>
                            <p class="tanggal"><?= date('d M Y', strtotime($u['waktu'])) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color:#94A3B8;font-size:14px;padding:20px 0;">Belum ada ulasan untuk cerita ini.</p>
            <?php endif; ?>
        </div>
    </section>

    <footer>
        <div class="footer_top">
            <div id="tentang-web">
                <div class="logo">
                    <img src="img/logoweb.svg" alt="Logo Gema Nusantara">
                    <p>Gema<br>Nusantara</p>
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

    <script>
        const openUlasan = document.getElementById('open_ulasan');
        const closeUlasan = document.getElementById('close_ulasan');
        const popup = document.getElementById('popup');

        function openPopUpUlasan() { popup.classList.add('active'); }
        function closePopUpUlasan() { popup.classList.remove('active'); }

        openUlasan.addEventListener('click', openPopUpUlasan);
        closeUlasan.addEventListener('click', closePopUpUlasan);

        const stars = document.querySelectorAll('.star');
        const inputRating = document.getElementById('input_rating');
        let selectedRating = 0;

        if (stars.length) {
            stars.forEach(star => {
                star.addEventListener('mouseover', () => {
                    const val = +star.dataset.value;
                    stars.forEach(s => s.classList.toggle('hover', +s.dataset.value <= val));
                });
                star.addEventListener('mouseout', () => {
                    stars.forEach(s => s.classList.remove('hover'));
                });
                star.addEventListener('click', () => {
                    selectedRating = +star.dataset.value;
                    inputRating.value = selectedRating;
                    stars.forEach(s => s.classList.toggle('aktif', +s.dataset.value <= selectedRating));
                    stars.forEach(s => s.textContent = +s.dataset.value <= selectedRating ? '★' : '☆');
                    document.getElementById('rating_error').style.display = 'none';
                });
            });
        }

        const formUlasan = document.getElementById('form_ulasan');
        if (formUlasan) {
            formUlasan.addEventListener('submit', function (e) {
                if (selectedRating === 0) {
                    e.preventDefault();
                    document.getElementById('rating_error').style.display = 'block';
                }
            });
        }

        document.addEventListener('click', function (e) {
            if (popup && popup.classList.contains('active') &&
                !popup.contains(e.target) && e.target !== openUlasan) {
                closePopUpUlasan();
            }
        });

        Chart.register(ChartDataLabels);
        const jumlahRating = ['5', '4', '3', '2', '1'];
        const rating = [<?= $distribusi[5] ?>, <?= $distribusi[4] ?>, <?= $distribusi[3] ?>, <?= $distribusi[2] ?>, <?= $distribusi[1] ?>];
        const ctx = document.getElementById('grafikRating').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: jumlahRating,
                datasets: [{
                    label: 'Jumlah Rating',
                    data: rating,
                    backgroundColor: ['#FFD700'],
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: {
                    datalabels: {
                        color: 'black',
                        font: { weight: '500' },
                        align: 'end',
                        anchor: 'end',
                        display: true,
                    }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 5 } }
                }
            }
        });
    </script>

    <div id="toast"></div>

    <script>
        const IS_LOGIN = <?= $logged_in ? 'true' : 'false' ?>;

        function tampilToast(pesan, durasi = 2500) {
            const t = document.getElementById('toast');
            t.textContent = pesan;
            t.classList.add('show');
            setTimeout(() => t.classList.remove('show'), durasi);
        }

        function toggleAksi(jenis) {
            if (!IS_LOGIN) {
                if (confirm('Kamu perlu masuk untuk melakukan ini. Masuk sekarang?'))
                    window.location.href = 'masuk.php';
                return;
            }
            const btn = document.getElementById('btn-' + jenis);
            const ceritaId = btn.dataset.id;
            const url = jenis === 'suka' ? 'toggle_suka.php' : 'toggle_simpan.php';

            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'cerita_id=' + ceritaId
            })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'ditambah') {
                        btn.classList.add(jenis === 'suka' ? 'aktif-suka' : 'aktif-simpan');
                        btn.style.fontVariationSettings = "'FILL' 1";
                        tampilToast(jenis === 'suka' ? '❤️ Ditambahkan ke Cerita Disukai' : '🔖 Disimpan ke Koleksi');
                    } else {
                        btn.classList.remove(jenis === 'suka' ? 'aktif-suka' : 'aktif-simpan');
                        btn.style.fontVariationSettings = "'FILL' 0";
                        tampilToast(jenis === 'suka' ? '💔 Dihapus dari Cerita Disukai' : '🗑️ Dihapus dari Koleksi');
                    }
                })
                .catch(() => tampilToast('Gagal. Coba lagi.'));
        }

        function bagikan() {
            if (navigator.share) {
                navigator.share({ title: document.title, url: window.location.href });
            } else {
                navigator.clipboard.writeText(window.location.href)
                    .then(() => tampilToast('🔗 Link disalin!'));
            }
        }
    </script>
</body>

</html>
<?php $conn->close(); ?>