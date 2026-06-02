<?php
session_start();
include "koneksi.php";

$user_id = $_SESSION['id'] ?? 0;
$logged_in = $user_id > 0;

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

// Ambil semua bab untuk navigasi prev/next (SEKALI SAJA)
$sql_all = "SELECT id, judul_bab FROM bab WHERE cerita_id = ? ORDER BY id ASC";
$stmt_all = $conn->prepare($sql_all);
$stmt_all->bind_param("i", $cerita_id);
$stmt_all->execute();
$semua_bab = $stmt_all->get_result()->fetch_all(MYSQLI_ASSOC);

$posisi = null;
foreach ($semua_bab as $i => $b) {
    if ($b['id'] == $bab_id) {
        $posisi = $i;
        break;
    }
}
$prev_bab = $posisi > 0 ? $semua_bab[$posisi - 1] : null;
$next_bab = $posisi < count($semua_bab) - 1 ? $semua_bab[$posisi + 1] : null;

if ($logged_in) {
    $status = ($next_bab === null) ? 'selesai' : 'sedang dibaca';

    // Cek apakah sudah ada record untuk user+cerita ini
    $cek = $conn->prepare(
        "SELECT id, status FROM riwayat_membaca WHERE user_id = ? AND cerita_id = ?"
    );
    $cek->bind_param("ii", $user_id, $cerita_id);
    $cek->execute();
    $existing = $cek->get_result()->fetch_assoc();

    if (!$existing) {
        // Belum ada sama sekali → INSERT
        $ins = $conn->prepare(
            "INSERT INTO riwayat_membaca (user_id, cerita_id, bab_id, waktu, status) 
             VALUES (?, ?, ?, NOW(), ?)"
        );
        $ins->bind_param("iiis", $user_id, $cerita_id, $bab_id, $status);
        $ins->execute();
    } else {
        $new_status = ($existing['status'] === 'selesai' && $status === 'sedang dibaca')
            ? 'selesai'
            : $status;

        $upd = $conn->prepare(
            "UPDATE riwayat_membaca 
             SET bab_id = ?, waktu = NOW(), status = ? 
             WHERE user_id = ? AND cerita_id = ?"
        );
        $upd->bind_param("isii", $bab_id, $new_status, $user_id, $cerita_id);
        $upd->execute();
    }
}

// Ambil ulasan dengan pagination
$per_page = 3;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

$sql_ulasan = "SELECT u.*, usr.nama, usr.foto 
               FROM ulasan u 
               JOIN user usr ON u.user_id = usr.id 
               WHERE u.cerita_id = ? 
               ORDER BY u.waktu DESC 
               LIMIT ? OFFSET ?";
$stmt_ul = $conn->prepare($sql_ulasan);
$stmt_ul->bind_param("iii", $cerita_id, $per_page, $offset);
$stmt_ul->execute();
$ulasan_list = $stmt_ul->get_result()->fetch_all(MYSQLI_ASSOC);

$sql_count = "SELECT COUNT(*) as total FROM ulasan WHERE cerita_id = ?";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param("i", $cerita_id);
$stmt_count->execute();
$total_ulasan = $stmt_count->get_result()->fetch_assoc()['total'];
$total_page = max(1, ceil($total_ulasan / $per_page));

