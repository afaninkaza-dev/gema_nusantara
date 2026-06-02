<?php
session_start();
include 'koneksi.php';

$user_id   = $_SESSION['id'] ?? 0;
$logged_in = $user_id > 0;

// Ambil foto profil terbaru dari database
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

$pesan = '';

// Handle kirim saran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $logged_in) {
    $isi_saran = trim($_POST['deskripsi'] ?? '');

    if ( $isi_saran === '') {
        $pesan = ['tipe' => 'error', 'isi' => 'Isi saran tidak boleh kosong.'];
    } else {
        $stmt = $conn->prepare("INSERT INTO saran (user_id, isi_saran, waktu) VALUES (?, ?, NOW())");
        $stmt->bind_param("is", $user_id, $isi_saran);
        if ($stmt->execute()) {
            $pesan = ['tipe' => 'sukses', 'isi' => 'Saran berhasil dikirim! Terima kasih.'];
        } else {
            $pesan = ['tipe' => 'error', 'isi' => 'Gagal mengirim saran: ' . $stmt->error];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gema Nusantara - Saran</title>
    <link rel="icon" href="img/logoweb.svg">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" crossorigin="anonymous"/>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            margin:0; padding-top:70px;
            font-family:"Poppins",sans-serif;
            background-color:#FEFDF7; color:#000;
        }

        /* ── NAV ── */
        nav {
            background:#FEFDF7; font-family:"Inter",sans-serif;
            padding:15px 10px; display:flex; align-items:center;
            justify-content:space-between;
            box-shadow:0 2px 10px rgba(141,138,138,.15);
            position:fixed; top:0; left:0; right:0; width:100%;
            z-index:1000; box-sizing:border-box;
        }
        .logo { display:flex; align-items:center; gap:10px; cursor:pointer; }
        .logo img { width:40px; height:auto; }
        .logo p { font-size:13px; line-height:19px; font-weight:700; color:#6D4A37; margin:0; }
        .nav-menu { display:flex; align-items:center; gap:30px; margin-left:auto; margin-right:30px; }
        nav a { text-decoration:none; font-weight:500; color:#918d8a; font-size:16px; transition:all .3s; }
        nav a:hover, nav a.active { color:#000; font-weight:700; }
        nav .profile { width:35px; height:35px; border-radius:50%; cursor:pointer; transition:transform .2s; }
        nav .profile:hover { transform:scale(1.1); }
        .menu-icon { display:none; font-size:24px; color:#000; cursor:pointer; }

        /* ── Dropdown profil ── */
        .profile-dropdown { position:relative; display:inline-flex; align-items:center; }
        .profile-dropdown .dropdown-menu {
            display:none; position:absolute;
            top:calc(100% + 10px); right:0;
            background:#fff; border-radius:12px;
            box-shadow:0 8px 24px rgba(0,0,0,.15);
            min-width:180px; padding:8px 0; z-index:2000;
        }
        .profile-dropdown .dropdown-menu.open { display:block; }
        .dropdown-menu a {
            display:flex; align-items:center; gap:10px;
            padding:11px 18px; font-size:14px; font-weight:500;
            color:#333; text-decoration:none; transition:background .15s;
            font-family:'Poppins',sans-serif;
        }
        .dropdown-menu a:hover { background:#f5f0ed; color:#6D4A37; }
        .dropdown-menu a.keluar { color:#C0392B; }
        .dropdown-menu a.keluar:hover { background:#fdf0ee; }
        .dropdown-menu hr { border:none; border-top:1px solid #eee; margin:4px 0; }

        @media (max-width:900px) {
            .menu-icon { display:block; }
            .nav-menu { position:absolute; top:65px; right:0; background:#FEFDF7; flex-direction:column; width:200px; padding:15px 0; gap:20px; box-shadow:0 4px 10px rgba(0,0,0,.2); border-radius:8px; margin:0; display:none; }
            .nav-menu.active { display:flex; }
        }

        /* ── KONTEN ── */
        .main-wrapper {
            display:flex; justify-content:space-between; align-items:flex-start;
            max-width:1200px; margin:40px auto 60px; padding:0 4%; gap:50px;
        }
        .content-left { flex:1; }

        .saran_header { background:#FEFDF7; border-radius:20px; padding:40px; margin-bottom:30px; }
        .saran_header h1 { font-size:32px; font-weight:700; line-height:1.4; color:#000; }

        .alasan_saran h2 { margin-left:40px; font-size:22px; font-weight:600; margin-bottom:15px; color:#000; }
        .alasan_saran ul { margin-left:40px; list-style-type:disc; padding-left:1.5em; line-height:1.8; color:#333; font-size:15px; }
        .alasan_saran li { margin-bottom:.5em; }

        /* ── FORM ── */
        .saran_container {
            display:flex; flex-direction:column;
            background:#F5F5F5; width:450px;
            border-radius:12px; padding:2.5em 2em;
            box-shadow:0 4px 15px rgba(0,0,0,.05);
        }
        .saran_container .logo { margin-bottom:20px; }
        .saran_container label { font-weight:600; margin-bottom:.5em; margin-top:1.2em; color:#000; font-size:14px; display:block; }

        .saran_container input,
        .saran_container textarea {
            width:100%; padding:.8em 1em;
            border:1px solid #ddd; border-radius:6px;
            font-family:"Poppins",sans-serif; font-size:14px;
            box-sizing:border-box; background:#fff; transition:border-color .3s;
        }
        .saran_container input::placeholder,
        .saran_container textarea::placeholder { font-size:13px; color:#999; }
        .saran_container input:focus,
        .saran_container textarea:focus { outline:none; border-color:#6D4A37; }
        .saran_container textarea { resize:vertical; min-height:180px; }

        .kirim {
            width:100%; padding:.9em;
            background:#6D4A37; color:#fff;
            border:none; border-radius:6px;
            font-family:"Poppins",sans-serif; font-size:15px; font-weight:600;
            cursor:pointer; transition:all .3s; margin-top:2em;
        }
        .kirim:hover { background:#5a3a2a; transform:translateY(-2px); box-shadow:0 4px 12px rgba(109,74,55,.3); }
        .kirim:disabled { opacity:.6; cursor:default; transform:none; }

        /* Alert */
        .alert {
            padding:12px 16px; border-radius:8px;
            font-size:13.5px; margin-bottom:16px;
        }
        .alert.sukses { background:#e8f5e9; color:#2e7d32; border:1px solid #a5d6a7; }
        .alert.error  { background:#ffebee; color:#c62828; border:1px solid #ef9a9a; }

        /* Belum login notice */
        .login-notice {
            background:#fff3e0; border:1px solid #ffcc80;
            border-radius:8px; padding:14px 16px;
            font-size:13.5px; color:#e65100; margin-bottom:16px;
        }
        .login-notice a { color:#6D4A37; font-weight:600; }

        /* ── FOOTER ── */
        footer { background:#FFF5E7; font-family:"Inter",sans-serif; font-size:14px; margin-top:80px; }
        .footer_top { padding:4em 4%; display:flex; gap:50px; justify-content:space-between; max-width:1200px; margin:0 auto; }
        #tentang-web { max-width:450px; }
        #tentang-web p { margin-top:15px; line-height:1.6; text-align:justify; }
        #navigasi, #kontak { min-width:150px; }
        footer h3 { margin-bottom:15px; font-size:16px; color:#6D4A37; }
        footer ul { list-style:none; padding:0; line-height:2.2em; }
        footer a { text-decoration:none; color:#000; transition:color .2s; }
        footer a:hover { color:#6D4A37; }
        .footer_bottom { padding:1.5em 4%; text-align:center; max-width:1200px; margin:0 auto; }
        .footer_bottom hr { border:none; border-top:1px solid #dcd3c7; margin-bottom:15px; }

        @media (max-width:968px) {
            .main-wrapper { flex-direction:column; gap:40px; }
            .saran_container { width:100%; }
            .footer_top { flex-direction:column; gap:30px; }
        }
    </style>
</head>
<body>

<nav>
    <div class="logo" onclick="location.href='landingpage.php'">
        <img src="img/logoweb.svg" alt="Logo">
        <p>Gema<br>Nusantara</p>
    </div>
    <div class="menu-icon" id="menu-icon"><i class="ph ph-list"></i></div>
    <div class="nav-menu" id="nav-menu">
        <a href="landingpage.php">Beranda</a>
        <a href="jelajahi.php">Jelajahi</a>
        <a class="active" href="saran.php">Saran</a>
        <?php if ($logged_in): ?>
            <div class="profile-dropdown">
                <img class="profile" src="<?= htmlspecialchars($_nav_foto) ?>" alt="profil" id="profileBtn" onclick="toggleDropdown()">
                <div class="dropdown-menu" id="profileDropdown">
                    <a href="settingakun_baru.php">
                        <i class="fas fa-user" style="width:18px;text-align:center;"></i> Profil
                    </a>
                    <hr>
                    <a href="masuk.php" class="keluar">
                        <i class="fas fa-sign-out-alt" style="width:18px;text-align:center;"></i> Keluar
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
document.addEventListener('click', function(e) {
    const btn = document.getElementById('profileBtn');
    const dd  = document.getElementById('profileDropdown');
    if (dd && btn && !btn.contains(e.target) && !dd.contains(e.target)) {
        dd.classList.remove('open');
    }
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
            <img src="img/logoweb.svg" alt="Logo">
            <p>Gema<br>Nusantara</p>
        </div>

        <?php if ($pesan): ?>
            <div class="alert <?= $pesan['tipe'] ?>"><?= $pesan['isi'] ?></div>
        <?php endif; ?>

        <?php if (!$logged_in): ?>
            <div class="login-notice">
                Kamu harus <a href="masuk.php">masuk</a> terlebih dahulu untuk mengirim saran.
            </div>
        <?php endif; ?>

        <form method="POST" action="">

            <label for="deskripsi">Isi Saran</label>
            <textarea id="deskripsi" name="deskripsi"
                      placeholder="Tulis saranmu..."
                      <?= !$logged_in ? 'disabled' : '' ?>></textarea>

            <button class="kirim" type="submit" <?= !$logged_in ? 'disabled' : '' ?>>
                Kirim Saran
            </button>
        </form>
    </div>
</div>

<footer>
    <div class="footer_top">
        <div id="tentang-web">
            <div class="logo">
                <img src="img/logoweb.svg" alt="Logo">
                <p>Gema<br>Nusantara</p>
            </div>
            <p>Temukan kembali pesona dongeng masa kecil Anda di Gema Nusantara, rumah digital bagi cerita rakyat yang telah diwariskan turun-temurun, dikemas dalam format yang mudah dinikmati kapan saja.</p>
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

</body>
</html>
<?php $conn->close(); ?>