<?php
session_start();
include "koneksi.php";

// ── Total Pengguna ──
$total_user = $conn->query("SELECT COUNT(*) AS total FROM user")->fetch_assoc()['total'];

// Pengguna baru bulan ini
$user_baru = $conn->query("SELECT COUNT(*) AS total FROM user WHERE MONTH(waktu)=MONTH(NOW()) AND YEAR(waktu)=YEAR(NOW())")->fetch_assoc()['total'];

// ── Total Cerita ──
$total_cerita = $conn->query("SELECT COUNT(*) AS total FROM cerita_rakyat")->fetch_assoc()['total'];

// Cerita baru bulan ini
$cerita_baru = $conn->query("SELECT COUNT(*) AS total FROM cerita_rakyat WHERE MONTH(waktu)=MONTH(NOW()) AND YEAR(waktu)=YEAR(NOW())")->fetch_assoc()['total'];

// ── Pengunjung Harian (dari koleksi, 7 hari ini) ──
// Senin s.d. Minggu minggu berjalan
$hari_labels = [];
$hari_data   = [];
$nama_hari   = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];
for ($i = 0; $i < 7; $i++) {
    $tanggal = date('Y-m-d', strtotime("monday this week +$i days"));
    $hari_labels[] = $nama_hari[$i];
    $r = $conn->prepare("SELECT COUNT(*) AS total FROM koleksi WHERE DATE(waktu)=?");
    $r->bind_param("s", $tanggal);
    $r->execute();
    $hari_data[] = (int)$r->get_result()->fetch_assoc()['total'];
}

// Total pengunjung bulan ini vs bulan lalu (dari koleksi)
$pengunjung_bulan_ini  = $conn->query("SELECT COUNT(*) AS total FROM koleksi WHERE MONTH(waktu)=MONTH(NOW()) AND YEAR(waktu)=YEAR(NOW())")->fetch_assoc()['total'];
$pengunjung_bulan_lalu = $conn->query("SELECT COUNT(*) AS total FROM koleksi WHERE MONTH(waktu)=MONTH(NOW())-1 AND YEAR(waktu)=YEAR(NOW())")->fetch_assoc()['total'];
$persen_pengunjung = $pengunjung_bulan_lalu > 0
    ? round((($pengunjung_bulan_ini - $pengunjung_bulan_lalu) / $pengunjung_bulan_lalu) * 100, 1)
    : 0;

// ── Cerita Terpopuler bulan ini (dari riwayat_membaca) ──
$sql_populer = "SELECT cr.judul, COUNT(rm.id) AS total 
                FROM riwayat_membaca rm 
                JOIN cerita_rakyat cr ON rm.cerita_id = cr.id
                WHERE MONTH(rm.waktu)=MONTH(NOW()) AND YEAR(rm.waktu)=YEAR(NOW())
                GROUP BY rm.cerita_id ORDER BY total DESC LIMIT 4";
$res_populer = $conn->query($sql_populer);
$populer_labels = [];
$populer_data   = [];
while ($row = $res_populer->fetch_assoc()) {
    $populer_labels[] = $row['judul'];
    $populer_data[]   = (int)$row['total'];
}
// fallback jika kosong
if (empty($populer_data)) {
    $populer_labels = ['Belum ada data'];
    $populer_data   = [1];
}

// ── Rating Tertinggi (dari ulasan) ──
$sql_rating = "SELECT cr.judul, ROUND(AVG(u.rating),2) AS avg_rating, COUNT(u.id) AS jml
               FROM ulasan u
               JOIN cerita_rakyat cr ON u.cerita_id = cr.id
               GROUP BY u.cerita_id HAVING jml >= 1
               ORDER BY avg_rating DESC LIMIT 4";
