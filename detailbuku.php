<?php
include "koneksi.php";

// Ambil ID cerita dari URL
$id_cerita = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_cerita <= 0) { header("Location: jelajahi.php"); exit; }

// Ambil data cerita lengkap
$sql_cerita = "SELECT * FROM cerita_rakyat WHERE id = ?";
$stmt_c = $conn->prepare($sql_cerita);
$stmt_c->bind_param("i", $id_cerita);
$stmt_c->execute();
$cerita = $stmt_c->get_result()->fetch_assoc();
if (!$cerita) { header("Location: jelajahi.php"); exit; }

// Ambil bab pertama (untuk tombol Baca Sekarang)
$sql_bab1 = "SELECT id FROM bab WHERE cerita_id = ? ORDER BY id ASC LIMIT 1";
$stmt_b = $conn->prepare($sql_bab1);
$stmt_b->bind_param("i", $id_cerita);
$stmt_b->execute();
$bab_pertama = $stmt_b->get_result()->fetch_assoc();

// Hitung jumlah bab
$sql_jbab = "SELECT COUNT(*) as total FROM bab WHERE cerita_id = ?";
$stmt_jb = $conn->prepare($sql_jbab);
$stmt_jb->bind_param("i", $id_cerita);
$stmt_jb->execute();
$jumlah_bab = $stmt_jb->get_result()->fetch_assoc()['total'];

// Ambil ulasan
$sql_ul = "SELECT u.*, usr.nama, usr.foto FROM ulasan u 
           JOIN user usr ON u.user_id = user.id 
           WHERE u.cerita_id = ? ORDER BY u.created_at DESC";
$stmt_ul = $conn->prepare($sql_ul);
$stmt_ul->bind_param("i", $id_cerita);
$stmt_ul->execute();
$ulasan_list = $stmt_ul->get_result()->fetch_all(MYSQLI_ASSOC);

// Hitung rata-rata rating & distribusi
$distribusi = [5=>0, 4=>0, 3=>0, 2=>0, 1=>0];
$total_rating = 0;
foreach ($ulasan_list as $u) {
    $total_rating += $u['rating'];
    if (isset($distribusi[$u['rating']])) $distribusi[$u['rating']]++;
}
$jumlah_ulasan = count($ulasan_list);
$rata_rata = $jumlah_ulasan > 0 ? round($total_rating / $jumlah_ulasan, 1) : 0;

