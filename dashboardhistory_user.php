<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['id'])) {
    header("Location: masuk.php");
    exit;
}

$user_id = $_SESSION['id'];
$active_page = 'history';
$q = $conn->prepare("
    SELECT cr.id, cr.judul, cr.sampul,
           ROUND(AVG(u.rating),1) AS avg_rating,
           DATE(rm.waktu) AS tgl_baca
    FROM riwayat_membaca rm
    JOIN cerita_rakyat cr ON rm.cerita_id = cr.id
    LEFT JOIN ulasan u ON cr.id = u.cerita_id
    WHERE rm.user_id = ?
    GROUP BY cr.id, cr.judul, cr.sampul, DATE(rm.waktu)
    ORDER BY tgl_baca DESC, rm.waktu DESC
");
$q->bind_param("i", $user_id);
$q->execute();
$result = $q->get_result();

// Kelompokkan per tanggal
$grouped = [];
while ($row = $result->fetch_assoc()) {
    $tgl = $row['tgl_baca'];
    $label = ($tgl === date('Y-m-d')) ? 'Hari Ini' : date('l, d/m/Y', strtotime($tgl));
    $grouped[$label][] = $row;
}

// Handle upload foto sidebar — harus di atas sebelum HTML dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'foto_sidebar') {
    // Ambil data user untuk keperluan hapus foto lama
    $_sbq = $conn->prepare("SELECT foto FROM user WHERE id = ?");
    $_sbq->bind_param("i", $user_id);
    $_sbq->execute();
    $_sb_old = $_sbq->get_result()->fetch_assoc();

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $ftype = mime_content_type($_FILES['foto']['tmp_name']);
        if (in_array($ftype, $allowed) && $_FILES['foto']['size'] <= 2 * 1024 * 1024) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $filename = 'foto_' . $user_id . '_' . time() . '.' . $ext;
            $target = 'img/profil/' . $filename;
            if (!is_dir('img/profil'))
                mkdir('img/profil', 0755, true);
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target)) {
                if (!empty($_sb_old['foto']) && $_sb_old['foto'] !== 'img/profile.jpg' && file_exists($_sb_old['foto']))
                    unlink($_sb_old['foto']);
                $upd = $conn->prepare("UPDATE user SET foto=? WHERE id=?");
                $upd->bind_param("si", $target, $user_id);
                $upd->execute();
            }
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="img/logoweb.svg">
    <title>Gema Nusantara - Riwayat Membaca</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #F7F7F7;
            color: #333;
            min-height: 100vh;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .content {
            flex: 1;
            padding: 36px;
            overflow-y: auto;
        }

        .content h1 {
            font-size: 22px;
            font-weight: 500;
            color: #333;
            margin-bottom: 28px;
            text-align: center;
        }

        .section_label {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
        }

        .section_label span {
            font-size: 15px;
            font-weight: 600;
            color: #6D4A37;
            white-space: nowrap;
        }

        .section_label::after {
            content: '';
            flex: 1;
            height: 2px;
            background-color: #6D4A36;
            border-radius: 2px;
        }

        .history_container {
            display: flex;
            flex-direction: column;
            gap: 40px;
            padding-bottom: 60px;
        }

        .cerita_container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .cerita_wrapper {
            background-color: #6D4A36;
            color: #F7F4E9;
            border-radius: 10px;
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            text-decoration: none;
        }

        .cerita_wrapper:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(109, 74, 54, 0.3);
        }

        .sampul {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            background-color: #8a6050;
            display: block;
        }

        .cerita_wrapper h2 {
            font-size: 13px;
            font-weight: 500;
            color: #F7F4E9;
            line-height: 1.4;
            min-height: 36px;
            display: flex;
            align-items: center;
            margin: 0;
        }

        .cerita_wrapper hr {
            border: none;
            border-top: 1px solid rgba(247, 244, 233, 0.4);
            margin: 0;
        }

        .rating {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .star {
            color: #FFD700;
            font-size: 13px;
        }

        .rating-number {
            font-size: 12px;
            font-weight: 500;
            flex: 1;
        }

        .icon-small {
            font-size: 15px;
            color: #F7F4E9;
            cursor: pointer;
            transition: transform 0.2s ease, color 0.2s ease;
        }

        .icon-small:hover {
            transform: scale(1.25);
        }

        .kosong {
            text-align: center;
            color: #999;
            font-size: 14px;
            padding: 60px 0;
        }

        @media (max-width: 700px) {
            .content {
                padding: 20px 16px;
            }

            .cerita_container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* ── SIDEBAR ── */
        .sidebar {
            width: 220px;
            background-color: #fff;
            padding: 24px 16px 20px;
            box-shadow: 2px 0 8px rgba(0, 0, 0, .06);
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            flex-shrink: 0;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .sb-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 28px;
            text-decoration: none;
        }

        .sb-logo img {
            width: 36px;
        }

        .sb-logo-text {
            font-size: 15px;
            font-weight: 600;
            color: #6D4A37;
            line-height: 1.25;
        }

        .sb-profile-form {
            align-self: center;
            margin-bottom: 28px;
        }

        .sb-profile-wrap {
            position: relative;
            width: 100px;
            height: 100px;
            flex-shrink: 0;
        }

        .sb-profile-wrap img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
        }

        .sb-overlay {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .2s;
        }

        .sb-profile-wrap:hover .sb-overlay {
            background: rgba(0, 0, 0, 0.35);
        }

        .sb-overlay i {
            color: #fff;
            font-size: 20px;
            opacity: 0;
            transition: opacity .2s;
        }

        .sb-profile-wrap:hover .sb-overlay i {
            opacity: 1;
        }

        .sb-upload-loading {
            display: none;
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }

        .sb-upload-loading.show {
            display: flex;
        }

        .sb-upload-loading i {
            color: #fff;
            font-size: 22px;
            animation: sb-spin 1s linear infinite;
        }

        @keyframes sb-spin {
            to {
                transform: rotate(360deg);
            }
        }

        .sidebar nav {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            text-decoration: none;
            color: #444;
            font-size: 13.5px;
            font-weight: 400;
            border-radius: 8px;
            transition: background .2s, color .2s;
        }

        .sidebar a i {
            width: 18px;
            text-align: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .sidebar a:hover {
            background: #f5f0ed;
            color: #6D4A37;
        }

        .sidebar a.active {
            background: #6D4A36;
            color: #fff;
            font-weight: 500;
        }

        .sidebar .logout-btn {
            margin-top: auto;
            color: #C0392B !important;
            font-weight: 500 !important;
        }

        .sidebar .logout-btn:hover {
            background: #fdf0ee !important;
        }

        @media (max-width: 700px) {
            .sidebar {
                width: 60px;
                padding: 16px 8px;
            }

            .sb-logo-text,
            .sidebar a span {
                display: none;
            }

            .sidebar a {
                padding: 10px;
                justify-content: center;
                gap: 0;
            }

            .sidebar a i {
                width: auto;
                font-size: 18px;
            }

            .sb-profile-wrap,
            .sb-profile-wrap img {
                width: 40px;
                height: 40px;
            }

        }
    </style>
</head>

<body>
    <div class="container">
        <?php
        // ── Sidebar inline ──
        $_sb = $conn->prepare("SELECT nama, foto FROM user WHERE id = ?");
        $_sb->bind_param("i", $user_id);
        $_sb->execute();
        $_sb_user = $_sb->get_result()->fetch_assoc();
        $_foto_src = (!empty($_sb_user['foto']) && file_exists($_sb_user['foto']))
            ? $_sb_user['foto'] : 'img/profile.jpg';

        // Handle upload foto dari sidebar
        
        ?>
        <div class="sidebar">
            <a href="landingpage.php" class="sb-logo">
                <img src="img/logoweb.svg" alt="logo">
                <span class="sb-logo-text">Gema<br>Nusantara</span>
            </a>
            <form id="sbFormFoto" class="sb-profile-form" method="POST" enctype="multipart/form-data" action="">
                <input type="hidden" name="aksi" value="foto_sidebar">
                <div class="sb-profile-wrap">
                    <img src="<?= htmlspecialchars($_foto_src) ?>" alt="Foto Profil" id="sbPreviewFoto">
                    <label for="sbInputFoto" class="sb-overlay" title="Ganti foto"><i class="fas fa-camera"></i></label>

                    <div class="sb-upload-loading" id="sbUploadLoading"><i class="fas fa-spinner"></i></div>
                    <input type="file" id="sbInputFoto" name="foto" accept="image/jpeg,image/png,image/webp"
                        style="display:none;">
                </div>
            </form>
            <nav>
                <a href="settingakun_baru.php"><i class="fas fa-user-cog"></i> <span>Profil</span></a>
                <a href="dashboard_user.php"><i class="fas fa-tasks"></i> <span>Aktivitas Saya</span></a>
                <a href="dashboardsimpan_user.php"><i class="fas fa-bookmark"></i> <span>Cerita Tersimpan</span></a>
                <a href="dashboardsuka_user.php"><i class="fas fa-heart"></i> <span>Cerita Disukai</span></a>
                <a href="dashboardulasan_user.php"><i class="fas fa-comment-alt"></i> <span>Riwayat Ulasan</span></a>
                <a href="dashboardhistory_user.php" class="active"><i class="fas fa-history"></i> <span>Riwayat
                        Membaca</span></a>
                <a href="keluar.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> <span>Keluar</span></a>
            </nav>
        </div>
        <script>
            (function () {
                var input = document.getElementById('sbInputFoto');
                var form = document.getElementById('sbFormFoto');
                var preview = document.getElementById('sbPreviewFoto');
                var loading = document.getElementById('sbUploadLoading');
                if (!input) return;
                input.addEventListener('change', function () {
                    var file = this.files[0];
                    if (!file) return;
                    if (file.size > 2 * 1024 * 1024) { alert('Ukuran foto maksimal 2MB.'); this.value = ''; return; }
                    var reader = new FileReader();
                    reader.onload = function (e) { preview.src = e.target.result; loading.classList.add('show'); form.submit(); };
                    reader.readAsDataURL(file);
                });
            })();
        </script>


        <div class="content">
            <h1>Riwayat Bacamu</h1>
            <?php if (empty($grouped)): ?>
                <p class="kosong">Belum ada riwayat membaca.</p>
            <?php else: ?>
                <div class="history_container">
                    <?php foreach ($grouped as $label => $cerita_list): ?>
                        <div>
                            <div class="section_label"><span><?= htmlspecialchars($label) ?></span></div>
                            <div class="cerita_container">
                                <?php foreach ($cerita_list as $cr): ?>
                                    <a class="cerita_wrapper" href="detailbuku.php?id=<?= $cr['id'] ?>">
                                        <img class="sampul" src="buku/<?= htmlspecialchars($cr['sampul']) ?>"
                                            alt="<?= htmlspecialchars($cr['judul']) ?>">
                                        <h2><?= htmlspecialchars($cr['judul']) ?></h2>
                                        <hr>
                                        <div class="rating">
                                            <i class="fas fa-star star"></i>
                                            <span class="rating-number"><?= $cr['avg_rating'] ?? '-' ?></span>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
<?php $conn->close(); ?>