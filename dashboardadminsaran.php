<?php
session_start();
include "koneksi.php";

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_key = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'asc' : 'desc';
$perPage = 10;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

// Map sort key ke nama kolom lengkap (dengan prefix tabel)
$sort_map = [
    'id'      => 'saran.id',
    'nama'    => 'user.nama',
    'tanggal' => 'saran.waktu',
];
$sort = isset($sort_map[$sort_key]) ? $sort_map[$sort_key] : 'saran.id';

// Hapus saran
if (isset($_GET['hapus'])) {
    $hapusId = (int) $_GET['hapus'];
    $conn->query("DELETE FROM saran WHERE id = $hapusId");
    $p = array_diff_key($_GET, ['hapus' => '']);
    header("Location: dashboardadminsaran.php?" . http_build_query($p));
    exit;
}

$where = "WHERE 1=1";
$params = [];
$types = '';
if ($search !== '') {
    $where .= " AND (user.nama LIKE ? OR saran.isi_saran LIKE ?)";
    $like = "%$search%";
    $params = [$like, $like];
    $types = 'ss';
}

// Query COUNT dengan JOIN
$stmt_count = $conn->prepare(
    "SELECT COUNT(*) AS total 
     FROM saran 
     LEFT JOIN user ON saran.user_id = user.id 
     $where"
);
if ($types)
    $stmt_count->bind_param($types, ...$params);
$stmt_count->execute();
$totalData = $stmt_count->get_result()->fetch_assoc()['total'];
$totalPages = max(1, ceil($totalData / $perPage));

// Query data dengan JOIN
$stmt = $conn->prepare(
    "SELECT saran.id, saran.user_id, saran.isi_saran, saran.waktu, user.nama 
     FROM saran 
     LEFT JOIN user ON saran.user_id = user.id 
     $where 
     ORDER BY $sort $order 
     LIMIT ? OFFSET ?"
);
$all_params = array_merge($params, [$perPage, $offset]);
$all_types = $types . 'ii';
$stmt->bind_param($all_types, ...$all_params);
$stmt->execute();
$result = $stmt->get_result();

