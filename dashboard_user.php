<?php
session_start();
include 'koneksi.php';

// Proteksi halaman - redirect jika belum login
if (!isset($_SESSION['id'])) {
    header("Location: masuk.php");
    exit;
}

$user_id = $_SESSION['id'];
$active_page = 'aktivitas';

// Ambil data user
$q_user = $conn->prepare("SELECT nama FROM user WHERE id = ?");
$q_user->bind_param("i", $user_id);
$q_user->execute();
$user = $q_user->get_result()->fetch_assoc();

// Hitung total sedang dibaca
$q_sedang = $conn->prepare("SELECT COUNT(*) AS total FROM riwayat_membaca WHERE user_id = ? AND status = 'sedang dibaca'");
$q_sedang->bind_param("i", $user_id);
$q_sedang->execute();
$total_sedang = $q_sedang->get_result()->fetch_assoc()['total'];

// Hitung total selesai dibaca
$q_selesai = $conn->prepare("SELECT COUNT(*) AS total FROM riwayat_membaca WHERE user_id = ? AND status = 'selesai'");
$q_selesai->bind_param("i", $user_id);
$q_selesai->execute();
$total_selesai = $q_selesai->get_result()->fetch_assoc()['total'];

// Riwayat membaca terbaru (4 cerita)
$q_riwayat = $conn->prepare("
    SELECT cr.id, cr.judul, cr.sampul, ROUND(AVG(u.rating),1) AS avg_rating
    FROM riwayat_membaca rm
    JOIN cerita_rakyat cr ON rm.cerita_id = cr.id
    LEFT JOIN ulasan u ON cr.id = u.cerita_id
    WHERE rm.user_id = ?
    GROUP BY cr.id, cr.judul, cr.sampul
    ORDER BY MAX(rm.waktu) DESC
    LIMIT 4
");
$q_riwayat->bind_param("i", $user_id);
$q_riwayat->execute();
$result_riwayat = $q_riwayat->get_result();

// Data grafik membaca per hari (7 hari terakhir)
$grafik_data = [];
$grafik_labels = [];
for ($i = 6; $i >= 0; $i--) {
    $tanggal = date('Y-m-d', strtotime("-$i days"));
    $label_hari = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'][date('w', strtotime($tanggal))];
    $grafik_labels[] = $label_hari;
    $q_hari = $conn->prepare("SELECT COUNT(*) AS total FROM riwayat_membaca WHERE user_id = ? AND DATE(waktu) = ?");
    $q_hari->bind_param("is", $user_id, $tanggal);
    $q_hari->execute();
    $grafik_data[] = $q_hari->get_result()->fetch_assoc()['total'];
}

// Lanjut baca (cerita terakhir dengan status sedang dibaca)
$q_lanjut = $conn->prepare("
    SELECT cr.id, cr.judul, cr.sampul, cr.asal_daerah, rm.waktu,
           ROUND(AVG(u.rating),1) AS avg_rating,
           (SELECT COUNT(*) FROM bab WHERE cerita_id = cr.id) AS total_bab,
           rm.bab_id
    FROM riwayat_membaca rm
    JOIN cerita_rakyat cr ON rm.cerita_id = cr.id
    LEFT JOIN ulasan u ON cr.id = u.cerita_id
    WHERE rm.user_id = ? AND rm.status = 'sedang dibaca'
    GROUP BY cr.id, cr.judul, cr.sampul, cr.asal_daerah, rm.waktu, rm.bab_id
    ORDER BY rm.waktu DESC
    LIMIT 1
");
$q_lanjut->bind_param("i", $user_id);
$q_lanjut->execute();
$lanjut = $q_lanjut->get_result()->fetch_assoc();

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
    <title>Gema Nusantara - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-datalabels/2.2.0/chartjs-plugin-datalabels.min.js"></script>
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

        .container {
            display: flex;
            min-height: 100vh;
        }

        .content {
            flex: 1;
            padding: 36px;
            overflow-y: auto;
        }

        .dashboard {
            display: flex;
            gap: 24px;
            align-items: flex-start;
        }

        .dashboard_kiri {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .rangkum_baca {
            display: flex;
            gap: 20px;
            margin-bottom: 24px;
        }

        .isi_rangkum {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            padding: 24px 20px;
            background-color: #fff;
            border: 2px solid #e8e0d8;
            border-radius: 10px;
        }

        .isi_rangkum h2 {
            font-size: 14px;
            font-weight: 500;
            color: #555;
            text-align: center;
        }

        .rangkum_detail {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .rangkum_detail img {
            width: 46px;
            height: 46px;
        }

        .rangkum_detail h3 {
            font-size: 42px;
            font-weight: 600;
            margin: 0;
            color: #6D4A37;
        }

        .riwayat_header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .riwayat_header h3 {
            font-size: 16px;
            font-weight: 600;
        }

        .riwayat_header a {
            font-size: 13px;
            color: #6D4A37;
            text-decoration: none;
        }

        .cerita_container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
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

        .cerita_wrapper .sampul {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 7px;
            background-color: #8a6050;
            display: block;
        }

        .cerita_wrapper h2 {
            font-size: 12px;
            font-weight: 600;
            line-height: 1.4;
            min-height: 32px;
            display: flex;
            align-items: center;
            margin: 0;
            color: #F7F4E9;
        }

        .cerita_wrapper hr {
            border: none;
            border-top: 1px solid rgba(247, 244, 233, 0.4);
        }

        .rating {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .rating .star {
            color: #FFD700;
            font-size: 12px;
        }

        .rating-number {
            font-size: 12px;
            font-weight: 500;
            flex: 1;
        }

        .icon-small {
            font-size: 14px;
            color: #F7F4E9;
            cursor: pointer;
            transition: transform 0.2s ease, color 0.2s ease;
        }

        .icon-small:hover {
            transform: scale(1.2);
        }

        .icon-small.liked {
            color: #E74C3C;
        }

        .icon-small.saved {
            color: #FFD700;
        }

        .dashboard_kanan {
            width: 280px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .jumlah_membaca {
            background: #fff;
            border: 2px solid #e8e0d8;
            border-radius: 10px;
            padding: 10px;
            height: 280px;
        }

        .lanjut_baca {
            display: flex;
            flex-direction: column;
            padding: 10px 12px 14px;
            background-color: #fff;
            border: 2px solid #e8e0d8;
            border-radius: 10px;
        }

        .lanjut_baca h2 {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 14px;
        }

        .isi_baca {
            display: flex;
            gap: 14px;
        }

        .isi_baca>img {
            width: 100px;
            border-radius: 6px;
            object-fit: cover;
            height: 140px;
        }

        .baca_detail {
            display: flex;
            flex-direction: column;
        }

        .baca_detail h3 {
            margin: 0 0 6px;
            font-size: 14px;
            font-weight: 600;
        }

        .isi_detail {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 4px;
        }

        .isi_detail p {
            font-size: 10px;
            margin: 0;
        }

        .isi_detail img {
            width: 14px;
            height: 14px;
        }

        #star {
            color: #FFD700;
            font-size: 12px;
            margin: 0;
        }

        .baca_actions {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }

        .btn-baca {
            background: #6D4A37;
            color: #fff;
            border: none;
            border-radius: 20px;
            padding: 7px 16px;
            font-size: 11px;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            font-weight: 500;
        }

        .btn-baca:hover {
            background: #5a3d2d;
        }

        .action-icons {
            display: flex;
            gap: 8px;
        }

        .action-icons i {
            font-size: 14px;
            color: #555;
            cursor: pointer;
            transition: color 0.2s;
        }

        .action-icons i:hover {
            color: #6D4A37;
        }

        .kosong {
            text-align: center;
            color: #999;
            font-size: 13px;
            padding: 30px 0;
            grid-column: span 4;
        }

        @media (max-width: 700px) {
            .content {
                padding: 20px 16px;
            }

            .rangkum_baca {
                flex-direction: column;
            }

            .cerita_container {
                grid-template-columns: repeat(2, 1fr);
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
                <a href="dashboard_user.php" class="active"><i class="fas fa-tasks"></i> <span>Aktivitas Saya</span></a>
                <a href="dashboardsimpan_user.php"><i class="fas fa-bookmark"></i> <span>Cerita Tersimpan</span></a>
                <a href="dashboardsuka_user.php"><i class="fas fa-heart"></i> <span>Cerita Disukai</span></a>
                <a href="dashboardulasan_user.php"><i class="fas fa-comment-alt"></i> <span>Riwayat Ulasan</span></a>
                <a href="dashboardhistory_user.php"><i class="fas fa-history"></i> <span>Riwayat Membaca</span></a>
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
            <section class="dashboard">
                <div class="dashboard_kiri">
                    <div class="rangkum_baca">
                        <div class="isi_rangkum">
                            <h2>Total Cerita Yang Sedang Dibaca</h2>
                            <div class="rangkum_detail">
                                <img src="img/book.svg" alt="Buku">
                                <h3><?= $total_sedang ?></h3>
                            </div>
                        </div>
                        <div class="isi_rangkum">
                            <h2>Total Cerita Yang Selesai Dibaca</h2>
                            <div class="rangkum_detail">
                                <img src="img/book.svg" alt="Buku">
                                <h3><?= $total_selesai ?></h3>
                            </div>
                        </div>
                    </div>

                    <div class="riwayat">
                        <div class="riwayat_header">
                            <h3>Riwayat Membacamu</h3>
                            <a href="dashboardhistory_user.php">Lihat Semua &gt;</a>
                        </div>
                        <div class="cerita_container">
                            <?php if ($result_riwayat && $result_riwayat->num_rows > 0): ?>
                                <?php while ($cr = $result_riwayat->fetch_assoc()): ?>
                                    <a class="cerita_wrapper" href="detailbuku.php?id=<?= $cr['id'] ?>">
                                        <img class="sampul" src="buku/<?= htmlspecialchars($cr['sampul']) ?>"
                                            alt="<?= htmlspecialchars($cr['judul']) ?>">
                                        <h2><?= htmlspecialchars($cr['judul']) ?></h2>
                                        <hr>
                                        <div class="rating">
                                            <i class="fas fa-star star"></i>
                                            <span class="rating-number"><?= $cr['avg_rating'] ?? '-' ?></span>
                                            <i class="fas fa-heart icon-small btn-like" title="Suka"></i>
                                            <i class="fas fa-bookmark icon-small btn-save" title="Simpan"></i>
                                        </div>
                                    </a>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="kosong">Belum ada riwayat membaca.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="dashboard_kanan">
                    <div class="jumlah_membaca">
                        <canvas id="grafikMembaca"></canvas>
                    </div>
                    <?php if ($lanjut): ?>
                        <div class="lanjut_baca">
                            <h2>Lanjut Membaca</h2>
                            <div class="isi_baca">
                                <img src="buku/<?= htmlspecialchars($lanjut['sampul']) ?>"
                                    alt="<?= htmlspecialchars($lanjut['judul']) ?>">
                                <div class="baca_detail">
                                    <h3><?= htmlspecialchars($lanjut['judul']) ?></h3>
                                    <div class="isi_detail">
                                        <img src="img/waktu.svg" alt="Waktu">
                                        <p>Terakhir dibaca: <?= date('d-m-Y', strtotime($lanjut['waktu'])) ?></p>
                                    </div>
                                    <div class="isi_detail">
                                        <p><?= $lanjut['avg_rating'] ?? '-' ?></p>
                                        <p id="star">★★★★★</p>
                                    </div>
                                    <div class="baca_actions">
                                        <a href="detailbuku.php?id=<?= $lanjut['id'] ?>" class="btn-baca">Baca</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Chart.register(ChartDataLabels);
            const ctx = document.getElementById('grafikMembaca').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($grafik_labels) ?>,
                    datasets: [{
                        label: 'Buku dibaca',
                        data: <?= json_encode($grafik_data) ?>,
                        backgroundColor: 'rgba(109,74,54,0.85)',
                        borderColor: 'rgba(109,74,54,0.85)',
                        borderWidth: 1,
                        borderRadius: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: { display: true, text: 'Jumlah Membaca Perhari', font: { size: 13, weight: '600', family: 'Poppins' }, color: '#222', padding: { bottom: 10 } },
                        legend: { display: false },
                        datalabels: { display: false }
                    },
                    scales: {
                        y: { min: 0, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        });
    </script>
</body>

</html>
<?php $conn->close(); ?>