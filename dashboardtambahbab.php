<?php
include "koneksi.php";

$cerita_id = isset($_GET['cerita_id']) ? (int) $_GET['cerita_id'] : 0;

if ($cerita_id <= 0) {
    header("Location: dashboardbuku.php");
    exit;
}

// Ambil data cerita
$sql = "SELECT * FROM cerita_rakyat WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cerita_id);
$stmt->execute();
$cerita = $stmt->get_result()->fetch_assoc();

if (!$cerita) {
    header("Location: dashboardbuku.php");
    exit;
}

// Hitung nomor bab berikutnya
$sql_count = "SELECT COUNT(*) as total FROM bab WHERE cerita_id = ?";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param("i", $cerita_id);
$stmt_count->execute();
$total = $stmt_count->get_result()->fetch_assoc()['total'];
$nomor_bab_baru = $total + 1;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Bab Baru</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #F6F7F8;
            color: #334155;
        }

        .container {
            display: flex;
            min-height: 100vh;
            padding-left: 60px;
        }

        .navbar {
            width: 60px;
            height: 100vh;
            background-color: #fff;
            border-right: 1px solid #E2E8F0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 20px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            box-shadow: 2px 0 8px rgba(30, 60, 100, 0.06);
        }

        .navbar .logo img {
            width: 38px;
        }

        .navbar nav {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            width: 100%;
            padding: 24px 10px 0;
        }

        .navbar nav a {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #000;
            font-size: 17px;
            text-decoration: none;
            transition: background 0.2s;
        }

        .navbar nav a.active {
            background: #000;
            color: #fff;
        }

        .main {
            flex: 1;
            padding: 40px;
            max-width: 800px;
        }

        .breadcrumb {
            font-size: 12px;
            color: #94A3B8;
            margin-bottom: 16px;
        }

        .breadcrumb a {
            color: #6D4A36;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        h1 {
            font-size: 24px;
            font-weight: 700;
            color: #1E293B;
            margin-bottom: 4px;
        }

        .sub-title {
            font-size: 13px;
            color: #64748B;
            margin-bottom: 28px;
        }

        .cerita-info {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 13px;
            color: #475569;
            margin-bottom: 24px;
        }

        .cerita-info span {
            font-weight: 600;
            color: #1E293B;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 7px;
            color: #475569;
        }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #CBD5E1;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            background-color: #fff;
            box-sizing: border-box;
            transition: border 0.2s;
            color: #1E293B;
        }

        input:focus {
            outline: none;
            border-color: #6D4A36;
            box-shadow: 0 0 0 3px rgba(109, 74, 54, 0.08);
        }

        textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #CBD5E1;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            background-color: #fff;
            box-sizing: border-box;
            transition: border 0.2s;
            color: #1E293B;
            min-height: 400px;
            resize: vertical;
            line-height: 1.75;
        }

        textarea:focus {
            outline: none;
            border-color: #6D4A36;
            box-shadow: 0 0 0 3px rgba(109, 74, 54, 0.08);
        }

        .form-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 28px;
        }

        .batal-button {
            padding: 10px 22px;
            background: #fff;
            border: 1px solid #CBD5E1;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            font-family: 'Inter', sans-serif;
            color: #475569;
            transition: background 0.15s;
        }

        .batal-button:hover {
            background: #F1F5F9;
        }

        .simpan-button {
            padding: 10px 22px;
            background: #6D4A36;
            color: #fff;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            font-family: 'Inter', sans-serif;
            transition: background 0.15s;
        }

        .simpan-button:hover {
            background: #5a3b2a;
        }
    </style>
</head>

<body>
    <div class="container">
        <aside class="navbar">
            <div class="logo">
                <img src="img/logoweb.svg" alt="Logo">
            </div>
        </aside>

        <main class="main">

            <h1>Tambah Bab Baru</h1>
            <p class="sub-title">Tambahkan bab baru untuk cerita "<?= htmlspecialchars($cerita['judul']); ?>".</p>

            <div class="cerita-info">
                Cerita: <span><?= htmlspecialchars($cerita['judul']); ?></span>
                &nbsp;·&nbsp; Bab ke-<span><?= $nomor_bab_baru; ?></span>
            </div>

            <form action="proses_tambahbab.php" method="POST">
                <input type="hidden" name="cerita_id" value="<?= $cerita_id; ?>">
                <input type="hidden" name="nomor_bab" value="<?= $nomor_bab_baru; ?>">

                <div class="form-group">
                    <label for="judul_bab">Judul Bab</label>
                    <input type="text" name="judul_bab" id="judul_bab" placeholder="Contoh: Bab 1. Kerajaan Balanipa"
                        required>
                </div>

                <div class="form-group">
                    <label>Isi Bab</label>
                    <textarea name="isi" placeholder="Mulai menulis isi bab..." required
                        style="min-height: 300px; resize: vertical;"></textarea>
                </div>
                <div class="form-buttons">
                    <button type="button" class="batal-button"
                        onclick="window.location.href='dashboardeditcerita.php?id=<?= $cerita_id; ?>'">
                        Batalkan
                    </button>
                    <button type="submit" name="submit" class="simpan-button">Simpan Bab</button>
                </div>
            </form>
        </main>
    </div>

    <script>
        function fmt(cmd) {
            document.getElementById('editor').focus();
            document.execCommand(cmd, false, null);
        }
    </script>
</body>

</html>