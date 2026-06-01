<?php
session_start();
include "koneksi.php";

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'waktu';
$order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'asc' : 'desc';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$perPage = 10;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

$allowed_sort = ['id', 'nama', 'email', 'role', 'waktu'];
if (!in_array($sort, $allowed_sort))
    $sort = 'waktu';

$where = "WHERE 1=1";
$params = [];
$types = '';
if ($search !== '') {
    $where .= " AND (nama LIKE ? OR email LIKE ?)";
    $like = "%$search%";
    $params = array_merge($params, [$like, $like]);
    $types .= 'ss';
}
if ($filter !== '') {
    $where .= " AND role = ?";
    $params[] = $filter;
    $types .= 's';
}

$stmt_count = $conn->prepare("SELECT COUNT(*) AS total FROM user $where");
if ($types)
    $stmt_count->bind_param($types, ...$params);
$stmt_count->execute();
$totalData = $stmt_count->get_result()->fetch_assoc()['total'];
$totalPages = max(1, ceil($totalData / $perPage));

$stmt = $conn->prepare("SELECT * FROM user $where ORDER BY $sort $order LIMIT ? OFFSET ?");
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
    return '?' . http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Akun Pengguna</title>
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
        }

        .main-header h1 {
            font-size: 22px;
            font-weight: 600;
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
        }

        .filter-select:focus {
            border-color: #6D4A36;
        }

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
            background: #fafafa;
            white-space: nowrap;
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

        .aksi-cell a {
            color: #555;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 6px;
            transition: background .15s;
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

        .role-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .role-admin {
            background: #fef3c7;
            color: #92400e;
        }

        .role-user {
            background: #e0f2fe;
            color: #075985;
        }

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
    </style>
</head>

<body>
    <aside class="sidebar">
        <div class="logo"><img src="img/logoweb.svg" alt="Logo"></div>
        <nav>
            <a href="dashboardhome.php" data-tip="Dashboard"><i class="fas fa-house"></i></a>
            <a href="dashboardbuku.php" data-tip="Cerita Rakyat"><i class="fas fa-book-open"></i></a>
            <a href="dashboardwebadmin.php" data-tip="Pengguna" class="active"><i class="fas fa-users"></i></a>
            <a href="dashboardadminsaran.php" data-tip="Saran"><i class="fas fa-comment-dots"></i></a>
        </nav>
        <div class="spacer"></div>
    </aside>

    <main class="main-content">
        <div class="main-header">
            <h1>Manajemen Akun Pengguna</h1>
        </div>

        <form method="GET" action="">
            <div class="toolbar">
                <div class="search-wrap">
                    <input type="text" name="search" placeholder="Cari nama atau email..."
                        value="<?= htmlspecialchars($search) ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </div>
                <select name="filter" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Role</option>
                    <option value="admin" <?= $filter === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="user" <?= $filter === 'user' ? 'selected' : '' ?>>User</option>
                </select>
                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                <input type="hidden" name="order" value="<?= htmlspecialchars($order) ?>">
            </div>
        </form>

        <table class="user-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th><a href="<?= sortUrl('nama') ?>">Nama <?= sortIcon('nama') ?></a></th>
                    <th><a href="<?= sortUrl('email') ?>">Email <?= sortIcon('email') ?></a></th>
                    <th><a href="<?= sortUrl('role') ?>">Role <?= sortIcon('role') ?></a></th>
                    <th><a href="<?= sortUrl('waktu') ?>">Waktu <?= sortIcon('waktu') ?></a></th>
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
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td>
                                <span class="role-badge <?= $row['role'] === 'admin' ? 'role-admin' : 'role-user' ?>">
                                    <?= htmlspecialchars($row['role']) ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y', strtotime($row['waktu'])) ?></td>
                            <td class="aksi-cell">
                                <a href="edit.php?id=<?= $row['id'] ?>" title="Edit"><i class="fas fa-pen"></i></a>
                                <a href="delete.php?id=<?= $row['id'] ?>" class="hapus" title="Hapus"
                                    onclick="return confirm('Yakin hapus akun ini?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endwhile;
                else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center;padding:32px;color:#94a3b8;">Tidak ada data ditemukan.</td>
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