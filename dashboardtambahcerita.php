<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Cerita Rakyat</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
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
        }

        .navbar .logo img {
            width: 45px;
        }

        /* Form Layout */
        form {
            display: flex;
            width: 100%;
            padding: 40px;
            gap: 40px;
            box-sizing: border-box;
        }

        .bagian-kiri {
            flex: 2;
        }

        .bagian-kanan {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        h1 {
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 10px 0;
            color: #1E293B;
        }

        .sub-title {
            font-size: 14px;
            color: #64748B;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #475569;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #CBD5E1;
            border-radius: 8px;
            font-size: 14px;
            background-color: #fff;
            box-sizing: border-box;
            transition: border 0.2s;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: #6D4A36;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        /* Box Bab Cerita */
        .bab-cerita {
            background: #fff;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 20px;
        }

        .bab-list {
            list-style: none;
            padding: 0;
            margin: 0 0 15px 0;
        }

        .bab-item {
            padding: 12px;
            background-color: #F8FAFC;
            border-radius: 8px;
            font-size: 14px;
            color: #1E293B;
            border: 1px solid #F1F5F9;
        }

        .btn-add-bab {
            width: 100%;
            padding: 10px;
            background-color: #E2E8F0;
            border: none;
            border-radius: 8px;
            color: #64748B;
            font-weight: 600;
            cursor: not-allowed;
        }

        .sampul-cerita {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 2px dashed #CBD5E1;
            border-radius: 12px;
            padding: 40px 20px;
            cursor: pointer;
            background-color: #fff;
            text-align: center;
            transition: all 0.3s ease;
        }

        .sampul-cerita:hover {
            background-color: #F8FAFC;
            border-color: #94A3B8;
        }

        .sampul-cerita p {
            margin: 0;
            font-size: 14px;
        }

        /* Buttons */
        .form-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 20px;
        }

        .batal-button {
            padding: 12px 24px;
            background: #fff;
            border: 1px solid #CBD5E1;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
        }

        .tambah-button {
            padding: 12px 24px;
            background: #6D4A36;
            color: #fff;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
        }

        @media (max-width: 900px) {
            form {
                flex-direction: column;
            }

            .container {
                flex-direction: column;
            }

            .navbar {
                width: 100%;
                height: auto;
                padding: 10px;
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

        <form action="proses_tambah.php" method="POST" enctype="multipart/form-data">
            <div class="bagian-kiri">
                <h1>Tambah Cerita Rakyat</h1>
                <p class="sub-title">Isi detail cerita rakyat mulai dari judul, sinopsis, isi cerita, dan terakhir
                    sampul cerita rakyat.</p>

                <div class="form-group">
                    <label for="judul">Judul</label>
                    <input type="text" name="judul" id="judul" placeholder="Contoh: To Dilaling" required>
                </div>

                <div class="form-group">
                    <label for="asalDaerah">Asal Daerah Cerita Rakyat</label>
                    <input type="text" name="asal_daerah" id="asalDaerah" placeholder="Isi asal daerah..." required>
                </div>

                <div class="form-group">
                    <label for="sinopsis">Sinopsis</label>
                    <textarea name="sinopsis" id="sinopsis" placeholder="Inti cerita..."></textarea>
                </div>

                <div class="form-group">
                    <label>Isi Cerita Rakyat</label>
                    <textarea name="isi_cerita" placeholder="Mulai menulis cerita..." required></textarea>
                </div>
            </div>

            <div class="bagian-kanan">
                <div class="form-group">
                    <label for="nama_bab">Bab Cerita Rakyat</label>
                    <input type="text" name="nama_bab" id="nama_bab" placeholder="Contoh: Awal Mula Kerajaan"
                        value="Bab 1" required>
                </div>

                <div class="form-group">
                    <label>Sampul Cerita Rakyat</label>
                    <input type="file" name="sampul" id="file-upload" hidden accept=".png,.jpg,.jpeg,.svg"
                        onchange="previewSampul(this)">
                    <label for="file-upload" class="sampul-cerita" id="label-upload">
                        <img src="img/tambah.svg" alt="Upload Icon" id="upload-icon"
                            style="width: 40px; margin-bottom: 10px; opacity: 0.5;">
                        <img id="preview-img" src="" alt="Preview"
                            style="display:none; width: 100%; max-height: 180px; object-fit: cover; border-radius: 8px; margin-bottom: 10px;">
                        <p id="file-name" style="font-weight: 500; color: #475569;">Klik untuk Upload file</p>
                        <p id="file-type-hint" style="font-size: 12px; color: #94A3B8;">PNG, JPG, JPEG, SVG</p>
                    </label>
                </div>

                <div class="form-buttons">
                    <button type="button" class="batal-button" onclick="window.history.back()">Batalkan</button>
                    <button type="submit" name="submit" class="tambah-button">+ Tambah Cerita</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function previewSampul(input) {
            const fileName = document.getElementById('file-name');
            const previewImg = document.getElementById('preview-img');
            const uploadIcon = document.getElementById('upload-icon');
            const hint = document.getElementById('file-type-hint');

            if (input.files && input.files[0]) {
                const file = input.files[0];

                // Tampilkan nama file
                fileName.innerText = file.name;
                fileName.style.color = "#6D4A36";
                hint.style.display = "none";

                // Tampilkan preview gambar
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewImg.style.display = "block";
                    uploadIcon.style.display = "none";
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>

</html>