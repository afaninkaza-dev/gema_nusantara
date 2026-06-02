<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['id'])) {
    header("Location: masuk.php");
    exit;
}

$user_id = $_SESSION['id'];
$active_page = 'profil';

// Satu query saja
$stmt = $conn->prepare("SELECT nama, email, foto FROM user WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$pesan = '';

// Handle upload foto — form terpisah dengan flag 'aksi=foto'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'foto') {
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $ftype = mime_content_type($_FILES['foto']['tmp_name']);

        if (in_array($ftype, $allowed)) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $filename = 'foto_' . $user_id . '_' . time() . '.' . $ext;
            $target = 'img/profil/' . $filename;

            if (!is_dir('img/profil'))
                mkdir('img/profil', 0755, true);

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target)) {
                if (!empty($user['foto']) && $user['foto'] !== 'img/profile.jpg' && file_exists($user['foto'])) {
                    unlink($user['foto']);
                }
                $upd = $conn->prepare("UPDATE user SET foto=? WHERE id=?");
                $upd->bind_param("si", $target, $user_id);
                $upd->execute();
                $user['foto'] = $target;
                $pesan = ['tipe' => 'sukses', 'isi' => 'Foto profil berhasil diperbarui.'];
            } else {
                $pesan = ['tipe' => 'error', 'isi' => 'Gagal menyimpan foto.'];
            }
        } else {
            $pesan = ['tipe' => 'error', 'isi' => 'Format foto tidak didukung. Gunakan JPG, PNG, atau WEBP.'];
        }
    } else {
        $pesan = ['tipe' => 'error', 'isi' => 'Gagal mengupload foto. Coba lagi.'];
    }
}

// Handle simpan profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'profil') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password_baru = $_POST['password'] ?? '';

    if ($nama === '' || $email === '') {
        $pesan = ['tipe' => 'error', 'isi' => 'Nama dan email tidak boleh kosong.'];
    } else {
        if ($password_baru !== '') {
            $hash = password_hash($password_baru, PASSWORD_DEFAULT);
            $upd = $conn->prepare("UPDATE user SET nama=?, email=?, password=? WHERE id=?");
            $upd->bind_param("sssi", $nama, $email, $hash, $user_id);
        } else {
            $upd = $conn->prepare("UPDATE user SET nama=?, email=? WHERE id=?");
            $upd->bind_param("ssi", $nama, $email, $user_id);
        }

        if ($upd->execute()) {
            $_SESSION['nama'] = $nama;
            $user['nama'] = $nama;
            $user['email'] = $email;
            $pesan = ['tipe' => 'sukses', 'isi' => 'Profil berhasil diperbarui.'];
        } else {
            $pesan = ['tipe' => 'error', 'isi' => 'Gagal memperbarui profil: ' . $upd->error];
        }
    }
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
    <title>Pengaturan Akun - Gema Nusantara</title>
    <link rel="icon" href="img/logoweb.svg">
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
            overflow: hidden;
        }


        /* CONTENT */
        .content {
            flex: 1;
            min-width: 0;
            padding: 40px 80px;
            overflow-y: auto;
        }

        .content h1 {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 32px;
        }

        /* ALERT */
        .alert {
            padding: 12px 18px;
            border-radius: 8px;
            font-size: 13.5px;
            margin-bottom: 24px;
        }

        .alert.sukses {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }

        .alert.error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }

        /* FORM */
        .form-group {
            margin-bottom: 22px;
        }

        label {
            display: block;
            font-size: 13px;
            color: #444;
            margin-bottom: 8px;
        }

        .input-wrap {
            position: relative;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 11px 40px 11px 14px;
            border: 1px solid #CBD5E1;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            color: #333;
            background: #fff;
            transition: border-color .2s;
            box-sizing: border-box;
        }

        input:focus {
            border-color: #6D4A37;
            outline: none;
        }

        .input-wrap i {
            position: absolute;
            top: 50%;
            right: 14px;
            transform: translateY(-50%);
            color: #94A3B8;
            font-size: 14px;
            cursor: pointer;
        }

        .inline-group {
            display: flex;
            gap: 20px;
        }

        .inline-group>div {
            flex: 1;
        }

        select {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #CBD5E1;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            color: #333;
            background: #fff;
            transition: border-color .2s;
            box-sizing: border-box;
            cursor: pointer;
        }

        select:focus {
            border-color: #6D4A37;
            outline: none;
        }

        .btn-simpan {
            margin-top: 10px;
            padding: 11px 32px;
            background: #6D4A37;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background .2s;
        }

        .btn-simpan:hover {
            background: #5a3d2d;
        }

        @media (max-width: 700px) {
            .content {
                padding: 24px 20px;
            }

            .inline-group {
                flex-direction: column;
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
                <a href="settingakun_baru.php" class="active"><i class="fas fa-user-cog"></i> <span>Profil</span></a>
                <a href="dashboard_user.php"><i class="fas fa-tasks"></i> <span>Aktivitas Saya</span></a>
                <a href="dashboardsimpan_user.php"><i class="fas fa-bookmark"></i> <span>Cerita Tersimpan</span></a>
                <a href="dashboardsuka_user.php"><i class="fas fa-heart"></i> <span>Cerita Disukai</span></a>
                <a href="dashboardulasan_user.php"><i class="fas fa-comment-alt"></i> <span>Riwayat Ulasan</span></a>
                <a href="dashboardhistory_user.php"><i class="fas fa-history"></i> <span>Riwayat Membaca</span></a>
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


        <!-- CONTENT -->
        <div class="content">
            <h1>Halo, <?= htmlspecialchars($user['nama']) ?></h1>

            <?php if ($pesan): ?>
                <div class="alert <?= $pesan['tipe'] ?>"><?= $pesan['isi'] ?></div>
            <?php endif; ?>

            <!-- Form profil dengan flag aksi=profil -->
            <form method="POST" action="">
                <input type="hidden" name="aksi" value="profil">

                <div class="form-group">
                    <label for="nama">Nama</label>
                    <div class="input-wrap">
                        <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($user['nama']) ?>"
                            required>
                        <i class="fas fa-edit"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-wrap">
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"
                            required>
                        <i class="fas fa-edit"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password Baru</label>
                    <div class="input-wrap">
                        <input type="password" id="password" name="password"
                            placeholder="Kosongkan jika tidak ingin mengubah">
                        <i class="fas fa-eye" id="togglePw"></i>
                    </div>
                </div>

                <div class="form-group">
                    <div class="inline-group">
                        <div>
                            <label for="jenis_kelamin">Jenis Kelamin</label>
                            <select id="jenis_kelamin">
                                <option value="Laki-Laki">Laki-Laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label for="negara">Negara</label>
                            <select id="negara">
                                <option value="Indonesia">Indonesia</option>
                                <option value="Malaysia">Malaysia</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-simpan">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <script>
        // Toggle password
        document.getElementById('togglePw').addEventListener('click', function () {
            const pw = document.getElementById('password');
            const isHidden = pw.type === 'password';
            pw.type = isHidden ? 'text' : 'password';
            this.classList.toggle('fa-eye', !isHidden);
            this.classList.toggle('fa-eye-slash', isHidden);
        });
    </script>
</body>

</html>
<?php $conn->close(); ?>