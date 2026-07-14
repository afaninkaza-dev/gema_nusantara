<?php
session_start();
include "koneksi.php";

// Parameter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'waktu';
$order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'asc' : 'desc';
$filter = isset($_GET['filter']) ? trim($_GET['filter']) : '';
$perPage = 10;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

$allowed_sort = ['id', 'judul', 'asal_daerah', 'waktu', 'dibuat_oleh'];
if (!in_array($sort, $allowed_sort))
    $sort = 'waktu';

// WHERE clause
$where = "WHERE 1=1";
$params = [];
$types = '';
if ($search !== '') {
    $where .= " AND (cr.judul LIKE ? OR cr.asal_daerah LIKE ? OR cr.sinopsis LIKE ?)";
    $like = "%$search%";
    $params = array_merge($params, [$like, $like, $like]);
    $types .= 'sss';
}
if ($filter !== '') {
    $where .= " AND cr.asal_daerah = ?";
    $params[] = $filter;
    $types .= 's';
}

// Total
$sql_count = "SELECT COUNT(*) AS total FROM cerita_rakyat cr LEFT JOIN user u ON cr.dibuat_oleh = u.id $where";
$stmt_count = $conn->prepare($sql_count);
if ($types)
    $stmt_count->bind_param($types, ...$params);
$stmt_count->execute();
$totalData = $stmt_count->get_result()->fetch_assoc()['total'];
$totalPages = max(1, ceil($totalData / $perPage));

// Data
$sql = "SELECT cr.*, u.nama AS nama_pembuat FROM cerita_rakyat cr LEFT JOIN user u ON cr.dibuat_oleh = u.id $where ORDER BY cr.$sort $order LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$all_params = array_merge($params, [$perPage, $offset]);
$all_types = $types . 'ii';
$stmt->bind_param($all_types, ...$all_params);
$stmt->execute();
$result = $stmt->get_result();

// Filter options (asal daerah)
$filter_result = $conn->query("SELECT DISTINCT asal_daerah FROM cerita_rakyat ORDER BY asal_daerah ASC");

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
    return '?' . http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Cerita Rakyat</title>
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
            background: #f7f7f7;
            display: flex;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .main-header h1 {
            font-size: 22px;
            font-weight: 600;
            color: #1e293b;
        }

        /* Toolbar */
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
            transition: border .2s;
        }

        .search-wrap input:focus {
            border-color: #6D4A36;
        }

        .search-wrap button {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            background: #000;
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

        .filter-select {
            padding: 9px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 20px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            outline: none;
            background: #fff;
            cursor: pointer;
            transition: border .2s;
        }

        .filter-select:focus {
            border-color: #6D4A36;
        }

        .btn-tambah {
            padding: 9px 18px;
            background: #000;
            color: #fff;
            border: none;
            border-radius: 18px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            white-space: nowrap;
            margin-left: auto;
        }

        .btn-tambah:hover {
            background: #333;
        }

        /* Table */
        .user-table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 2px 5px rgba(0, 0, 0, .1);
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
        }

        .user-table th,
        .user-table td {
            padding: 11px 13px;
            text-align: left;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }

        .user-table th {
            font-weight: 600;
            color: #555;
            white-space: nowrap;
            background: #fafafa;
        }

        .user-table th a {
            color: #555;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .user-table th a:hover {
            color: #000;
        }

        .user-table tbody tr:hover {
            background: #f9f9f9;
        }

        .sampul-cell img {
            width: 52px;
            height: 72px;
            object-fit: cover;
            border-radius: 4px;
        }

        .sinopsis-cell {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            max-width: 300px;
            color: #475569;
        }

        .aksi-cell a {
            color: #555;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 6px;
            transition: background .15s, color .15s;
            text-decoration: none;
        }

        .aksi-cell a:hover {
            background: #f1f5f9;
            color: #000;
        }

        .aksi-cell a.hapus:hover {
            background: #fef2f2;
            color: #ef4444;
        }

        /* Pagination */
        .pagination {
            display: flex;
            align-items: center;
            gap: 4px;
            margin-top: 20px;
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
            background: #e2e8f0;
        }

        .pagination a.active {
            background: #000;
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

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            background: #f1f5f9;
            color: #475569;
        }
    </style>
</head>

<body>
    <aside class="sidebar">
        <div class="logo"><img src="img/logoweb.svg" alt="Logo"></div>
        <nav>
            <a href="dashboardhome.php" data-tip="Dashboard"><i class="fas fa-house"></i></a>
            <a href="dashboardbuku.php" data-tip="Cerita Rakyat" class="active"><i class="fas fa-book-open"></i></a>
            <a href="dashboardwebadmin.php" data-tip="Pengguna"><i class="fas fa-users"></i></a>
            <a href="dashboardadminsaran.php" data-tip="Saran"><i class="fas fa-comment-dots"></i></a>
        </nav>
        <div class="spacer"></div>
    </aside>

    <main class="main-content">
        <div class="main-header">
            <h1>Manajemen Cerita Rakyat</h1>
        </div>

        <form method="GET" action="">
            <div class="toolbar">
                <div class="search-wrap">
                    <input type="text" name="search" placeholder="Cari judul, daerah, sinopsis..."
                        value="<?= htmlspecialchars($search) ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </div>
                <select name="filter" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Daerah</option>
                    <?php while ($f = $filter_result->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($f['asal_daerah']) ?>" <?= $filter === $f['asal_daerah'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($f['asal_daerah']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                <input type="hidden" name="order" value="<?= htmlspecialchars($order) ?>">
                <button type="button" class="btn-tambah" onclick="window.location.href='dashboardtambahcerita.php'">+
                    Tambah Cerita</button>
            </div>
        </form>

        <table class="user-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th><a href="<?= sortUrl('judul') ?>">Judul <?= sortIcon('judul') ?></a></th>
                    <th><a href="<?= sortUrl('asal_daerah') ?>">Asal Daerah <?= sortIcon('asal_daerah') ?></a></th>
                    <th>Sinopsis</th>
                    <th>Sampul</th>
                    <th><a href="<?= sortUrl('waktu') ?>">Waktu <?= sortIcon('waktu') ?></a></th>
                    <th><a href="<?= sortUrl('dibuat_oleh') ?>">Dibuat Oleh <?= sortIcon('dibuat_oleh') ?></a></th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = $offset + 1;
                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['judul']) ?></td>
                            <td><span class="badge"><?= htmlspecialchars($row['asal_daerah']) ?></span></td>
                            <td>
                                <div class="sinopsis-cell"><?= htmlspecialchars($row['sinopsis']) ?></div>
                            </td>
                            <td class="sampul-cell"><img src="buku/<?= htmlspecialchars($row['sampul']) ?>" alt="sampul"></td>
                            <td><?= date('d/m/Y', strtotime($row['waktu'])) ?></td>
                            <td><span class="badge" style="background:#EFF6FF;color:#1D4ED8;">Admin</span></td>
                            <td class="aksi-cell">
                                <a href="dashboardeditcerita.php?id=<?= $row['id'] ?>" title="Edit"><i
                                        class="fas fa-pen"></i></a>
                                <a href="dashboardhapuscerita.php?id=<?= $row['id'] ?>" class="hapus" title="Hapus"
                                    onclick="return confirm('Yakin hapus cerita ini?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endwhile;
                else: ?>
                    <tr>
                        <td colspan="8" style="text-align:center;padding:32px;color:#94a3b8;">Tidak ada data ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

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
    </main>
</body>

</html>