$res_rating = $conn->query($sql_rating);
$rating_labels = [];
$rating_data   = [];
while ($row = $res_rating->fetch_assoc()) {
    $rating_labels[] = $row['judul'];
    $rating_data[]   = (float)$row['avg_rating'];
}
// fallback
if (empty($rating_data)) {
    $rating_labels = ['Belum ada data'];
    $rating_data   = [0];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gema Nusantara - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-datalabels/2.2.0/chartjs-plugin-datalabels.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Poppins',sans-serif; background:#F6F7F8; min-height:100vh; display:flex; }

        .sidebar {
            width:68px; min-height:100vh; background:#fff; border-right:1px solid #e2e8f0;
            display:flex; flex-direction:column; align-items:center; padding:24px 0 20px;
            position:fixed; left:0; top:0; z-index:100; box-shadow:2px 0 8px rgba(30,60,100,.06);
        }
        .sidebar .logo { width:40px; height:40px; margin-bottom:32px; display:flex; align-items:center; justify-content:center; }
        .sidebar .logo img { width:38px; height:38px; object-fit:contain; }
        .sidebar nav { display:flex; flex-direction:column; align-items:center; gap:6px; width:100%; padding:0 10px; }
        .sidebar nav a {
            width:44px; height:44px; border-radius:12px; display:flex; align-items:center;
            justify-content:center; color:#000; font-size:17px; text-decoration:none;
            transition:background .2s,color .2s; position:relative;
        }
        .sidebar nav a.active { background:#000; color:#fff; box-shadow:0 4px 14px rgba(30,41,59,.28); }
        .sidebar nav a::after {
            content:attr(data-tip); position:absolute; left:54px; background:#000; color:#fff;
            font-size:11px; padding:4px 10px; border-radius:6px; white-space:nowrap;
            pointer-events:none; opacity:0; transition:opacity .18s; z-index:200;
        }
        .sidebar nav a:hover::after { opacity:1; }
        .sidebar .spacer { flex:1; }

        .main { margin-left:68px; flex:1; padding:24px 28px 28px; }

        .topbar { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; }
        .topbar h1 { font-size:22px; font-weight:600; color:#1e293b; }

        .top-row { display:grid; grid-template-columns:1.2fr 1fr; gap:16px; margin-bottom:16px; align-items:start; }
        .stats-group { display:flex; gap:12px; }

        .stat-card {
            background:#fff; border-radius:12px; border:1px solid #e2e8f0;
            padding:20px 10px; flex:1; display:flex; flex-direction:column;
            align-items:center; text-align:center;
        }
        .stat-card .label { font-size:10px; font-weight:600; color:#64748b; margin-bottom:8px; line-height:1.4; }
        .stat-card .icon { width:40px; height:40px; display:flex; align-items:center; justify-content:center; margin-bottom:8px; color:#64748b; font-size:24px; }
        .stat-card .number { font-size:26px; font-weight:700; color:#1e293b; line-height:1; margin-bottom:6px; }
        .stat-card .delta { font-size:9px; font-weight:500; line-height:1.4; color:#64748b; }
        .delta.green { color:#22c55e; }
        .delta.red { color:#ef4444; }

        .pie-card { background:#fff; border-radius:12px; border:1px solid #e2e8f0; padding:18px; }
        .pie-card h2 { font-size:13px; font-weight:600; color:#1e293b; text-align:center; margin-bottom:10px; }
        .pie-canvas-wrap { height:130px; }

        .bottom-row { display:grid; grid-template-columns:1.3fr 1fr; gap:16px; }
        .chart-card { background:#fff; border-radius:12px; border:1px solid #e2e8f0; padding:18px 18px 14px; }
        .chart-card h2 { font-size:13px; font-weight:600; color:#1e293b; text-align:center; margin-bottom:10px; }
        .line-canvas-wrap { height:240px; }
        .bar-canvas-wrap { height:230px; }
    </style>
</head>
<body>
<aside class="sidebar">
    <div class="logo"><img src="img/logoweb.svg" alt="Logo"></div>
    <nav>
        <a href="dashboardhome.php" data-tip="Dashboard" class="active"><i class="fas fa-house"></i></a>
        <a href="dashboardbuku.php" data-tip="Cerita Rakyat"><i class="fas fa-book-open"></i></a>
        <a href="dashboardwebadmin.php" data-tip="Pengguna"><i class="fas fa-users"></i></a>
        <a href="dashboardadminsaran.php" data-tip="Saran"><i class="fas fa-comment-dots"></i></a>
    </nav>
    <div class="spacer"></div>
</aside>

<main class="main">
    <div class="topbar">
        <h1>Dashboard Utama</h1>
        <img src="img/profile.svg" alt="profile">
    </div>

    <div class="top-row">
        <div class="stats-group">
            <div class="stat-card">
                <p class="label">Total Pengguna</p>
                <div class="icon"><i class="fas fa-users"></i></div>
                <p class="number"><?= number_format($total_user) ?></p>
                <p class="delta green">+<?= $user_baru ?> pengguna baru bulan ini</p>
            </div>
            <div class="stat-card">
                <p class="label">Total Cerita Rakyat</p>
                <div class="icon"><i class="fas fa-book-open"></i></div>
                <p class="number"><?= number_format($total_cerita) ?></p>
                <p class="delta green">+<?= $cerita_baru ?> cerita baru bulan ini</p>
            </div>
            <div class="stat-card">
                <p class="label">Pengunjung<br>Bulan Ini</p>
                <div class="icon"><i class="fas fa-chart-line"></i></div>
                <p class="number"><?= number_format($pengunjung_bulan_ini) ?></p>
                <p class="delta <?= $persen_pengunjung >= 0 ? 'green' : 'red' ?>">
                    <?= $persen_pengunjung >= 0 ? '+' : '' ?><?= $persen_pengunjung ?>%
                    dibanding bulan lalu
                </p>
            </div>
        </div>

        <div class="pie-card">
            <h2>Cerita Terpopuler Bulan Ini</h2>
            <div class="pie-canvas-wrap">
                <canvas id="grafikTerpopuler"></canvas>
            </div>
        </div>
    </div>

    <div class="bottom-row">
        <div class="chart-card">
            <h2>Jumlah Pengunjung Harian (Minggu Ini)</h2>
            <div class="line-canvas-wrap">
                <canvas id="grafikPengunjung"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <div class="bar-canvas-wrap">
                <canvas id="grafikRating"></canvas>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    Chart.register(ChartDataLabels);

    // ── Line chart ──
    const ctx1 = document.getElementById('grafikPengunjung').getContext('2d');
    const grad = ctx1.createLinearGradient(0, 0, 0, 240);
    grad.addColorStop(0, 'rgba(95,169,95,0.45)');
    grad.addColorStop(1, 'rgba(255,255,255,0.0)');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: <?= json_encode($hari_labels) ?>,
            datasets: [{
                label: 'Pengunjung',
                data: <?= json_encode($hari_data) ?>,
                fill: true,
                backgroundColor: grad,
                borderColor: 'rgba(95,169,95,0.9)',
                pointBackgroundColor: '#fff',
                pointBorderColor: 'rgba(95,169,95,1)',
                pointBorderWidth: 2,
                pointRadius: 5,
                tension: 0.35,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                datalabels: { display: false },
                legend: { display: true, position: 'bottom', labels: { font: { size:11, family:'Poppins' }, boxWidth:14, padding:10 } }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize:50, font:{ family:'Poppins', size:11 } }, grid: { color:'rgba(0,0,0,0.05)' } },
                x: { ticks: { font:{ family:'Poppins', size:11 } }, grid: { display:false } }
            }
        }
    });

    // ── Pie chart ──
    const ctx2 = document.getElementById('grafikTerpopuler').getContext('2d');
    const totalPie = <?= json_encode(array_sum($populer_data)) ?>;
    new Chart(ctx2, {
        type: 'pie',
        data: {
            labels: <?= json_encode($populer_labels) ?>,
            datasets: [{
                data: <?= json_encode($populer_data) ?>,
                backgroundColor: ['#8979FF','#FF928A','#3CC3DF','#FFAE4C'],
                borderWidth: 2, borderColor: '#fff'
            }]
        },
        options: {
            maintainAspectRatio: false, responsive: true,
            plugins: {
                legend: {
                    position: 'right',
                    labels: { font:{ size:9, family:'Poppins' }, boxWidth:10, padding:8, color:'#475569', usePointStyle:true }
                },
                datalabels: {
                    color: '#fff',
                    font: { weight:'700', size:10, family:'Poppins' },
                    formatter: (v) => totalPie > 0 ? ((v/totalPie)*100).toFixed(0)+'%' : ''
                }
            },
            scales: { y:{ display:false }, x:{ display:false } }
        }
    });

    // ── Bar chart ──
    const ctx3 = document.getElementById('grafikRating').getContext('2d');
    new Chart(ctx3, {
        type: 'bar',
        data: {
            labels: <?= json_encode($rating_labels) ?>,
            datasets: [{
                label: 'Rata-rata Rating',
                data: <?= json_encode($rating_data) ?>,
                backgroundColor: 'rgba(88,129,87,0.85)',
                borderWidth: 0, borderRadius: 4
            }]
        },
        options: {
            maintainAspectRatio: false, responsive: true,
            plugins: {
                title: { display:true, text:'Rating Tertinggi Cerita Rakyat', font:{ size:13, weight:'600', family:'Poppins' }, color:'#1e293b', padding:{ bottom:10 } },
                legend: { display:true, position:'bottom', labels:{ font:{ size:10, family:'Poppins' }, boxWidth:12, padding:8 } },
                datalabels: {
                    anchor:'center', align:'center', color:'#fff',
                    font:{ weight:'700', size:11, family:'Poppins' },
                    formatter: v => v > 0 ? v.toFixed(2) : ''
                }
            },
            scales: {
                y: { min:0, max:5, ticks:{ stepSize:1, callback: v => v.toFixed(0), font:{ family:'Poppins', size:11 } }, grid:{ color:'rgba(0,0,0,0.05)' } },
                x: { ticks:{ font:{ family:'Poppins', size:10 } }, grid:{ display:false } }
            }
        }
    });
});
</script>
</body>
</html>