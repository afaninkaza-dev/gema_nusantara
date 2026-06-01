<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Gema Nusantara</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz@0,14..32;1,14..32&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,700;1,14..32,700&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,700;1,14..32,500&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            height: 100vh;
            background-color: #F7F4E9;
        }

        .bagian-kiri {
            background-color: #F7F4E9;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .logo {
            width: 300px;
        }

        .brand {
            color: #6D4A36;
            font-weight: 700;
            font-size: 43px;
            text-align: center;
        }

        .bagian-kanan {
            flex: 1;
            background-color: #fff;
            border-top-left-radius: 80px;
            border-bottom-left-radius: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .register {
            width: 100%;
            text-align: center;
        }

        .welcome h2 {
            font-size: 40px;
            font-weight: 600;
            margin-bottom: 30px;
        }

        .pesan-error {
            color: #c0392b;
            font-size: 13px;
            margin-bottom: 10px;
        }

        .kotak-input input {
            display: block;
            width: 70%;
            padding: 12px 18px;
            background-color: #fff;
            margin: 0 auto 14px auto;
            color: #000;
            border: 1px solid #6D4A36;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            outline: none;
            transition: border-color 0.2s;
        }

        .kotak-input input:focus {
            border-color: #3d1f0a;
            box-shadow: 0 0 0 3px rgba(107, 66, 38, 0.1);
        }

        .tombol-masuk {
            margin-top: 25px;
            margin-bottom: 15px;
        }

        .tombol-masuk button {
            width: 30%;
            background-color: #6D4A36;
            color: white;
            border: none;
            padding: 14px 20px;
            border-radius: 30px;
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .tombol-masuk button:hover {
            background-color: #4e2f17;
        }

        .sudah {
            text-align: center;
            font-size: 14px;
            color: #000;
        }

        .sudah a {
            color: #6b4226;
            text-decoration: none;
            font-weight: 500;
            margin-left: 4px;
        }

        @media (max-width: 900px) {
            body {
                flex-direction: column;
                height: auto;
                min-height: 100vh;
            }

            .bagian-kiri {
                display: none;
            }

            .bagian-kanan {
                flex: 1;
                border-radius: 0;
                width: 100%;
                padding: 40px 20px;
            }

            .kotak-input input {
                width: 90%;
            }

            .tombol-masuk button {
                width: 60%;
            }
        }
    </style>
</head>

<body>
    <div class="bagian-kiri">
        <div class="logo-container">
            <img src="img/logoweb.svg" alt="Logo Gema Nusantara" class="logo">
            <h1 class="brand">Gema Nusantara</h1>
        </div>
    </div>

    <div class="bagian-kanan">
        <div class="register">
            <div class="welcome">
                <h2>Daftar Akun</h2>
            </div>

            <form action="proses_daftar.php" method="POST">
                <div class="kotak-input">
                    <input type="text" name="nama" placeholder="Masukkan nama" required />
                    <input type="email" name="email" placeholder="Masukkan email" required />
                    <input type="password" name="password" placeholder="Masukkan kata sandi" required />
                    <input type="password" name="konfirm" placeholder="Konfirmasi kata sandi" required />
                </div>
                <div class="tombol-masuk">
                    <button type="submit" name="submit">Buat Akun</button>
                </div>
            </form>

            <div class="sudah">
                <p>Sudah memiliki akun? <a href="masuk.php">Masuk!</a></p>
            </div>
        </div>
    </div>
</body>

</html>