function bintang($r) {
    return str_repeat('★', (int)$r) . str_repeat('☆', 5 - (int)$r);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
            @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz@0,14..32;1,14..32&display=swap');
            @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,700;1,14..32,700&display=swap');
            @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,700;1,14..32,500&display=swap');

            body {
                margin: 0;
                padding-top: 60px;
                font-family: "Poppins";
                background-color: #FEFDF7;
                color: #000;
            }

            nav {
                background-color: #FEFDF7;
                font-family: 'inter';
                padding: 0.5em 1em;
                display: flex;
                align-items: center;
                box-shadow: 0 0 10px #8d8a8a;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                width: 100%;
                z-index: 1000;
                box-sizing: border-box;
            }

            .logo {
                width: 50px;
                display: flex;
                align-items: center;
                margin-right: auto;
                cursor: pointer;
            }

            .logo p {
                font-size: 12px;
                line-height: 1em;
                font-weight: 600;
                color: #6D4A37;
                margin: 0;
            }

            nav .profile:hover {
                transform: scale(1.1);
            }

            nav a {
                text-decoration: none;
                font-weight: 500;
                color: #918d8a;
                font-size: 16px;
                margin-right: 2em;
                transition: all 0.3s ease;
            }

            nav a:hover,
            nav a.active {
                color: #000;
                font-weight: 700;
            }

            nav .profile {
                width: 30px;
                height: 30px;
                cursor: pointer;
            }

            .detail_top {
                background-color: #ffefd8;
                display: flex;
                flex-direction: row;
                padding: 75px 0px 0px 125px;
                gap: 50px;
            }

            #sampul {
                width: 260px;
                height: 350px;
                border-radius: 5px;
                object-fit: cover;
            }

            .detail {
                display: flex;
                flex-direction: column;
            }

            .detail h2 {
                font-size: 25px;
                font-weight: 700;
            }

            .detail p {
                font-size: 15px;
                font-weight: 500;
            }

            .judul_cerita {
                font-size: 35px;
                font-weight: 500;
            }

            .update p,
            .actions_detail {
                font-size: 14px;
                font-weight: 400;
                margin: 0;
            }

            .detail h2,
            .detail p {
                margin: 0;
            }

            .update img {
                width: 15px;
                height: 15px;
            }

            .asal_daerah {
                font-size: 16px;
                font-weight: 600;
            }

            .isi_rating,
            .update,
            .detail_bab,
            .detail_baca {
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .top_rating {
                display: flex;
                flex-direction: column;
            }

            .top_rating .rating {
                font-size: 35px;
                color: #FFD700;
                margin: 0;
            }

            .actions {
                display: flex;
                align-items: center;
                gap: 10px;
                margin: auto 0px;
            }

            .actions img {
                width: 28px;
                height: 28px;
            }

            .button {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 10px 24px;
                background-color: #6D4A37;
                border-radius: 100px;
                color: white;
                cursor: pointer;
                transition: all 0.3s ease;
                font-size: 15px;
                font-weight: 500;
                text-decoration: none;
            }

            .button:hover {
                background-color: #5a3a2a;
                transform: scale(1.05);
            }

            .sinopsis,
            .ulasan {
                padding: 50px 200px 0px 125px;
            }

            .sinopsis_header,
            .ulasan_header {
                display: flex;
                align-items: center;
            }

            .coklat {
                width: 5px;
                height: 30px;
                background-color: #6D4A37;
                margin-right: 15px;
                border-radius: 2px;
            }

            .sinopsis_header h2,
            .ulasan_header h2 {
                font-size: 24px;
                font-weight: 600;
                margin: 0;
            }

            .sinopsis p,
            .ulasan_header p {
                text-align: justify;
                font-size: 14px;
            }

            .ulasan_rangkuman {
                display: flex;
                flex-direction: row;
            }

            .jumlah_rating {
                width: 500px;
            }


            .grafik_detail {
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
            }

            .ulasan_button {
                display: flex;
                justify-content: center;
                margin-top: 30px;
            }

            .ulasan_button button {
                padding: 8px 50px;
                background-color: #6D4A37;
                color: white;
                border: none;
                border-radius: 100px;
                font-size: 16px;
                font-weight: 500;
                font-family: 'Poppins';
                cursor: pointer;
                transition: all 0.3s ease;
            }

            .ulasan_button button:hover {
                background-color: #5a3a2a;
                transform: scale(1.05);
            }

            #popup {
                display: none;
                justify-content: center;
                align-items: center;
                width: fit-content;
                height: fit-content;
                margin: auto;
                padding: 5px;
                background-color: #fff;
                gap: 10px;
                z-index: 2;
            }

            #close_ulasan {
                position: relative;
                top: 10px;
                left: 0px;
                cursor: pointer;
                color: #000;
            }

            #popup.active {
                display: flex;
                flex-direction: column;
                box-shadow: 0 0 10px #8d8a8a;
            }

            #popup.active h2 {
                font-size: 20px;
                font-weight: 500;
                margin: 0px;
            }

            .beri_rating {
                display: flex;
                flex-direction: row;
                padding: 5px 10px;
                gap: 2px;
                border: 1px solid #8d8a8a;
                border-radius: 6px;
            }

            .ulasan_container {
                display: flex;
                flex-direction: column;
                gap: 20px;
                margin-bottom: 30px;
                padding: 0px;
            }

            .ulasan_container .ulasan {
                border-bottom: 2px solid #ddd;
                padding: 0px;
                padding-bottom: 10px;
            }

            .ulasan_container :first-child {
                margin-top: 10px;
            }

            .ulasan_top {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .ulasan_top .profile {
                width: 30px;
                height: 30px;
                object-fit: cover;
                display: block;
                margin-bottom: 10px;
            }

            .ulasan_info {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }

            .ulasan_info h3 {
                font-size: 12px;
                font-weight: 600;
                margin: 0;
            }

            .rating {
                color: #FFD700;
                font-size: 25px;
            }

            .isi_ulasan p {
                text-align: justify;
                font-size: 13px;
                color: #333;
                margin: 5px;
            }

            .isi_ulasan .tanggal {
                font-size: 10px;
                color: #999;
                margin-top: 10px;
            }


            footer {
                background-color: #FFF5E7;
                text-align: justify;
                font-family: "Inter";
                font-size: 14px;
            }

            .footer_top {
                padding: 3em 4em;
                display: flex;
                gap: 150px;
            }

            .footer_bottom {
                padding: 0.5em 4em;
                text-align: center;
            }

            .footer_bottom hr {
                border-top: 1.2px solid #000000;
            }

            #tentang-web,
            #navigasi,
            #kontak {
                max-width: 25em;
            }

            footer ul {
                list-style-type: none;
                padding-inline-start: 0;
                line-height: 2em;
            }

            a {
                text-decoration: none;
                color: #000;

            }
        </style>
        <link rel="icon" href="cr_wrapper/logo_web.png">
        <title>Gema Nusantara</title>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
        <script
            src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-datalabels/2.2.0/chartjs-plugin-datalabels.min.js"></script>
    </head>

