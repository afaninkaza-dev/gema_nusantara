<?php
include "koneksi.php";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: dashboardbuku.php");
    exit;
}

// Ambil data cerita
$sql = "SELECT * FROM cerita_rakyat WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$cerita = $result->fetch_assoc();

if (!$cerita) {
    header("Location: dashboardbuku.php");
    exit;
}

// Ambil daftar bab
$sql_bab = "SELECT * FROM bab WHERE cerita_id = ? ORDER BY id ASC";
$stmt_bab = $conn->prepare($sql_bab);
$stmt_bab->bind_param("i", $id);
$stmt_bab->execute();
$result_bab = $stmt_bab->get_result();
$bab_list = $result_bab->fetch_all(MYSQLI_ASSOC);
$bab_json = json_encode($bab_list);

// Ambil isi bab pertama
$sql_isi = "SELECT isi FROM bab WHERE cerita_id = ? ORDER BY id ASC LIMIT 1";
$stmt_isi = $conn->prepare($sql_isi);
$stmt_isi->bind_param("i", $id);
$stmt_isi->execute();
$bab_pertama = $stmt_isi->get_result()->fetch_assoc();
$isi_bab1 = $bab_pertama['isi'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Cerita Rakyat</title>
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

        /* SIDEBAR */
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

        /* FORM LAYOUT */
        form {
            display: flex;
            width: 100%;
            padding: 40px;
            gap: 32px;
            box-sizing: border-box;
            align-items: flex-start;
        }

        .bagian-kiri {
            flex: 2;
        }

        .bagian-kanan {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 20px;
            position: sticky;
            top: 20px;
        }

        h1 {
            font-size: 26px;
            font-weight: 700;
            margin: 0 0 6px 0;
            color: #1E293B;
        }

        .sub-title {
            font-size: 13px;
            color: #64748B;
            margin-bottom: 28px;
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
        textarea {
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

        input:focus,
        textarea:focus {
            outline: none;
            border-color: #6D4A36;
            box-shadow: 0 0 0 3px rgba(109, 74, 54, 0.08);
        }

        textarea {
            min-height: 110px;
            resize: vertical;
            line-height: 1.6;
        }

        /* RICH TEXT TOOLBAR */
        .editor-wrapper {
            border: 1px solid #CBD5E1;
            border-radius: 8px;
            background: #fff;
            overflow: hidden;
        }

        .editor-toolbar {
            display: flex;
            align-items: center;
            gap: 2px;
            padding: 8px 10px;
            border-bottom: 1px solid #E2E8F0;
            background: #F8FAFC;
        }

        .editor-toolbar button {
            width: 30px;
            height: 28px;
            border: none;
            background: transparent;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 700;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.15s, color 0.15s;
        }

        .editor-toolbar button:hover {
            background: #E2E8F0;
            color: #1E293B;
        }

        .editor-toolbar .divider {
            width: 1px;
            height: 20px;
            background: #E2E8F0;
            margin: 0 4px;
        }

        .editor-content {
            min-height: 220px;
            padding: 14px 16px;
            font-size: 14px;
            line-height: 1.75;
            color: #1E293B;
            outline: none;
        }

        .editor-content:focus {
            box-shadow: none;
        }

        /* Textarea tersembunyi untuk submit */
        #isi_cerita_hidden {
            display: none;
        }

        /* BAB CERITA */
        .card-box {
            background: #fff;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 18px;
        }

        .card-box-title {
            font-size: 13px;
            font-weight: 600;
            color: #1E293B;
            margin-bottom: 12px;
        }

        .bab-list {
            list-style: none;
            padding: 0;
            margin: 0 0 12px 0;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .bab-item {
            padding: 10px 14px;
            background-color: #F8FAFC;
            border-radius: 8px;
            font-size: 13px;
            color: #1E293B;
            border: 1px solid #F1F5F9;
            cursor: pointer;
            transition: background 0.15s, border-color 0.15s;
        }

        .bab-item:hover {
            background: #EFF6FF;
            border-color: #BFDBFE;
        }

        .bab-item.active {
            background: #E0E7FF;
            border-color: #A5B4FC;
            font-weight: 500;
        }

        .btn-add-bab {
            width: 100%;
            padding: 10px;
            background: #fff;
            border: 1px dashed #CBD5E1;
            border-radius: 8px;
            color: #64748B;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.15s, border-color 0.15s;
            font-family: 'Inter', sans-serif;
        }

        .btn-add-bab:hover {
            background: #F8FAFC;
            border-color: #94A3B8;
            color: #334155;
        }

        /* SAMPUL */
        .sampul-preview {
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #E2E8F0;
            background: #F8FAFC;
            min-height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }

        .sampul-preview img {
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            display: block;
        }

        .sampul-upload {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 2px dashed #CBD5E1;
            border-radius: 10px;
            padding: 22px 16px;
            cursor: pointer;
            background-color: #fff;
            text-align: center;
            transition: all 0.2s;
        }

        .sampul-upload:hover {
            background: #F8FAFC;
            border-color: #94A3B8;
        }

        .sampul-upload i {
            font-size: 24px;
            color: #94A3B8;
            margin-bottom: 8px;
        }

        .sampul-upload p {
            margin: 0;
            font-size: 13px;
            color: #64748B;
        }

        .sampul-upload .hint {
            font-size: 11px;
            color: #94A3B8;
            margin-top: 4px;
        }

        /* BUTTONS */
        .form-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 4px;
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

        /* SUCCESS / ERROR ALERT */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #DCFCE7;
            color: #166534;
            border: 1px solid #BBF7D0;
        }

        .alert-error {
            background: #FEE2E2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }

        @media (max-width: 900px) {
            form {
                flex-direction: column;
                padding: 24px 20px;
            }

            .bagian-kanan {
                position: static;
            }
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

        <form action="proses_edit.php" method="POST" enctype="multipart/form-data" onsubmit="syncEditor()">
            <input type="hidden" name="id" value="<?= $cerita['id']; ?>">

            <div class="bagian-kiri">
                <h1>Edit Cerita Rakyat</h1>
                <p class="sub-title">Edit detail cerita rakyat mulai dari judul, sinopsis, isi cerita, dan terakhir
                    sampul cerita rakyat.</p>

                <?php if (isset($_GET['status']) && $_GET['status'] === 'sukses'): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> Cerita berhasil diperbarui!</div>
                <?php elseif (isset($_GET['status']) && $_GET['status'] === 'gagal'): ?>
                    <div class="alert alert-error"><i class="fas fa-times-circle"></i> Gagal memperbarui cerita. Silakan
                        coba lagi.</div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="judul">Judul</label>
                    <input type="text" name="judul" id="judul" value="<?= htmlspecialchars($cerita['judul']); ?>"
                        placeholder="Contoh: To Dilaling" required>
                </div>

                <div class="form-group">
                    <label for="asalDaerah">Asal Daerah Cerita Rakyat</label>
                    <input type="text" name="asal_daerah" id="asalDaerah"
                        value="<?= htmlspecialchars($cerita['asal_daerah']); ?>" placeholder="Isi asal daerah..."
                        required>
                </div>

                <div class="form-group">
                    <label for="sinopsis">Sinopsis</label>
                    <textarea name="sinopsis" id="sinopsis"
                        placeholder="Inti cerita..."><?= htmlspecialchars($cerita['sinopsis']); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Isi Cerita Rakyat</label>
                    <div class="editor-wrapper">
                        <div id="editor" class="editor-content" contenteditable="true"><?= $isi_bab1; ?></div>
                    </div>
                    <textarea name="isi_cerita" id="isi_cerita_hidden" style="display:none;"></textarea>
                </div>
            </div>

            <div class="bagian-kanan">
                <!-- BAB CERITA -->
                <div class="card-box">
                    <p class="card-box-title">Bab Cerita Rakyat</p>
                    <ul class="bab-list">
                        <?php if (!empty($bab_list)): ?>
                            <?php foreach ($bab_list as $index => $bab): ?>
                                <li class="bab-item <?= $index === 0 ? 'active' : ''; ?>" data-id="<?= $bab['id']; ?>"
                                    onclick="loadBab(this, <?= $bab['id']; ?>)">
                                    <?= htmlspecialchars($bab['judul_bab']); ?>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="bab-item">Bab 1. <?= htmlspecialchars($cerita['judul']); ?></li>
                        <?php endif; ?>
                    </ul>
                    <input type="hidden" name="bab_id" id="bab_id_hidden" value="<?= $bab_list[0]['id'] ?? ''; ?>">
                    <button type="button" class="btn-add-bab"
                        onclick="window.location.href='dashboardtambahbab.php?cerita_id=<?= $cerita['id']; ?>'">
                        + Tambah Bab Baru
                    </button>
                </div>

                <!-- SAMPUL -->
                <div class="card-box">
                    <p class="card-box-title">Sampul Cerita Rakyat</p>

                    <?php if (!empty($cerita['sampul'])): ?>
                        <div class="sampul-preview" id="sampul-preview">
                            <img src="buku/<?= htmlspecialchars($cerita['sampul']); ?>"
                                alt="Sampul <?= htmlspecialchars($cerita['judul']); ?>" id="sampul-img">
                        </div>
                        <p style="font-size:11px;color:#94A3B8;margin-bottom:8px;text-align:center;">
                            Klik di bawah untuk ganti sampul
                        </p>
                    <?php else: ?>
                        <div class="sampul-preview" id="sampul-preview" style="display:none;">
                            <img src="" alt="Preview" id="sampul-img">
                        </div>
                    <?php endif; ?>

                    <input type="file" name="sampul" id="file-upload" hidden
                        accept="image/png,image/jpeg,image/svg+xml,.svg" onchange="previewSampul(this)">
                    <label for="file-upload" class="sampul-upload" id="upload-label">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p id="file-name">Klik untuk ganti sampul</p>
                        <p class="hint">PNG, JPG, JPEG, dan SVG</p>
                    </label>
                </div>

                <!-- TOMBOL AKSI -->
                <div class="form-buttons">
                    <button type="button" class="batal-button"
                        onclick="window.location.href='dashboardbuku.php'">Batalkan</button>
                    <button type="submit" name="submit" class="simpan-button">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        // Isi hidden textarea saat halaman pertama load
        document.getElementById('isi_cerita_hidden').value =
            document.getElementById('editor').innerHTML;

        // Update setiap kali editor diketik
        document.getElementById('editor').addEventListener('input', function () {
            document.getElementById('isi_cerita_hidden').value = this.innerHTML;
        });

        // Data semua bab
        const babData = <?= $bab_json; ?>;

        function loadBab(el, babId) {
            // Highlight aktif
            document.querySelectorAll('.bab-item').forEach(b => b.classList.remove('active'));
            el.classList.add('active');

            // Cari data bab yang dipilih
            const bab = babData.find(b => b.id == babId);
            if (bab) {
                document.getElementById('editor').innerHTML = bab.isi || '';
                document.getElementById('bab_id_hidden').value = babId;
            }
        }

        function fmt(cmd, val) {
            document.getElementById('editor').focus();
            document.execCommand(cmd, false, val || null);
        }

        function syncEditor() {
            document.getElementById('isi_cerita_hidden').value =
                document.getElementById('editor').innerHTML;
        }

        function previewSampul(input) {
            const preview = document.getElementById('sampul-preview');
            const img = document.getElementById('sampul-img');
            const label = document.getElementById('file-name');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    img.src = e.target.result;
                    preview.style.display = 'flex';
                    label.textContent = input.files[0].name;
                    label.style.color = '#6D4A36';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>

</html>