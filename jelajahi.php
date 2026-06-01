<?php
include "koneksi.php";

// Handle search
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($search !== '') {
    $like = "%$search%";
    $sql = "SELECT c.*, AVG(u.rating) as rata_rating 
            FROM cerita_rakyat c 
            LEFT JOIN ulasan u ON c.id = u.cerita_id
            WHERE c.judul LIKE ? 
            GROUP BY c.id 
            ORDER BY c.id ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT c.*, AVG(u.rating) as rata_rating 
            FROM cerita_rakyat c 
            LEFT JOIN ulasan u ON c.id = u.cerita_id
            GROUP BY c.id 
            ORDER BY c.id ASC";
    $result = mysqli_query($conn, $sql);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding-top: 70px;
            font-family: "Poppins", sans-serif;
            background-color: #FEFDF7;
            color: #000;
        }

        /* ===== NAVBAR (sama persis dengan beranda) ===== */
        nav {
            background-color: #FEFDF7;
            font-family: "Inter", sans-serif;
            padding: 15px 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(141, 138, 138, 0.15);
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
            height: auto;
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
            transition: all 0.3s ease;
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
            transition: transform 0.2s ease;
        }

        nav .profile:hover {
            transform: scale(1.1);
        }

        .menu-icon {
            display: none;
            font-size: 24px;
            color: #000;
            cursor: pointer;
        }

        @media (max-width: 900px) {
            .menu-icon {
                display: block;
            }

            .nav-menu {
                position: absolute;
                top: 65px;
                right: 0;
                background: #FEFDF7;
                flex-direction: column;
                width: 200px;
                padding: 15px 0;
                gap: 20px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
                border-radius: 8px;
                margin: 0;
                display: none;
            }

            .nav-menu.active {
                display: flex;
            }

            .nav-menu img.profile {
                margin-top: 10px;
            }
        }

        /* ===== HERO ===== */
        .hero {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-family: 'Poppins';
            color: #000;
            text-align: center;
            padding: 40px 20px 10px;
        }

        .hero h2 {
            font-size: 40px;
            font-weight: 300;
            margin-top: 30px;
            margin-bottom: 30px;
            text-shadow: 0px 10px 15px rgba(0, 0, 0, 0.5);
        }

        .search-bar {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            position: relative;
        }

        .search-bar input[type="text"] {
            padding: 10px 15px;
            border: 1px solid #000;
            border-radius: 20px;
            width: 300px;
            font-family: "Poppins", sans-serif;
            font-size: 14px;
            outline: none;
        }

        .search-bar button {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background-color: #000;
            color: #fff;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        /* ===== GRID CERITA ===== */
        .cerita-container {
            display: grid;
            grid-template-columns: repeat(4, 230px);
            gap: 50px;
            justify-content: center;
            margin: 60px auto 0;
            padding: 0 40px 120px;
        }

        @media (max-width: 1100px) {
            .cerita-container {
                grid-template-columns: repeat(3, 200px);
                gap: 40px;
                padding: 0 30px 100px;
            }
        }

        @media (max-width: 750px) {
            .cerita-container {
                grid-template-columns: repeat(2, 200px);
                gap: 30px;
                padding: 0 20px 80px;
            }

            .hero h2 {
                font-size: 26px;
            }
        }

        @media (max-width: 480px) {
            .cerita-container {
                grid-template-columns: repeat(1, 230px);
                gap: 30px;
                padding: 0 20px 80px;
            }
        }

        /* ===== KARTU BUKU ===== */
        .cerita-wrapper {
            width: 100%;
            padding: 15px;
            background-color: #6D4A36;
            color: #F7F4E9;
            display: flex;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .cerita-wrapper:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .sampul {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .cerita-wrapper h2 {
            margin: 0;
            font-weight: 600;
            font-size: 15px;
            height: 60px;
            display: flex;
            align-items: center;
        }

        .cerita-wrapper hr {
            margin: 0;
            border: none;
            border-top: 1px solid #F7F4E9;
        }

        .rating {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .star {
            width: 20px;
            height: 20px;
        }

        .rating-number {
            font-size: 16px;
            font-weight: 500;
            margin-right: auto;
        }

        .material-icons.icon-action {
            font-size: 22px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .material-icons.icon-action:hover {
            transform: scale(1.2);
        }

        /* ===== FOOTER ===== */
        footer {
            background-color: #FFF5E7;
            font-family: "Inter", sans-serif;
            font-size: 14px;
        }

        .footer_top {
            padding: 4em 4%;
            display: flex;
            gap: 50px;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
        }

        #tentang-web {
            max-width: 450px;
        }

        #tentang-web p {
            margin-top: 15px;
            line-height: 1.6;
            text-align: justify;
        }

        #navigasi,
        #kontak {
            min-width: 150px;
        }

        footer h3 {
            margin-bottom: 15px;
            font-size: 16px;
            color: #6D4A37;
        }

        footer ul {
            list-style-type: none;
            padding: 0;
            line-height: 2.2em;
        }

        footer a {
            text-decoration: none;
            color: #000;
            transition: color 0.2s;
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
            margin-bottom: 15px;
        }
    </style>
    <title>Gema Nusantara - Jelajahi</title>
</head>

<body>
    <header>
        <nav>
            <div class="logo">
                <img src="img/logoweb.svg" alt="logo web">
                <p>Gema <br>Nusantara</p>
            </div>

            <div class="menu-icon" id="menu-icon">
                <i class="ph ph-list"></i>
            </div>

            <div class="nav-menu" id="nav-menu">
                <a href="landingpage.php">Beranda</a>
                <a class="active" href="jelajahi.php">Jelajahi</a>
                <a href="saran.php">Saran</a>
                <a href="settingakun_baru.php"><img class="profile" src="img/profile22.svg" alt="user profile"></a>
            </div>
        </nav>
        <script>
            const menuIcon = document.getElementById("menu-icon");
            const navMenu = document.getElementById("nav-menu");

            menuIcon.addEventListener("click", () => {
                navMenu.classList.toggle("active");
            });
        </script>
    </header>

    <section class="hero" id="home">
        <h2>Menyelami Kisah Tradisi dalam Genggaman Tangan</h2>
        <form class="search-bar" method="GET" action="jelajahi.php">
            <input type="text" name="q" placeholder="Telusuri judul buku"
                value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>
    </section>

    <div class="cerita-container">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($data = mysqli_fetch_assoc($result)): ?>
                <div class="cerita-wrapper" onclick="window.location.href='detailbuku.php?id=<?php echo $data['id']; ?>'">
                    <img class="sampul"
                        src="buku/<?php echo htmlspecialchars($data['sampul']); ?>"
                        alt="Gambar <?php echo htmlspecialchars($data['judul']); ?>">
                    <h2><?php echo htmlspecialchars($data['judul']); ?></h2>
                    <hr>
                    <div class="rating">
                        <span class="star">⭐</span>
                        <span class="rating-number">
                            <?php echo $data['rata_rating'] ? number_format($data['rata_rating'], 1) : '0.0'; ?>
                        </span>
                        <span class="material-icons icon-action"
                            onclick="event.stopPropagation(); location.href='dashboardsuka_user.php';">favorite_border</span>
                        <span class="material-icons icon-action"
                            onclick="event.stopPropagation(); location.href='dashboardsimpan.php';">bookmark_border</span>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="grid-column:1/-1; text-align:center; padding:60px; color:#94A3B8;">
                <p style="font-size:48px;">📚</p>
                <p style="margin-top:12px; font-size:16px;">
                    Cerita "<?php echo htmlspecialchars($search); ?>" tidak ditemukan.
                </p>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <div class="footer_top">
            <div id="tentang-web">
                <div class="logo">
                    <img src="img/logoweb.svg" alt="Logo Gema Nusantara">
                    <p>Gema <br> Nusantara</p>
                </div>
                <p>Temukan kembali pesona dongeng masa kecil Anda di Gema Nusantara, rumah digital bagi cerita
                    rakyat yang telah diwariskan turun-temurun, dikemas dalam format yang mudah dinikmati kapan saja.
                    Mari bersama-sama melestarikan budaya Indonesia dan berbagi kearifan leluhur kepada generasi
                    mendatang.
                </p>
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