<body>
    <nav>
        <div class="logo">
            <img src="img/logoweb.svg" alt="Logo Gema Nusantara">
            <p>Gema <br> Nusantara</p>
        </div>
        <a href="landingpage.html">Beranda</a>
        <a class="active" href="jelajahi.html">Jelajahi</a>
        <a href="saran.html">Saran</a>
        <img class="profile" src="img/profile22.svg" alt="User Icon">
    </nav>

    <section class="detail_top">
        <img id="sampul" src="buku/<?= htmlspecialchars($cerita['sampul']) ?>" 
             alt="Sampul <?= htmlspecialchars($cerita['judul']) ?>">

        <div class="detail">
            <h2><span class="judul_cerita"><?= htmlspecialchars($cerita['judul']) ?></span></h2>
            <div class="update">
                <span class="material-symbols-outlined icon-jam">schedule</span>
                <p>Terakhir diperbarui: <?= date('d - m - Y', strtotime($cerita['updated_at'] ?? 'now')) ?></p>
            </div>
            <h2><span class="asal_daerah">Asal Daerah: <?= htmlspecialchars($cerita['asal_daerah']) ?></span></h2>
            <div class="top_rating">
                <div class="isi_rating">
                    <h2><?= $rata_rata ?></h2>
                    <p><span class="rating"><?= bintang($rata_rata) ?></span></p>
                </div>
                <p><span class="actions_detail"><?= $jumlah_ulasan ?> Ulasan</span></p>
            </div>
            <div class="detail_bab">
                <h2><?= $jumlah_bab ?></h2>
                <p>Bab</p>
            </div>
            <div class="detail_baca">
                <h2><?= htmlspecialchars($cerita['jumlah_baca'] ?? 0) ?></h2>
                <p>Dibaca</p>
            </div>
            <div class="actions">
                <?php if ($bab_pertama): ?>
                    <a href="baca_buku.php?bab=<?= $bab_pertama['id'] ?>" class="button">Baca Sekarang</a>
                <?php else: ?>
                    <span class="button" style="opacity:0.5;cursor:default;">Belum Ada Bab</span>
                <?php endif; ?>
                <span class="material-symbols-outlined icon-action">favorite</span>
                <span class="material-symbols-outlined icon-action">bookmark</span>
                <span class="material-symbols-outlined icon-action">share</span>
            </div>
        </div>
    </section>

    <section class="sinopsis">
        <div class="sinopsis_header">
            <div class="coklat"></div>
            <h2>Sinopsis</h2>
        </div>
        <p><?= nl2br(htmlspecialchars($cerita['sinopsis'])) ?></p>
    </section>

    <section class="ulasan">
        <div class="ulasan_header">
            <div class="coklat"></div>
            <h2>Ulasan</h2>
        </div>

        <div class="ulasan_rangkuman">
            <div class="jumlah_rating">
                <canvas id="grafikRating"></canvas>
            </div>

            <hr class="pembatas">


            <div class="grafik_detail">
                <h2><?= $rata_rata ?>/5</h2>
                <div class="rating"><?= bintang($rata_rata) ?></div>
                <p><span class="actions_detail"><?= $jumlah_ulasan ?> Ulasan</span></p>
                <div class="ulasan_button">
                    <button id="open_ulasan">Beri Ulasan</button>
                </div>

                <div id="popup">
                    <span id="close_ulasan">x</span>
                    <h2>Tulis Ulasan</h2>
                    <p>Ceritakan kesan dan pengalamanmu membaca <?= htmlspecialchars($cerita['judul']) ?></p>
                    <div class="beri_rating">
                        <img src="star.svg">
                        <img src="star.svg">
                        <img src="star.svg">
                        <img src="star.svg">
                        <img src="star.svg">
                    </div>
                    <textarea id="deskripsi" name="deskripsi_cerita" placeholder="Tulis ulasanmu disini..."></textarea>
                    <button class="kirim_ulasan">Kirim Ulasan</button>
                </div>
            </div>

        </div>
        </div>

        <div class="ulasan_container">
            <?php if (!empty($ulasan_list)): ?>
                <?php foreach ($ulasan_list as $u): ?>
                <div class="ulasan">
                    <div class="ulasan_top">
                        <img class="profile" 
                             src="<?= $u['foto'] ? 'uploads/'.htmlspecialchars($u['foto']) : 'img/profile.svg' ?>" 
                             alt="User">
                        <h3><?= htmlspecialchars($u['nama']) ?></h3>
                        <div class="rating"><?= bintang($u['rating']) ?></div>
                    </div>
                    <div class="isi_ulasan">
                        <p><?= htmlspecialchars($u['komentar']) ?></p>
                        <p class="tanggal"><?= date('Y - m - d', strtotime($u['created_at'])) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color:#94A3B8;font-size:14px;padding:20px 0;">Belum ada ulasan untuk cerita ini.</p>
            <?php endif; ?>
        </div>
        </div>
    </section>


    <footer>
        <div class="footer_top">
            <div id="tentang-web">
                <div class="logo">
                    <img src="img/logoweb.svg" alt="Logo Gema Nusantara">
                    <p>Gema <br> Nusantara</p>
                </div>
                <p>Temukan kembali pesona dongeng masa kecil Anda di Gema Nusantara, rumah digital bagi cerita rakyat
                    yang telah diwariskan turun-temurun, dikemas dalam format yang mudah dinikmati kapan saja. Mari
                    bersama-sama melestarikan budaya Indonesia dan berbagi kearifan leluhur kepada generasi mendatang
                </p>
            </div>
            <div id="navigasi">
                <h3>Navigasi</h3>
                <ul>
                    <li><a href="landingpage.html">Beranda</a></li>
                    <li><a href="jelajahi.html">Jelajahi</a></li>
                    <li><a href="saran.html">Saran</a></li>
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
            <p>Copyright @Gema Nusantara 2025</p>
        </div>
    </footer>

    <script>
        const openUlasan = document.getElementById('open_ulasan');
        const closeUlasan = document.getElementById('close_ulasan');
        const popup = document.getElementById('popup');

        function openPopUpUlasan() {
            popup.classList.add('active');
        }
        function closePopUpUlasan() {
            popup.classList.remove('active');
        }

        openUlasan.addEventListener('click', openPopUpUlasan);
        closeUlasan.addEventListener('click', closePopUpUlasan);

        Chart.register(ChartDataLabels);
        const jumlahRating = ['5', '4', '3', '2', '1'];
        const rating = [<?= $distribusi[5] ?>, <?= $distribusi[4] ?>, <?= $distribusi[3] ?>, <?= $distribusi[2] ?>, <?= $distribusi[1] ?>];
        const ctx = document.getElementById('grafikRating').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: jumlahRating,
                datasets: [{
                    label: 'Jumlah Rating',
                    data: rating,
                    backgroundColor: ['#FFD700'],
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: {
                    datalabels: {
                        color: 'black',
                        font: {
                            weight: '500',
                        },
                        align: 'end',
                        anchor: 'end',
                        display: true,
                    }


                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 5,
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>