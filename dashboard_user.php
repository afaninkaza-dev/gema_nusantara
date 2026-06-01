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

        /* Stat Boxes */
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

        /* Riwayat */
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

        /* Card Grid */
        .cerita_container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
        }

        /* Card */
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
        }

        .cerita_wrapper hr {
            border: none;
            border-top: 1px solid rgba(247, 244, 233, 0.4);
        }

        /* Rating Row */
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
            animation: pop 0.25s ease;
        }

        .icon-small.saved {
            color: #FFD700;
            animation: pop 0.25s ease;
        }

        @keyframes pop {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.4);
            }

            100% {
                transform: scale(1);
            }
        }

        /* Right Panel */
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

        .action-icons i.liked {
            color: #E74C3C;
        }

        .action-icons i.saved {
            color: #6D4A37;
        }

        /* Responsive */
        @media (max-width: 1100px) {
            .dashboard {
                flex-direction: column;
            }

            .dashboard_kanan {
                width: 100%;
                flex-direction: row;
            }

            .jumlah_membaca {
                flex: 1;
            }

            .lanjut_baca {
                flex: 1;
            }

            .cerita_container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 700px) {
            .sidebar {
                width: 60px;
                padding: 16px 8px;
            }

            .logo-text,
            .sidebar a span,
            .sidebar a:not(.logo) {
                font-size: 0;
                gap: 0;
                padding: 10px;
                justify-content: center;
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

            .rangkum_baca {
                flex-direction: column;
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
                <a href="dashboard_user.php" class="active"><i class="fas fa-tasks"></i> <span>Aktivitas Saya</span></a>
                <a href="dashboardsimpan_user.php"><i class="fas fa-bookmark"></i> <span>Cerita Tersimpan</span></a>
                <a href="dashboardsuka_user.php"><i class="fas fa-heart"></i> <span>Cerita Disukai</span></a>
                <a href="dashboardulasan_user.php"><i class="fas fa-comment-alt"></i> <span>Riwayat Ulasan</span></a>
                <a href="dashboardhistory_user.php"><i class="fas fa-history"></i> <span>Riwayat Membaca</span></a>
                <a href="masuk.php" class="logout-button"><i class="fas fa-sign-out-alt"></i> <span>Keluar</span></a>
            </nav>
        </div>

        <!-- CONTENT -->
        <div class="content">
            <section class="dashboard">

                <!-- KIRI -->
                <div class="dashboard_kiri">
                    <div class="rangkum_baca">
                        <div class="isi_rangkum">
                            <h2>Total Cerita Yang Sedang Dibaca</h2>
                            <div class="rangkum_detail">
                                <img src="img/book.svg" alt="Buku">
                                <h3>23</h3>
                            </div>
                        </div>
                        <div class="isi_rangkum">
                            <h2>Total Cerita Yang Selesai Dibaca</h2>
                            <div class="rangkum_detail">
                                <img src="img/book.svg" alt="Buku">
                                <h3>8</h3>
                            </div>
                        </div>
                    </div>

                    <div class="riwayat">
                        <div class="riwayat_header">
                            <h3>Riwayat Membacamu</h3>
                            <a href="dashboardhistory_user.php">Lihat Semua &gt;</a>
                        </div>
                        <div class="cerita_container">

                            <div class="cerita_wrapper">
                                <img class="sampul" src="img/ceritakeong.svg" alt="Keong Mas">
                                <h2>Keong Mas</h2>
                                <hr>
                                <div class="rating">
                                    <i class="fas fa-star star"></i>
                                    <span class="rating-number">4.4</span>
                                    <i class="fas fa-heart icon-small btn-like" title="Suka"></i>
                                    <i class="fas fa-bookmark icon-small btn-save" title="Simpan"></i>
                                </div>
                            </div>

                            <div class="cerita_wrapper">
                                <img class="sampul" src="img/ceritabawang.svg" alt="Bawang Merah dan Bawang Putih">
                                <h2>Bawang Merah dan Bawang Putih</h2>
                                <hr>
                                <div class="rating">
                                    <i class="fas fa-star star"></i>
                                    <span class="rating-number">4.5</span>
                                    <i class="fas fa-heart icon-small btn-like" title="Suka"></i>
                                    <i class="fas fa-bookmark icon-small btn-save" title="Simpan"></i>
                                </div>
                            </div>

                            <div class="cerita_wrapper">
                                <img class="sampul" src="img/ceritabiwar.svg" alt="Biwar dan Naga">
                                <h2>Biwar dan Naga</h2>
                                <hr>
                                <div class="rating">
                                    <i class="fas fa-star star"></i>
                                    <span class="rating-number">4.4</span>
                                    <i class="fas fa-heart icon-small btn-like" title="Suka"></i>
                                    <i class="fas fa-bookmark icon-small btn-save" title="Simpan"></i>
                                </div>
                            </div>

                            <div class="cerita_wrapper">
                                <img class="sampul" src="img/ceritarawa.svg" alt="Rawa Pening">
                                <h2>Rawa Pening</h2>
                                <hr>
                                <div class="rating">
                                    <i class="fas fa-star star"></i>
                                    <span class="rating-number">4.4</span>
                                    <i class="fas fa-heart icon-small btn-like" title="Suka"></i>
                                    <i class="fas fa-bookmark icon-small btn-save" title="Simpan"></i>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- KANAN -->
                <div class="dashboard_kanan">
                    <div class="jumlah_membaca">
                        <canvas id="grafikMembaca"></canvas>
                    </div>
                    <div class="lanjut_baca">
                        <h2>Lanjut Membaca</h2>
                        <div class="isi_baca">
                            <img src="img/ceritatimun.svg" alt="Timun Mas">
                            <div class="baca_detail">
                                <h3>Timun Mas</h3>
                                <div class="isi_detail">
                                    <img src="img/waktu.svg" alt="Waktu">
                                    <p>Terakhir dibaca : 18-11-2025</p>
                                </div>
                                <div class="isi_detail">
                                    <p>4.5</p>
                                    <p id="star">★★★★★</p>
                                </div>
                                <div class="isi_detail">
                                    <img src="img/icon1.svg" alt="Pembaca">
                                    <p>8.1K</p>
                                </div>
                                <div class="isi_detail">
                                    <img src="img/picture.svg" alt="Bab">
                                    <p>Sisa 2 Bab</p>
                                </div>
                                <div class="baca_actions">
                                    <button class="btn-baca">Baca</button>
                                    <div class="action-icons">
                                        <i class="fas fa-heart btn-like-panel" title="Suka"></i>
                                        <i class="fas fa-bookmark btn-save-panel" title="Simpan"></i>
                                        <i class="fas fa-share-alt"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
                    labels: ['Sen', 'Sel', 'Rab', 'Kam', "Jum", 'Sab', 'Min'],
                    datasets: [{
                        label: 'Buku dibaca',
                        data: [3, 8, 6, 3, 3, 6, 5],
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
                        title: {
                            display: true, text: 'Jumlah Membaca Perhari',
                            font: { size: 13, weight: '600', family: 'Poppins' },
                            color: '#222', padding: { bottom: 10 }
                        },
                        legend: { display: false },
                        datalabels: { display: false }
                    },
                    scales: {
                        y: { min: 0, max: 10, ticks: { stepSize: 2 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                        x: { grid: { display: false } }
                    }
                }
            });

            // Like toggle
            document.querySelectorAll('.btn-like').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    this.classList.toggle('liked');
                    // Simpan ke localStorage sebagai "disukai"
                    const title = this.closest('.cerita_wrapper').querySelector('h2').textContent.trim();
                    let liked = JSON.parse(localStorage.getItem('liked') || '[]');
                    if (this.classList.contains('liked')) {
                        if (!liked.includes(title)) liked.push(title);
                    } else {
                        liked = liked.filter(t => t !== title);
                    }
                    localStorage.setItem('liked', JSON.stringify(liked));
                });
            });

            // Save toggle
            document.querySelectorAll('.btn-save').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    this.classList.toggle('saved');
                    const title = this.closest('.cerita_wrapper').querySelector('h2').textContent.trim();
                    let saved = JSON.parse(localStorage.getItem('saved') || '[]');
                    if (this.classList.contains('saved')) {
                        if (!saved.includes(title)) saved.push(title);
                    } else {
                        saved = saved.filter(t => t !== title);
                    }
                    localStorage.setItem('saved', JSON.stringify(saved));
                });
            });

            const likePanel = document.querySelector('.btn-like-panel');
            const savePanel = document.querySelector('.btn-save-panel');
            if (likePanel) likePanel.addEventListener('click', function () { this.classList.toggle('liked'); });
            if (savePanel) savePanel.addEventListener('click', function () { this.classList.toggle('saved'); });
        });
    </script>
</body>

</html>