// Ambil foto profil navbar
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
    <link rel="icon" href="img/logoweb.svg">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        crossorigin="anonymous" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@400;500;600&display=swap');

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
            cursor: pointer;
            transition: transform .2s;
        }

        nav .profile:hover {
            transform: scale(1.1);
        }

        /* ── Dropdown profil ── */
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

        .sampul {
            width: 300px;
            height: auto;
        }

        .cerita_wrapper {
            background-color: #fff;
            box-shadow: 0 0 10px rgba(141, 138, 138, 0.3);
            border-radius: 8px;
            padding: 120px 60px 40px;
            position: relative;
            z-index: 1;
        }

        .isi_cerita {
            margin-bottom: 30px;
        }

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

        .isi_teks p,
        .isi_cerita p {
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

        .button p {
            margin: 0;
        }

        .button:hover {
            background-color: #5a3a2a;
            transform: scale(1.05);
        }

        .ulasan_header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }

        .ulasan_header .coklat {
            width: 5px;
            height: 30px;
            background-color: #6D4A37;
            margin-right: 15px;
            border-radius: 2px;
        }

        .ulasan_header h2 {
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }

        .ulasan_container {
            display: flex;
            flex-direction: column;
            gap: 25px;
            margin-bottom: 30px;
        }

        .ulasan {
            border-bottom: 2px solid #ddd;
            padding-bottom: 20px;
        }

        .ulasan_top {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 5px;
        }

        .ulasan_top .profile {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
        }

        .ulasan_info {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .ulasan_info h3 {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
        }

        .rating {
            color: #FFD700;
            font-size: 20px;
        }

        .isi_ulasan p {
            text-align: justify;
            line-height: 1.6;
            font-size: 14px;
            margin: 0 0 5px;
            color: #333;
        }

        .isi_ulasan .tanggal {
            font-size: 12px;
            color: #999;
            margin-top: 10px;
        }

        .ulasan_bottom {
            display: flex;
            flex-direction: column;
        }

        .pagination {
            margin: 10px 0 20px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .pagination a {
            color: black;
            padding: 8px 16px;
            text-decoration: none;
            transition: background-color .3s;
            border-radius: 4px;
        }

        .pagination a.active {
            background-color: #6D4A37;
            color: white;
        }

        .pagination a:hover:not(.active):not(.disabled) {
            background-color: #ddd;
        }

        .pagination a.disabled {
            color: #ccc;
            pointer-events: none;
        }

        .ulasan_button {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }

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

        .ulasan_button button:hover {
            background-color: #5a3a2a;
            transform: scale(1.05);
        }

        /* Modal Ulasan */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 3000;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.open {
            display: flex;
        }

        .modal-box {
            background: #fff;
            border-radius: 16px;
            padding: 36px 40px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .modal-box h3 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #1e293b;
        }

        .modal-close {
            position: absolute;
            top: 16px;
            right: 20px;
            background: none;
            border: none;
            font-size: 22px;
            cursor: pointer;
            color: #94a3b8;
        }

        .modal-close:hover {
            color: #000;
        }

        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 4px;
            margin-bottom: 18px;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            font-size: 32px;
            color: #ddd;
            cursor: pointer;
            transition: color .15s;
        }

        .star-rating input:checked~label,
        .star-rating label:hover,
        .star-rating label:hover~label {
            color: #FFD700;
        }

        .modal-textarea {
            width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 14px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            resize: vertical;
            min-height: 110px;
            outline: none;
            transition: border-color .2s;
        }

        .modal-textarea:focus {
            border-color: #6D4A37;
        }

        .modal-submit {
            margin-top: 18px;
            width: 100%;
            padding: 12px;
            background: #6D4A37;
            color: #fff;
            border: none;
            border-radius: 100px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Poppins';
            cursor: pointer;
            transition: background .2s;
        }

        .modal-submit:hover {
            background: #5a3a2a;
        }

        .modal-msg {
            font-size: 13px;
            margin-top: 10px;
            text-align: center;
        }

        .modal-msg.error {
            color: #ef4444;
        }

        .modal-msg.success {
            color: #22c55e;
        }

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

        @media (max-width: 900px) {
            #cerita {
                padding: 20px 20px;
            }

            .cerita_wrapper {
                padding: 120px 20px 30px;
            }

            .menu-icon {
                display: block;
            }

            .nav-menu {
                display: none;
                position: absolute;
                top: 60px;
                right: 0;
                background: #FEFDF7;
                flex-direction: column;
                width: 200px;
                padding: 15px 0;
                gap: 20px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            }

            .nav-menu.active {
                display: flex;
            }

            .modal-box {
                margin: 0 16px;
                padding: 28px 22px;
            }
        }
    </style>
    <title><?= htmlspecialchars($bab['judul_bab']) ?> — <?= htmlspecialchars($bab['judul_cerita']) ?></title>
</head>

