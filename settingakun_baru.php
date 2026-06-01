<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Akun</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            background-color: #F7F7F7;
            color: #333;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 240px;
            background-color: #fff;
            padding: 20px 15px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            position: relative;
            box-sizing: border-box;
        }

        .logo {
            display: flex;
            align-items: center;
            cursor: pointer;
            margin-bottom: 24px;
            gap: 10px;
        }

        .logo img {
            width: 40px;
        }

        .logo p {
            font-size: 16px;
            line-height: 1.2em;
            font-weight: 600;
            color: #6D4A37;
            margin: 0;
            font-family: 'Poppins', sans-serif;
        }

        .profile-wrapper {
            position: relative;
            align-self: center;
            margin-bottom: 24px;
        }

        .profile-wrapper img {
            width: 110px;
            display: block;
        }

        .sidebar .edit-icon {
            position: absolute;
            bottom: 0px;
            right: 0px;
            background-color: #fff;
            border-radius: 50%;
            padding: 5px;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .sidebar a {
            display: flex;
            align-items: center;
            padding: 10px 12px;
            margin-bottom: 4px;
            text-decoration: none;
            color: #000;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 400;
            font-family: 'Poppins', sans-serif;
            width: 100%;
            box-sizing: border-box;
            border-radius: 8px;
        }

        .sidebar a i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
            font-size: 16px;
        }

        .sidebar a:hover {
            color: #6D4A37;
            background-color: #f5f5f5;
        }

        .sidebar a.active {
            font-weight: 400;
            color: #fff;
            background-color: #6D4A37;
        }

        .logout-button {
            margin-top: auto;
            padding-top: 16px;
            border-top: 1px solid #eee;
            font-weight: 400;
            width: 100%;
            color: #C0392B !important;
            border-radius: 8px;
        }

        .content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .content h1 {
            font-size: 22px;
            margin-bottom: 40px;
            margin-left: 60px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
        }

        .form-group {
            margin-bottom: 20px;
            margin-left: 60px;
        }

        label {
            display: block;
            font-size: 13px;
            color: #000;
            margin-bottom: 8px;
            font-family: 'Poppins', sans-serif;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="password"],
        select {
            width: 70%;
            padding: 12px;
            border: 1px solid #94A3B8;
            border-radius: 5px;
            font-size: 14px;
            color: #333;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="tel"]:focus,
        input[type="password"]:focus,
        select:focus {
            border-color: #6D4A37;
            outline: none;
        }

        .input-with-icon {
            position: relative;
            width: 70%;
            /* Disesuaikan dengan lebar input */
        }

        .input-with-icon input {
            width: 100%;
            /* Menghindari luapan */
            padding-right: 40px;
        }

        .input-with-icon i {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            color: #94A3B8;
            cursor: pointer;
        }

        .form-group.inline {
            display: flex;
            width: 70%;
            gap: 20px;
        }

        .form-group.inline>div {
            flex: 1;
        }

        .form-group.inline select {
            width: 100%;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                align-items: center;
            }

            .sidebar a {
                justify-content: center;
            }

            .content h1,
            .form-group {
                margin-left: 0;
            }

            input[type="text"],
            input[type="email"],
            input[type="tel"],
            input[type="password"],
            select,
            .input-with-icon,
            .form-group.inline {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="sidebar">
            <div class="logo">
                <a href="landingpage.html"><img src="img/logoweb.svg" alt="logo web"></a>
                <p>Gema <br>Nusantara</p>
            </div>

            <div class="profile-wrapper">
                <img src="img/profile22.svg" alt="Foto Profil">
                <div class="edit-icon"><i class="fas fa-pen"></i></div>
            </div>

            <a class="active" href="settingakun_baru.php"><i class="fas fa-user-cog"></i> Pengaturan Akun</a>
            <a href="dashboard_user.php"><i class="fas fa-tasks"></i> Aktivitas Saya</a>
            <a href="dashboardsimpan_user.php"><i class="fas fa-bookmark"></i> Cerita Tersimpan</a>
            <a href="dashboardsuka_user.php"><i class="fas fa-heart"></i> Cerita Disukai</a>
            <a href="dashboardulasan_user.php"><i class="fas fa-comment-alt"></i> Riwayat Ulasan</a>
            <a href="dashboardhistory_user.php"><i class="fas fa-history"></i> Riwayat Membaca</a>

            <a href="daftarakun.html" class="logout-button"><i class="fas fa-sign-out-alt"></i> Keluar</a>
        </div>

        <div class="content">
            <h1>Halo, Rangga</h1>
            <form>
                <div class="form-group">
                    <label for="nama">Nama</label>
                    <div class="input-with-icon">
                        <input type="text" id="nama" value="Rangga">
                        <i class="fas fa-edit"></i>
                    </div>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-with-icon">
                        <input type="email" id="email" value="rangga123@gmail.com">
                        <i class="fas fa-edit"></i>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <input type="password" id="password" value=".....">
                        <i class="fas fa-edit"></i>
                    </div>
                </div>
                <div class="form-group inline">
                    <div>
                        <label for="jenisKelamin">Jenis Kelamin</label>
                        <select id="jenisKelamin">
                            <option>Laki-Laki</option>
                            <option>Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label for="negara">Negara</label>
                        <select id="negara">
                            <option>Indonesia</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>

</html>