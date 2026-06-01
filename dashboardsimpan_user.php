<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="img/logoweb.svg">
    <title>Gema Nusantara - Cerita Tersimpan</title>
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

        /* ── CARD GRID ── */
        .cerita_container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        /* ── CARD ── */
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

        .sampul {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            background-color: #8a6050;
            display: block;
        }

        .cerita_wrapper h2 {
            font-size: 13px;
            font-weight: 500;
            color: #F7F4E9;
            line-height: 1.4;
            min-height: 36px;
            display: flex;
            align-items: center;
            margin: 0;
        }

        .cerita_wrapper hr {
            border: none;
            border-top: 1px solid rgba(247, 244, 233, 0.4);
            margin: 0;
        }

        /* Rating Row */
        .rating {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .star {
            color: #FFD700;
            font-size: 13px;
        }

        .rating-number {
            font-size: 12px;
            font-weight: 500;
            flex: 1;
        }

        .icon-small {
            font-size: 15px;
            color: #F7F4E9;
            cursor: pointer;
            transition: transform 0.2s ease, color 0.2s ease;
        }

        .icon-small:hover {
            transform: scale(1.25);
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

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            margin-top: 36px;
            margin-bottom: 20px;
        }

        .pagination a,
        .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 6px;
            text-decoration: none;
            color: #555;
            font-size: 13px;
            font-weight: 500;
            border: 1px solid #e0d8d4;
            background: #fff;
            transition: all 0.2s;
        }

        .pagination a.active {
            background: #6D4A37;
            color: #fff;
            border-color: #6D4A37;
        }

        .pagination a:hover:not(.active) {
            background: #f5ede8;
            color: #6D4A37;
            border-color: #c8a898;
        }

        .pagination .dots {
            border: none;
            background: transparent;
            color: #999;
            pointer-events: none;
        }

        /* Responsive */
        @media (max-width: 1300px) {
            .cerita_container {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 960px) {
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
                <a href="dashboardsimpan_user.php" class="active"><i class="fas fa-bookmark"></i> <span>Cerita
                        Tersimpan</span></a>
                <a href="dashboardsuka_user.php"><i class="fas fa-heart"></i> <span>Cerita Disukai</span></a>
                <a href="dashboardulasan_user.php"><i class="fas fa-comment-alt"></i> <span>Riwayat Ulasan</span></a>
                <a href="dashboardhistory_user.php"><i class="fas fa-history"></i> <span>Riwayat Membaca</span></a>
                <a href="masuk.php" class="logout-button"><i class="fas fa-sign-out-alt"></i> <span>Keluar</span></a>
            </nav>
        </div>

        <!-- CONTENT -->
        <div class="content">
            <h1>Cerita yang Kamu Simpan</h1>

            <div class="cerita_container">

                <div class="cerita_wrapper">
                    <img class="sampul" src="img/ceritakeong.svg" alt="Keong Mas">
                    <h2>Keong Mas</h2>
                    <hr>
                    <div class="rating">
                        <i class="fas fa-star star"></i>
                        <span class="rating-number">4.6</span>
                        <i class="fas fa-heart icon-small btn-like" title="Suka"></i>
                        <i class="fas fa-bookmark icon-small btn-save saved" title="Simpan"></i>
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
                        <i class="fas fa-bookmark icon-small btn-save saved" title="Simpan"></i>
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
                        <i class="fas fa-bookmark icon-small btn-save saved" title="Simpan"></i>
                    </div>
                </div>

                <div class="cerita_wrapper">
                    <img class="sampul" src="img/ceritaabo.svg" alt="Abo Mamongkuroit">
                    <h2>Abo Mamongkuroit Dan Tulap Si Raksasa</h2>
                    <hr>
                    <div class="rating">
                        <i class="fas fa-star star"></i>
                        <span class="rating-number">4.4</span>
                        <i class="fas fa-heart icon-small btn-like liked" title="Suka"></i>
                        <i class="fas fa-bookmark icon-small btn-save saved" title="Simpan"></i>
                    </div>
                </div>

                <div class="cerita_wrapper">
                    <img class="sampul" src="img/ceritatimun.svg" alt="Timun Mas">
                    <h2>Timun Mas</h2>
                    <hr>
                    <div class="rating">
                        <i class="fas fa-star star"></i>
                        <span class="rating-number">4.7</span>
                        <i class="fas fa-heart icon-small btn-like" title="Suka"></i>
                        <i class="fas fa-bookmark icon-small btn-save saved" title="Simpan"></i>
                    </div>
                </div>

                <div class="cerita_wrapper">
                    <img class="sampul" src="img/ceritapangeran.svg" alt="Pangeran Amat Mude">
                    <h2>Pangeran Amat Mude</h2>
                    <hr>
                    <div class="rating">
                        <i class="fas fa-star star"></i>
                        <span class="rating-number">3.9</span>
                        <i class="fas fa-heart icon-small btn-like" title="Suka"></i>
                        <i class="fas fa-bookmark icon-small btn-save saved" title="Simpan"></i>
                    </div>
                </div>

                <div class="cerita_wrapper">
                    <img class="sampul" src="img/ceritabatu.svg" alt="Batu Golog">
                    <h2>Batu Golog</h2>
                    <hr>
                    <div class="rating">
                        <i class="fas fa-star star"></i>
                        <span class="rating-number">4.4</span>
                        <i class="fas fa-heart icon-small btn-like" title="Suka"></i>
                        <i class="fas fa-bookmark icon-small btn-save saved" title="Simpan"></i>
                    </div>
                </div>

                <div class="cerita_wrapper">
                    <img class="sampul" src="img/ceritakelingking.svg" alt="Si Kelingking">
                    <h2>Si Kelingking</h2>
                    <hr>
                    <div class="rating">
                        <i class="fas fa-star star"></i>
                        <span class="rating-number">4.4</span>
                        <i class="fas fa-heart icon-small btn-like" title="Suka"></i>
                        <i class="fas fa-bookmark icon-small btn-save saved" title="Simpan"></i>
                    </div>
                </div>

            </div>

            <div class="pagination">
                <a href="#">&laquo;</a>
                <a href="#" class="active">1</a>
                <a href="#">2</a>
                <a href="#">3</a>
                <span class="dots">...</span>
                <a href="#">7</a>
                <a href="#">8</a>
                <a href="#">&raquo;</a>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.btn-like').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                this.classList.toggle('liked');
                const title = this.closest('.cerita_wrapper')?.querySelector('h2').textContent.trim() || '';
                let liked = JSON.parse(localStorage.getItem('liked') || '[]');
                if (this.classList.contains('liked')) {
                    if (!liked.includes(title)) liked.push(title);
                } else {
                    liked = liked.filter(t => t !== title);
                }
                localStorage.setItem('liked', JSON.stringify(liked));
            });
        });

        document.querySelectorAll('.btn-save').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                this.classList.toggle('saved');
                const card = this.closest('.cerita_wrapper');
                const title = card?.querySelector('h2').textContent.trim() || '';
                let saved = JSON.parse(localStorage.getItem('saved') || '[]');
                if (this.classList.contains('saved')) {
                    if (!saved.includes(title)) saved.push(title);
                } else {
                    saved = saved.filter(t => t !== title);
                    // Hapus card dari halaman simpan jika di-unsave
                    if (card) card.remove();
                }
                localStorage.setItem('saved', JSON.stringify(saved));
            });
        });
    </script>
</body>

</html>