<body>
    <header>
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
                        <img class="profile" src="<?= htmlspecialchars($_nav_foto) ?>" alt="profil" id="profileBtn"
                            onclick="toggleDropdown()">
                        <div class="dropdown-menu" id="profileDropdown">
                            <a href="settingakun_baru.php">
                                <i style="font-size:16px;width:18px;text-align:center;" class="fas fa-user"></i> Profil
                            </a>
                            <hr>
                            <a href="masuk.php" class="keluar">
                                <i style="font-size:16px;width:18px;text-align:center;" class="fas fa-sign-out-alt"></i>
                                Keluar
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="masuk.php">Masuk</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <section id="cerita">
        <div class="sampul_container">
            <img class="sampul" src="buku/<?= htmlspecialchars($bab['sampul']) ?>"
                alt="Sampul <?= htmlspecialchars($bab['judul_cerita']) ?>">
        </div>

        <div class="cerita_wrapper">
            <div class="isi_cerita">
                <h1><?= htmlspecialchars($bab['judul_cerita']) ?></h1>
                <h2><?= htmlspecialchars($bab['judul_bab']) ?></h2>
                <div class="isi_teks">
                    <?php
                    $isi = $bab['isi'];
                    // Kalau plain text (tidak ada tag HTML), wrap tiap baris jadi <p>
                    if (strip_tags($isi) === $isi) {
                        $paragraf = array_filter(array_map('trim', explode("\n", $isi)));
                        echo '<p>' . implode('</p><p>', $paragraf) . '</p>';
                    } else {
                        // Sudah HTML — tampilkan langsung
                        echo $isi;
                    }
                    ?>
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
                                    <?php
                                    $foto_ulasan = (!empty($u['foto']) && file_exists($u['foto']))
                                        ? $u['foto'] : 'img/profile.jpg';
                                    ?>
                                    <img class="profile" src="<?= htmlspecialchars($foto_ulasan) ?>"
                                        alt="<?= htmlspecialchars($u['nama']) ?>">
                                    <div class="ulasan_info">
                                        <h3><?= htmlspecialchars($u['nama']) ?></h3>
                                        <div class="rating"><?= bintang($u['rating']) ?></div>
                                    </div>
                                </div>
                                <div class="isi_ulasan">
                                    <!-- FIX: pakai isi_ulasan & waktu -->
                                    <p><?= htmlspecialchars($u['isi_ulasan']) ?></p>
                                    <p class="tanggal"><?= date('d-m-Y', strtotime($u['waktu'])) ?></p>
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
                <?php if ($total_page > 1): ?>
                    <div class="pagination">
                        <a href="?bab=<?= $bab_id ?>&page=<?= max(1, $page - 1) ?>"
                            class="<?= $page <= 1 ? 'disabled' : '' ?>">&laquo;</a>
                        <?php for ($p = 1; $p <= $total_page; $p++): ?>
                            <a href="?bab=<?= $bab_id ?>&page=<?= $p ?>"
                                class="<?= $p == $page ? 'active' : '' ?>"><?= $p ?></a>
                        <?php endfor; ?>
                        <a href="?bab=<?= $bab_id ?>&page=<?= min($total_page, $page + 1) ?>"
                            class="<?= $page >= $total_page ? 'disabled' : '' ?>">&raquo;</a>
                    </div>
                <?php endif; ?>

                <div class="ulasan_button">
                    <?php if ($logged_in): ?>
                        <button id="open_ulasan">Beri Ulasan</button>
                    <?php else: ?>
                        <button onclick="location.href='masuk.php'">Masuk untuk Memberi Ulasan</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- MODAL ULASAN -->
    <?php if ($logged_in): ?>
        <div class="modal-overlay" id="modalUlasan">
            <div class="modal-box">
                <button class="modal-close" id="close_ulasan">&times;</button>
                <h3>Beri Ulasan</h3>
                <div class="star-rating">
                    <input type="radio" id="s5" name="rating" value="5"><label for="s5">★</label>
                    <input type="radio" id="s4" name="rating" value="4"><label for="s4">★</label>
                    <input type="radio" id="s3" name="rating" value="3"><label for="s3">★</label>
                    <input type="radio" id="s2" name="rating" value="2"><label for="s2">★</label>
                    <input type="radio" id="s1" name="rating" value="1"><label for="s1">★</label>
                </div>
                <textarea class="modal-textarea" id="isi_ulasan" placeholder="Tulis ulasan kamu di sini..."></textarea>
                <button class="modal-submit" id="submit_ulasan">Kirim Ulasan</button>
                <p class="modal-msg" id="modal_msg"></p>
            </div>
        </div>
    <?php endif; ?>

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
        // Navbar mobile
        document.getElementById('menu-icon').addEventListener('click', () => {
            document.getElementById('nav-menu').classList.toggle('active');
        });

        // Dropdown profil
        function toggleDropdown() {
            document.getElementById('profileDropdown').classList.toggle('open');
        }
        document.addEventListener('click', function (e) {
            const btn = document.getElementById('profileBtn');
            const dd = document.getElementById('profileDropdown');
            if (dd && btn && !btn.contains(e.target) && !dd.contains(e.target)) {
                dd.classList.remove('open');
            }
        });

        <?php if ($logged_in): ?>
            // Modal ulasan
            const modal = document.getElementById('modalUlasan');
            const btnOpen = document.getElementById('open_ulasan');
            const btnClose = document.getElementById('close_ulasan');
            const msgEl = document.getElementById('modal_msg');

            btnOpen.addEventListener('click', () => modal.classList.add('open'));
            btnClose.addEventListener('click', () => modal.classList.remove('open'));
            modal.addEventListener('click', (e) => { if (e.target === modal) modal.classList.remove('open'); });

            document.getElementById('submit_ulasan').addEventListener('click', async () => {
                const rating = document.querySelector('input[name="rating"]:checked');
                const isi = document.getElementById('isi_ulasan').value.trim();
                msgEl.textContent = '';
                msgEl.className = 'modal-msg';

                if (!rating) { msgEl.textContent = 'Pilih rating terlebih dahulu.'; msgEl.classList.add('error'); return; }
                if (!isi) { msgEl.textContent = 'Tulis ulasan terlebih dahulu.'; msgEl.classList.add('error'); return; }

                const fd = new FormData();
                fd.append('cerita_id', <?= $cerita_id ?>);
                fd.append('rating', rating.value);
                fd.append('isi_ulasan', isi);

                try {
                    const res = await fetch('simpan_ulasan.php', { method: 'POST', body: fd });
                    const data = await res.json();
                    if (data.success) {
                        msgEl.textContent = 'Ulasan berhasil dikirim!';
                        msgEl.classList.add('success');
                        setTimeout(() => location.reload(), 1200);
                    } else {
                        msgEl.textContent = data.message || 'Gagal mengirim ulasan.';
                        msgEl.classList.add('error');
                    }
                } catch {
                    msgEl.textContent = 'Terjadi kesalahan. Coba lagi.';
                    msgEl.classList.add('error');
                }
            });
        <?php endif; ?>
    </script>
</body>

</html>
<?php $conn->close(); ?>