function sortUrl($col)
{
    $o = (isset($_GET['sort']) && $_GET['sort'] == $col && (!isset($_GET['order']) || $_GET['order'] == 'desc')) ? 'asc' : 'desc';
    $p = array_merge($_GET, ['sort' => $col, 'order' => $o, 'page' => 1]);
    return '?' . http_build_query($p);
}
function sortIcon($col)
{
    if (!isset($_GET['sort']) || $_GET['sort'] !== $col)
        return '<i class="fas fa-sort" style="opacity:.3"></i>';
    return $_GET['order'] === 'asc' ? '<i class="fas fa-sort-up"></i>' : '<i class="fas fa-sort-down"></i>';
}
function pageUrl($p)
{
    $params = array_merge($_GET, ['page' => $p]);
    unset($params['hapus']);
    return '?' . http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Saran</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #F7F7F7;
            display: flex;
            color: #000;
        }

        .sidebar {
            width: 68px;
            min-height: 100vh;
            background: #fff;
            border-right: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px 0 20px;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 100;
            box-shadow: 2px 0 8px rgba(30, 60, 100, .06);
        }

        .sidebar .logo {
            width: 40px;
            height: 40px;
            margin-bottom: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar .logo img {
            width: 38px;
            height: 38px;
            object-fit: contain;
        }

        .sidebar nav {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            width: 100%;
            padding: 0 10px;
        }

        .sidebar nav a {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #000;
            font-size: 17px;
            text-decoration: none;
            transition: background .2s, color .2s;
            position: relative;
        }

        .sidebar nav a.active {
            background: #000;
            color: #fff;
            box-shadow: 0 4px 14px rgba(30, 41, 59, .28);
        }

        .sidebar nav a:hover:not(.active) {
            background: #ffffff;
            color: #000000;
        }

        .sidebar nav a::after {
            content: attr(data-tip);
            position: absolute;
            left: 54px;
            background: #000;
            color: #fff;
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 6px;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity .18s;
            z-index: 200;
        }

        .sidebar nav a:hover::after {
            opacity: 1;
        }

        .sidebar .spacer {
            flex: 1;
        }

        .main-content {
            margin-left: 68px;
            flex: 1;
            padding: 28px 32px;
        }

        .main-header {
            margin-bottom: 20px;
        }

        .main-header h1 {
            font-size: 22px;
            font-weight: 500;
            color: #1e293b;
        }

        .toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .search-wrap {
            position: relative;
            flex: 1;
            min-width: 200px;
            max-width: 320px;
        }

        .search-wrap input {
            width: 100%;
            padding: 9px 38px 9px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 20px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            outline: none;
        }

        .search-wrap input:focus {
            border-color: #000000;
        }

        .search-wrap button {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            background: #000000;
            color: #fff;
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dashboard {
            background: #fff;
            padding: 24px 28px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .dashboard h2 {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
        }

        .sort-bar {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .sort-bar span {
            font-size: 12px;
            color: #94a3b8;
        }

        .sort-bar a {
            font-size: 12px;
            padding: 4px 12px;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            color: #475569;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: background .15s;
        }

        .sort-bar a:hover {
            background: #f1f5f9;
        }

        .sort-bar a.active-sort {
            background: #ffffff;
            color: #000000;
            border-color: #e7e4e4;
        }

        .saran-table {
            width: 100%;
            border-collapse: collapse;
        }

        .saran-table th,
        .saran-table td {
            padding: 11px 13px;
            text-align: left;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
        }

        .saran-table th {
            font-weight: 600;
            color: #555;
            background: #fafafa;
            white-space: nowrap;
        }

        .saran-table th a {
            color: #555;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .saran-table th a:hover {
            color: #000;
        }

        .saran-table tbody tr:hover {
            background: #fafafa;
        }

        .isi-cell {
            color: #475569;
            max-width: 500px;
        }

        .nama-fallback {
            color: #94a3b8;
            font-style: italic;
        }

        .aksi-cell a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            color: #ef4444;
            background: #fef2f2;
            text-decoration: none;
            font-size: 14px;
            transition: background .2s, color .2s;
        }

        .aksi-cell a:hover {
            background: #ef4444;
            color: #fff;
        }

        .empty-state {
            text-align: center;
            padding: 48px 0;
            color: #94a3b8;
            font-size: 15px;
        }

        .empty-state i {
            font-size: 40px;
            margin-bottom: 12px;
            display: block;
            color: #cbd5e1;
        }

        .pagination {
            display: flex;
            align-items: center;
            gap: 4px;
            margin-top: 24px;
            flex-wrap: wrap;
        }

        .pagination a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
            padding: 0 10px;
            border-radius: 8px;
            color: #000;
            text-decoration: none;
            font-size: 13px;
            transition: background .15s;
        }

        .pagination a:hover:not(.active):not(.disabled) {
            background: #f1ece9;
            color: #000000;
        }

        .pagination a.active {
            background: #000000;
            color: #fff;
            font-weight: 600;
        }

        .pagination a.disabled {
            color: #cbd5e1;
            pointer-events: none;
        }

        .pagination-info {
            font-size: 12px;
            color: #94a3b8;
            margin-left: 8px;
        }
    </style>
</head>

<body>
    <aside class="sidebar">
        <div class="logo"><img src="img/logoweb.svg" alt="Logo"></div>
        <nav>
            <a href="dashboardhome.php" data-tip="Dashboard"><i class="fas fa-house"></i></a>
            <a href="dashboardbuku.php" data-tip="Cerita Rakyat"><i class="fas fa-book-open"></i></a>
            <a href="dashboardwebadmin.php" data-tip="Pengguna"><i class="fas fa-users"></i></a>
            <a href="dashboardadminsaran.php" data-tip="Saran" class="active"><i class="fas fa-comment-dots"></i></a>
        </nav>
        <div class="spacer"></div>
    </aside>

    <main class="main-content">
        <div class="main-header">
            <h1>Dashboard Admin</h1>
        </div>

        <form method="GET" action="">
            <div class="toolbar">
                <div class="search-wrap">
                    <input type="text" name="search" placeholder="Cari nama atau isi saran..."
                        value="<?= htmlspecialchars($search) ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </div>
                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort_key) ?>">
                <input type="hidden" name="order" value="<?= htmlspecialchars($order) ?>">
            </div>
        </form>

        <div class="dashboard">
            <div class="dashboard-header">
                <h2>Daftar Saran (<?= $totalData ?>)</h2>
                <div class="sort-bar">
                    <span>Urutkan:</span>
                    <a href="<?= sortUrl('id') ?>" class="<?= $sort_key === 'id' ? 'active-sort' : '' ?>">Terbaru
                        <?= sortIcon('id') ?></a>
                    <a href="<?= sortUrl('nama') ?>" class="<?= $sort_key === 'nama' ? 'active-sort' : '' ?>">Nama
                        <?= sortIcon('nama') ?></a>
                    <a href="<?= sortUrl('tanggal') ?>" class="<?= $sort_key === 'tanggal' ? 'active-sort' : '' ?>">Tanggal
                        <?= sortIcon('tanggal') ?></a>
                </div>
            </div>

            <?php if ($result && $result->num_rows > 0): ?>
                <table class="saran-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th><a href="<?= sortUrl('nama') ?>">Nama <?= sortIcon('nama') ?></a></th>
                            <th><a href="<?= sortUrl('tanggal') ?>">Tanggal <?= sortIcon('tanggal') ?></a></th>
                            <th>Isi Saran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = $offset + 1;
                        while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <?php if (!empty($row['nama'])): ?>
                                        <?= htmlspecialchars($row['nama']) ?>
                                    <?php else: ?>
                                        <span class="nama-fallback">Pengguna dihapus</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y', strtotime($row['waktu'])) ?></td>
                                <td class="isi-cell">"<?= htmlspecialchars($row['isi_saran']) ?>"</td>
                                <td class="aksi-cell">
                                    <a href="<?= pageUrl($page) ?>&hapus=<?= $row['id'] ?>"
                                        onclick="return confirm('Yakin hapus saran ini?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-comment-slash"></i>
                    Belum ada saran yang masuk.
                </div>
            <?php endif; ?>

            <div class="pagination">
                <a href="<?= pageUrl(1) ?>" class="<?= $page <= 1 ? 'disabled' : '' ?>">&laquo;</a>
                <a href="<?= pageUrl(max(1, $page - 1)) ?>" class="<?= $page <= 1 ? 'disabled' : '' ?>">&#8249;</a>
                <?php
                $range = 2;
                for ($i = 1; $i <= $totalPages; $i++):
                    if ($i === 1 || $i === $totalPages || ($i >= $page - $range && $i <= $page + $range)):
                        ?>
                        <a href="<?= pageUrl($i) ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                    <?php elseif ($i === $page - $range - 1 || $i === $page + $range + 1): ?>
                        <a class="disabled">...</a>
                    <?php endif; endfor; ?>
                <a href="<?= pageUrl(min($totalPages, $page + 1)) ?>"
                    class="<?= $page >= $totalPages ? 'disabled' : '' ?>">&#8250;</a>
                <a href="<?= pageUrl($totalPages) ?>" class="<?= $page >= $totalPages ? 'disabled' : '' ?>">&raquo;</a>
                <span class="pagination-info">Menampilkan <?= $offset + 1 ?>–<?= min($offset + $perPage, $totalData) ?> dari
                    <?= $totalData ?> data</span>
            </div>
        </div>
    </main>
</body>

</html>