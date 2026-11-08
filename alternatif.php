<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';

// Add coordinate columns for existing installations.
foreach (['latitude', 'longitude'] as $column) {
    $checkColumn = mysqli_query($koneksi, "SHOW COLUMNS FROM alternatif LIKE '$column'");
    if (!$checkColumn) {
        http_response_code(500);
        exit('Gagal memeriksa struktur tabel alternatif: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
    }

    if (mysqli_num_rows($checkColumn) === 0) {
        $alter = mysqli_query($koneksi, "ALTER TABLE alternatif ADD COLUMN $column DECIMAL(10,7) NULL");
        if (!$alter) {
            http_response_code(500);
            exit('Gagal menambahkan kolom koordinat: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
        }
    }
}

$pesanError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi'])) {
    $input = [];
    foreach (['aksi', 'kode_alternatif', 'nama_kos', 'alamat', 'telepon', 'latitude', 'longitude'] as $field) {
        $value = $_POST[$field] ?? '';
        if (!is_string($value)) {
            $pesanError = 'Format data yang dikirim tidak valid.';
            break;
        }
        $input[$field] = trim($value);
    }

    $aksi = $input['aksi'] ?? '';
    $kode = $input['kode_alternatif'] ?? '';
    $nama = $input['nama_kos'] ?? '';
    $alamat = $input['alamat'] ?? '';
    $telepon = $input['telepon'] ?? '';
    $latitude = $input['latitude'] ?? '';
    $longitude = $input['longitude'] ?? '';

    if ($pesanError === '') {
        if (!in_array($aksi, ['tambah', 'ubah'], true)) {
            $pesanError = 'Permintaan tidak dikenali. Silakan muat ulang halaman.';
        } elseif ($kode === '' || $nama === '') {
            $pesanError = 'Kode alternatif dan nama kos wajib diisi.';
        } elseif (($latitude === '') !== ($longitude === '')) {
            $pesanError = 'Isi latitude dan longitude sekaligus, atau kosongkan keduanya.';
        } elseif ($latitude !== '' && (
            !is_numeric($latitude)
            || !is_numeric($longitude)
            || (float) $latitude < -90
            || (float) $latitude > 90
            || (float) $longitude < -180
            || (float) $longitude > 180
        )) {
            $pesanError = 'Koordinat tidak valid. Latitude harus antara -90 hingga 90 dan longitude -180 hingga 180.';
        } else {
            $latitudeDb = $latitude === '' ? null : $latitude;
            $longitudeDb = $longitude === '' ? null : $longitude;

            if ($aksi === 'ubah') {
                $idAlternatif = filter_var($_POST['id_alternatif'] ?? null, FILTER_VALIDATE_INT);
                if ($idAlternatif === false || $idAlternatif < 1) {
                    $pesanError = 'Data kos yang akan diperbarui tidak valid.';
                } else {
                    $statement = mysqli_prepare(
                        $koneksi,
                        'UPDATE alternatif SET kode_alternatif = ?, nama_kos = ?, alamat = ?, telepon = ?, latitude = ?, longitude = ? WHERE id_alternatif = ?'
                    );
                    if ($statement) {
                        mysqli_stmt_bind_param($statement, 'ssssssi', $kode, $nama, $alamat, $telepon, $latitudeDb, $longitudeDb, $idAlternatif);
                        if (mysqli_stmt_execute($statement)) {
                            mysqli_stmt_close($statement);
                            header('Location: alternatif.php?status=diubah');
                            exit;
                        }
                        $pesanError = 'Gagal memperbarui data kos: ' . mysqli_stmt_error($statement);
                        mysqli_stmt_close($statement);
                    } else {
                        $pesanError = 'Gagal menyiapkan pembaruan data: ' . mysqli_error($koneksi);
                    }
                }
            } else {
                $statement = mysqli_prepare(
                    $koneksi,
                    'INSERT INTO alternatif (kode_alternatif, nama_kos, alamat, telepon, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?)'
                );
                if ($statement) {
                    mysqli_stmt_bind_param($statement, 'ssssss', $kode, $nama, $alamat, $telepon, $latitudeDb, $longitudeDb);
                    if (mysqli_stmt_execute($statement)) {
                        $id_alt_baru = mysqli_insert_id($koneksi);
                        mysqli_stmt_close($statement);
                        // Otomatis isi nilai default 0 di tabel penilaian untuk kriteria yang ada.
                        $q_kriteria = mysqli_query($koneksi, "SELECT id_kriteria FROM kriteria");
                        while ($k = mysqli_fetch_assoc($q_kriteria)) {
                            $id_k = (int) $k['id_kriteria'];
                            mysqli_query($koneksi, "INSERT INTO penilaian (id_alternatif, id_kriteria, nilai) VALUES ($id_alt_baru, $id_k, 0)");
                        }

                        header('Location: alternatif.php?status=ditambah');
                        exit;
                    }
                    $pesanError = 'Gagal menyimpan data kos: ' . mysqli_stmt_error($statement);
                    mysqli_stmt_close($statement);
                } else {
                    $pesanError = 'Gagal menyiapkan penyimpanan data: ' . mysqli_error($koneksi);
                }
            }
        }
    }
}

// --- PROSES HAPUS DATA ---
if (isset($_GET['hapus'])) {
    $id = filter_var($_GET['hapus'], FILTER_VALIDATE_INT);
    if ($id !== false && $id > 0) {
        mysqli_query($koneksi, "DELETE FROM alternatif WHERE id_alternatif = $id");
    }
    header("Location: alternatif.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Alternatif Tempat Kos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="app.css?v=4">
</head>
<body class="container py-4">

    <!-- Navigasi Utama (Cukup 1 baris ini saja) -->
    <nav class="app-nav mb-4">
        <a href="index.php" class="btn btn-outline-primary btn-sm">Beranda</a>
        <a href="alternatif.php" class="btn btn-primary btn-sm">Data Kos</a>
        <a href="kriteria.php" class="btn btn-outline-primary btn-sm">Kriteria & Bobot</a>
        <a href="penilaian.php" class="btn btn-outline-primary btn-sm">Penilaian</a>
        <a href="hitung.php" class="btn btn-success btn-sm">Hasil Rekomendasi</a>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div><h2 class="fw-bold mb-1">Pilihan Tempat Kos</h2><p class="text-muted mb-0">Tambahkan data kos dan koordinatnya agar lokasinya mudah dibuka di Google Maps.</p></div>
        <!-- Tombol Modal Tambah -->
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">+ Tambah pilihan kos</button>
    </div>

    <?php if ($pesanError !== ''): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($pesanError, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['status'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="status">
            <?= $_GET['status'] === 'diubah' ? 'Data kos berhasil diperbarui.' : 'Data kos berhasil ditambahkan.' ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>

    <!-- Tabel Data Alternatif -->
    <div class="table-responsive"><table class="table table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th width="50">No</th>
                <th>Kode</th>
                <th>Nama Kos</th>
                <th>Alamat</th>
                <th>Lokasi</th>
                <th>Telepon</th>
                <th>Tindakan</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            $query = mysqli_query($koneksi, "SELECT * FROM alternatif ORDER BY id_alternatif ASC");
            while ($row = mysqli_fetch_assoc($query)):
                $adaKoordinat = isset($row['latitude'], $row['longitude'])
                    && $row['latitude'] !== null
                    && $row['longitude'] !== null
                    && $row['latitude'] !== ''
                    && $row['longitude'] !== '';
                $koordinat = $adaKoordinat
                    ? (string) $row['latitude'] . ', ' . (string) $row['longitude']
                    : '';
                $urlMaps = $adaKoordinat
                    ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode(str_replace(' ', '', $koordinat))
                    : '';
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><span class="badge bg-secondary"><?= htmlspecialchars($row['kode_alternatif'], ENT_QUOTES, 'UTF-8') ?></span></td>
                <td><b><?= htmlspecialchars($row['nama_kos'], ENT_QUOTES, 'UTF-8') ?></b></td>
                <td><?= htmlspecialchars($row['alamat'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <?php if ($adaKoordinat): ?>
                        <span class="d-block small text-nowrap"><?= htmlspecialchars($koordinat, ENT_QUOTES, 'UTF-8') ?></span>
                        <a class="btn btn-outline-primary btn-sm mt-1" href="<?= htmlspecialchars($urlMaps, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-geo-alt"></i> Buka Maps
                        </a>
                    <?php else: ?>
                        <span class="text-muted small">Belum diatur</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['telepon'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <div class="d-flex flex-wrap gap-1">
                        <button
                            type="button"
                            class="btn btn-outline-primary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#modalTambah"
                            data-mode="ubah"
                            data-id="<?= (int) $row['id_alternatif'] ?>"
                            data-kode="<?= htmlspecialchars($row['kode_alternatif'], ENT_QUOTES, 'UTF-8') ?>"
                            data-nama="<?= htmlspecialchars($row['nama_kos'], ENT_QUOTES, 'UTF-8') ?>"
                            data-alamat="<?= htmlspecialchars($row['alamat'], ENT_QUOTES, 'UTF-8') ?>"
                            data-telepon="<?= htmlspecialchars($row['telepon'], ENT_QUOTES, 'UTF-8') ?>"
                            data-latitude="<?= $adaKoordinat ? htmlspecialchars((string) $row['latitude'], ENT_QUOTES, 'UTF-8') : '' ?>"
                            data-longitude="<?= $adaKoordinat ? htmlspecialchars((string) $row['longitude'], ENT_QUOTES, 'UTF-8') : '' ?>"
                        >Ubah</button>
                        <a href="alternatif.php?hapus=<?= (int) $row['id_alternatif'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Hapus pilihan kos ini dari daftar?')">Hapus</a>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table></div>

    <!-- Form tambah atau ubah kos -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="alternatif.php" method="POST" id="formKos">
                    <input type="hidden" name="aksi" id="aksiForm" value="tambah">
                    <input type="hidden" name="id_alternatif" id="idAlternatif">
                    <div class="modal-header">
                        <h5 class="modal-title" id="judulModal">Tambah Tempat Kos Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kode Alternatif</label>
                            <input type="text" name="kode_alternatif" id="kodeAlternatif" class="form-control" placeholder="Contoh: A5" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Kos</label>
                            <input type="text" name="nama_kos" id="namaKos" class="form-control" placeholder="Contoh: Kos Melati Asri" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" id="alamatKos" class="form-control" rows="2" placeholder="Jl. Pasar V No. 10"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nomor Telepon</label>
                            <input type="text" name="telepon" id="teleponKos" class="form-control" placeholder="0812xxxx">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Koordinat lokasi <span class="text-muted fw-normal">(opsional)</span></label>
                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <label for="latitude" class="form-label small text-muted mb-1">Latitude</label>
                                    <input type="number" step="any" min="-90" max="90" name="latitude" id="latitude" class="form-control" placeholder="-6.200000">
                                </div>
                                <div class="col-sm-6">
                                    <label for="longitude" class="form-label small text-muted mb-1">Longitude</label>
                                    <input type="number" step="any" min="-180" max="180" name="longitude" id="longitude" class="form-control" placeholder="106.816666">
                                </div>
                            </div>
                            <div class="form-text">
                                Di Google Maps, klik kanan lokasi di komputer atau tekan lama di HP, lalu salin pasangan angka koordinat.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="tombolSimpan">Simpan kos</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script JS Bootstrap untuk Modal -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const modalKos = document.getElementById('modalTambah');
        const formKos = document.getElementById('formKos');

        modalKos.addEventListener('show.bs.modal', function (event) {
            const tombol = event.relatedTarget;
            const modeUbah = tombol && tombol.dataset.mode === 'ubah';

            formKos.reset();
            document.getElementById('aksiForm').value = modeUbah ? 'ubah' : 'tambah';
            document.getElementById('idAlternatif').value = modeUbah ? tombol.dataset.id : '';
            document.getElementById('judulModal').textContent = modeUbah ? 'Ubah Data Kos' : 'Tambah Tempat Kos Baru';
            document.getElementById('tombolSimpan').textContent = modeUbah ? 'Simpan perubahan' : 'Simpan kos';

            if (modeUbah) {
                document.getElementById('kodeAlternatif').value = tombol.dataset.kode;
                document.getElementById('namaKos').value = tombol.dataset.nama;
                document.getElementById('alamatKos').value = tombol.dataset.alamat;
                document.getElementById('teleponKos').value = tombol.dataset.telepon;
                document.getElementById('latitude').value = tombol.dataset.latitude;
                document.getElementById('longitude').value = tombol.dataset.longitude;
            }
        });
    </script>
</body>
</html>