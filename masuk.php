<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Gema Nusantara</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', Arial, sans-serif;
            display: flex;
            height: 100vh;
            background-color: #F7F4E9;
        }

        /* ── KIRI ──────────────────────────────────── */
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
            color: #6b4226;
            font-weight: 700;
            font-size: 43px;
            text-align: center;
        }

        /* ── KANAN ─────────────────────────────────── */
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
            font-size: 44px;
            font-weight: 600;
            margin-bottom: 35px;
        }

        /* ── INPUT ─────────────────────────────────── */
        .kotak-input input {
            display: block;
            width: 70%;
            padding: 12px 18px;
            background-color: #fff;
            margin: 0 auto 16px auto;
            color: #000;
            border: 1px solid #6b4226;
            border-radius: 12px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .kotak-input input:focus {
            border-color: #3d1f0a;
            box-shadow: 0 0 0 3px rgba(107, 66, 38, 0.1);
        }

        /* ── LUPA SANDI ────────────────────────────── */
        .lupa {
            display: flex;
            justify-content: flex-end;
            width: 70%;
            margin: 0 auto 10px auto;
        }

        .lupa a {
            font-size: 12px;
            color: #6b4226;
            text-decoration: none;
            font-weight: 500;
        }

        .lupa a:hover {
            text-decoration: underline;
        }

        /* ── TOMBOL MASUK ──────────────────────────── */
        .tombol-masuk {
            margin-top: 20px;
            margin-bottom: 16px;
        }

        .tombol-masuk button {
            width: 30%;
            background-color: #6b4226;
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

        /* ── DAFTAR LINK ───────────────────────────── */
        .belum {
            text-align: center;
            font-size: 14px;
            color: #000;
        }

        .belum a {
            color: #6b4226;
            text-decoration: none;
            font-weight: 600;
            margin-left: 4px;
        }

        /* ── RESPONSIVE ────────────────────────────── */
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

            .daftar-dengan {
                width: 90%;
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
                <h2>Masuk</h2>
            </div>

            <!-- Form dikirim ke proses_masuk.php via POST -->
            <form action="proses_masuk.php" method="POST">
                <div class="kotak-input">
                    <input type="email" name="email" placeholder="Masukkan email" required />
                    <input type="password" name="password" placeholder="Masukkan kata sandi" required />
                </div>

                <div class="lupa">
                    <a href="lupasandi.html">Lupa sandi?</a>
                </div>

                <div class="tombol-masuk">
                    <button type="submit" name="login">Masuk</button>
                </div>
            </form>

            <div class="belum">
                <p>Belum memiliki akun? <a href="daftarakun.php">Daftar sekarang!</a></p>
            </div>

        </div>
    </div>
</body>

</html>