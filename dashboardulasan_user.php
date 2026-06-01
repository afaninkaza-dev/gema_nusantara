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

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            width: 220px;
            background-color: #fff;
            padding: 24px 16px 20px;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.06);
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            flex-shrink: 0;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 28px;
            cursor: pointer;
            text-decoration: none;
        }

        .logo img {
            width: 36px;
            height: 36px;
        }

        .logo-text {
            font-size: 15px;
            font-weight: 600;
            color: #6D4A37;
            line-height: 1.25;
        }

        .profile-wrapper {
            position: relative;
            align-self: center;
            margin-bottom: 28px;
        }

        .profile-wrapper img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            background: #f0e8e2;
        }

        .edit-icon {
            position: absolute;
            bottom: 2px;
            right: 2px;
            background: #fff;
            border-radius: 50%;
            width: 26px;
            height: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18);
            font-size: 11px;
            color: #555;
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
            transition: background 0.2s, color 0.2s;
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
            background: #6D4A37;
            color: #fff;
            font-weight: 500;
        }

        .logout-button {
            margin-top: auto;
            color: #C0392B !important;
            font-weight: 500 !important;
        }

        .logout-button:hover {
            background: #fdf0ee !important;
        }

        /* ── CONTENT ── */
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

        /* Ulasan */
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

        /* Responsive */
        @media (max-width: 700px) {
            .sidebar {
                width: 60px;
                padding: 16px 8px;
            }

            .logo-text,
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
                margin: 0;
                font-size: 18px;
            }

            .profile-wrapper img {
                width: 40px;
                height: 40px;
            }

            .logo img {
                width: 32px;
            }

            .edit-icon {
                display: none;
            }

            .content {
                padding: 20px 16px;
            }
        }
    </style>
</head>

<body>
    <div class="container">

        <!-- SIDEBAR -->
        <div class="sidebar">
            <a href="landingpage.php" class="logo">
                <img src="img/logoweb.svg" alt="logo">
                <span class="logo-text">Gema<br>Nusantara</span>
            </a>
            <div class="profile-wrapper">
                <img src="img/profile22.svg" alt="Foto Profil">
                <div class="edit-icon"><i class="fas fa-pen"></i></div>
            </div>
            <nav>
                <a href="settingakun_baru.php"><i class="fas fa-user-cog"></i> <span>Pengaturan Akun</span></a>
                <a href="dashboard_user.php"><i class="fas fa-tasks"></i> <span>Aktivitas Saya</span></a>
                <a href="dashboardsimpan_user.php"><i class="fas fa-bookmark"></i> <span>Cerita Tersimpan</span></a>
                <a href="dashboardsuka_user.php"><i class="fas fa-heart"></i> <span>Cerita Disukai</span></a>
                <a href="dashboardulasan_user.php" class="active"><i class="fas fa-comment-alt"></i> <span>Riwayat
                        Ulasan</span></a>
                <a href="dashboardhistory_user.php"><i class="fas fa-history"></i> <span>Riwayat Membaca</span></a>
                <a href="masuk.php" class="logout-button"><i class="fas fa-sign-out-alt"></i> <span>Keluar</span></a>
            </nav>
        </div>

        <!-- CONTENT -->
        <div class="content">
            <h1>Riwayat Ulasanmu</h1>

            <div class="ulasan_container">

                <div class="ulasan">
                    <div class="ulasan_top">
                        <h2>Hari ini</h2>
                        <div class="aksi">
                            <i class="fas fa-pen" title="Edit"></i>
                            <i class="fas fa-trash" title="Hapus"></i>
                        </div>
                    </div>
                    <div class="isi_ulasan">
                        <p>"Sebagai orang tua, saya tersentuh dengan pesan moral dalam cerita To Dilaling. Bahwa setiap
                            anak punya potensi besar, terlepas dari latar belakang atau kondisi awalnya."</p>
                        <p>Berkomentar di <a href="detailbuku.php?id=1">To Dilaling</a></p>
                    </div>
                </div>

                <div class="ulasan">
                    <div class="ulasan_top">
                        <h2>Selasa, 18/11/2025</h2>
                        <div class="aksi">
                            <i class="fas fa-pen" title="Edit"></i>
                            <i class="fas fa-trash" title="Hapus"></i>
                        </div>
                    </div>
                    <div class="isi_ulasan">
                        <p>"Cerita Rawa Pening sangat memukau. Legenda tentang Baro Klinting ini mengajarkan kita untuk
                            tidak sombong dan selalu menghargai sesama."</p>
                        <p>Berkomentar di <a href="detailbuku.php?id=2">Rawa Pening</a></p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</body>

</html>