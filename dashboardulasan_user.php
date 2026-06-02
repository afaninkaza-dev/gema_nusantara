<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['id'])) {
    header("Location: masuk.php");
    exit;
}

$user_id = $_SESSION['id'];
$active_page = 'ulasan';

// Hapus ulasan
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $ulasan_id = (int) $_GET['hapus'];
    $q_hapus = $conn->prepare("DELETE FROM ulasan WHERE id = ? AND user_id = ?");
    $q_hapus->bind_param("ii", $ulasan_id, $user_id);
    $q_hapus->execute();
    header("Location: dashboardulasan_user.php");
    exit;
}

// Ambil semua ulasan user
$q = $conn->prepare("
    SELECT u.id, u.isi_ulasan, u.rating, u.waktu,
           cr.id AS cerita_id, cr.judul
    FROM ulasan u
    JOIN cerita_rakyat cr ON u.cerita_id = cr.id
    WHERE u.user_id = ?
    ORDER BY u.waktu DESC
");
$q->bind_param("i", $user_id);
$q->execute();
$result = $q->get_result();

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
    <title>Gema Nusantara - Riwayat Ulasan</title>
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

        .content h1 {
            font-size: 22px;
            font-weight: 500;
            color: #333;
            margin-bottom: 28px;
            text-align: center;
        }

        .ulasan_container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            max-width: 800px;
            margin: 0 auto;
        }

        .ulasan {
            background-color: #fff;
            border: 1px solid #e8e0d8;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .ulasan_top {
            background-color: #f7f3ef;
            padding: 12px 20px;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #e8e0d8;
        }

        .ulasan_top h2 {
            font-size: 14px;
            font-weight: 500;
            color: #555;
            margin: 0;
        }

        .ulasan_top_kiri {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .stars {
            color: #FFD700;
            font-size: 13px;
        }

        .aksi {
            display: flex;
            gap: 10px;
        }

        .aksi i {
            font-size: 15px;
            color: #888;
            cursor: pointer;
            transition: color 0.2s, transform 0.2s;
        }

        .aksi i:hover {
            color: #6D4A37;
            transform: scale(1.15);
        }

        .aksi .fa-trash:hover {
            color: #E74C3C;
        }

        .isi_ulasan {
            padding: 16px 20px;
        }

        .isi_ulasan p {
            font-size: 14px;
            line-height: 1.7;
            color: #444;
            margin-bottom: 8px;
        }

        .isi_ulasan p:last-child {
            margin-bottom: 0;
        }

        .isi_ulasan a {
            color: #6D4A37;
            font-weight: 500;
            text-decoration: none;
        }

        .isi_ulasan a:hover {
            text-decoration: underline;
        }

        .kosong {
            text-align: center;
            color: #999;
            font-size: 14px;
            padding: 60px 0;
        }

        /* Modal Edit */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 999;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.show {
            display: flex;
        }

        .modal {
            background: #fff;
            border-radius: 12px;
            padding: 28px;
            width: 90%;
            max-width: 500px;
        }

        .modal h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
        }

        .modal textarea {
            width: 100%;
            height: 120px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            resize: vertical;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 16px;
        }

        .btn-batal {
            padding: 8px 20px;
            border: 1px solid #ddd;
            border-radius: 20px;
            background: #fff;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
        }

        .btn-simpan {
            padding: 8px 20px;
            border: none;
            border-radius: 20px;
            background: #6D4A37;
            color: #fff;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
        }

        @media (max-width: 700px) {
            .content {
                padding: 20px 16px;
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
                <a href="dashboardulasan_user.php" class="active"><i class="fas fa-comment-alt"></i> <span>Riwayat
                        Ulasan</span></a>
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
            <h1>Riwayat Ulasanmu</h1>
            <?php if (isset($_GET['sukses'])): ?>
                <div
                    style="background:#d4edda;color:#1a5c2a;border:1px solid #b7dfc4;border-radius:8px;padding:12px 20px;font-size:14px;font-weight:500;max-width:800px;margin:0 auto 16px;">
                    ✅ Ulasan berhasil diperbarui!
                </div>
            <?php endif; ?>
            <?php if ($result && $result->num_rows > 0): ?>
                <div class="ulasan_container">
                    <?php while ($u = $result->fetch_assoc()):
                        $tgl = (date('Y-m-d', strtotime($u['waktu'])) === date('Y-m-d')) ? 'Hari ini' : date('l, d/m/Y', strtotime($u['waktu']));
                        $stars = str_repeat('★', $u['rating']) . str_repeat('☆', 5 - $u['rating']);
                        ?>
                        <div class="ulasan" id="ulasan-<?= $u['id'] ?>">
                            <div class="ulasan_top">
                                <div class="ulasan_top_kiri">
                                    <h2><?= htmlspecialchars($tgl) ?></h2>
                                    <span class="stars"><?= $stars ?></span>
                                </div>
                                <div class="aksi">
                                    <i class="fas fa-pen" title="Edit"
                                        onclick="bukaEdit(<?= $u['id'] ?>, `<?= addslashes(htmlspecialchars($u['isi_ulasan'])) ?>`)"></i>
                                    <i class="fas fa-trash" title="Hapus"
                                        onclick="if(confirm('Hapus ulasan ini?')) window.location='?hapus=<?= $u['id'] ?>'"></i>
                                </div>
                            </div>
                            <div class="isi_ulasan">
                                <p id="teks-<?= $u['id'] ?>">"<?= htmlspecialchars($u['isi_ulasan']) ?>"</p>
                                <p>Berkomentar di <a
                                        href="detailbuku.php?id=<?= $u['cerita_id'] ?>"><?= htmlspecialchars($u['judul']) ?></a>
                                </p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="kosong">Belum ada ulasan yang ditulis.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Edit Ulasan -->
    <div class="modal-overlay" id="modalEdit">
        <div class="modal">
            <h3>Edit Ulasan</h3>
            <form method="POST" action="edit_ulasan.php">
                <input type="hidden" name="ulasan_id" id="editId">
                <textarea name="isi_ulasan" id="editTeks" placeholder="Tulis ulasanmu..."></textarea>
                <div class="modal-actions">
                    <button type="button" class="btn-batal" onclick="tutupEdit()">Batal</button>
                    <button type="submit" class="btn-simpan">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function bukaEdit(id, teks) {
            document.getElementById('editId').value = id;
            document.getElementById('editTeks').value = teks;
            document.getElementById('modalEdit').classList.add('show');
        }
        function tutupEdit() {
            document.getElementById('modalEdit').classList.remove('show');
        }
        document.getElementById('modalEdit').addEventListener('click', function (e) {
            if (e.target === this) tutupEdit();
        });
    </script>
</body>

</html>
<?php $conn->close(); ?>