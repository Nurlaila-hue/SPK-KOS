<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';

// --- PROSES SIMPAN / UPDATE PENILAIAN ---
if (isset($_POST['simpan'])) {
    foreach ($_POST['nilai'] as $id_alt => $kriterias) {
        foreach ($kriterias as $id_k => $val) {
            // Bersihkan format titik ribuan sebelum disimpan ke database (misal: "600.000" jadi "600000")
            $val_clean = str_replace('.', '', $val);
            $val_clean = str_replace(',', '.', $val_clean); // Mengantisipasi jika ada koma desimal

            // Cek apakah data nilai sudah ada
            $cek = mysqli_query($koneksi, "SELECT * FROM penilaian WHERE id_alternatif = '$id_alt' AND id_kriteria = '$id_k'");
            if (mysqli_num_rows($cek) > 0) {
                // Jika sudah ada, lakukan UPDATE
                mysqli_query($koneksi, "UPDATE penilaian SET nilai = '$val_clean' WHERE id_alternatif = '$id_alt' AND id_kriteria = '$id_k'");
            } else {
                // Jika belum ada, lakukan INSERT
                mysqli_query($koneksi, "INSERT INTO penilaian (id_alternatif, id_kriteria, nilai) VALUES ('$id_alt', '$id_k', '$val_clean')");
            }
        }
    }
    header("Location: penilaian.php?status=sukses");
}

// 1. Ambil Data Kriteria
$kriteria = [];
$q_kriteria = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY id_kriteria ASC");
while ($row = mysqli_fetch_assoc($q_kriteria)) {
    $kriteria[] = $row;
}

// 2. Ambil Data Alternatif
$alternatif = [];
$q_alt = mysqli_query($koneksi, "SELECT * FROM alternatif ORDER BY id_alternatif ASC");
while ($row = mysqli_fetch_assoc($q_alt)) {
    $alternatif[] = $row;
}

// 3. Ambil Data Penilaian yang Sudah Ada
$penilaian = [];
$q_nilai = mysqli_query($koneksi, "SELECT * FROM penilaian");
while ($row = mysqli_fetch_assoc($q_nilai)) {
    $penilaian[$row['id_alternatif']][$row['id_kriteria']] = $row['nilai'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Penilaian Tempat Kos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="app.css?v=4">
</head>
<body class="container py-4">

    <!-- Navigasi Utama -->
    <nav class="app-nav mb-4">
        <a href="index.php" class="btn btn-outline-primary btn-sm">Beranda</a>
        <a href="alternatif.php" class="btn btn-outline-primary btn-sm">Data Kos</a>
        <a href="kriteria.php" class="btn btn-outline-primary btn-sm">Kriteria & Bobot</a>
        <a href="penilaian.php" class="btn btn-primary btn-sm">Penilaian</a>
        <a href="hitung.php" class="btn btn-success btn-sm">Hasil Rekomendasi</a>
    </nav>

    <div class="page-heading"><h2>Nilai Setiap Pilihan Kos</h2><p>Isi nilai kos untuk tiap kriteria agar sistem dapat menghitung rekomendasi. Buka tautan peta di samping nama kos untuk memeriksa lokasinya.</p></div>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'sukses'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Penilaian berhasil disimpan. Hasil rekomendasi sudah siap diperbarui.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="penilaian.php" method="POST">
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th width="50">No</th>
                        <th>Nama Kos</th>
                        <?php foreach ($kriteria as $k): ?>
                            <th><?= $k['nama_kriteria'] ?> (<?= $k['kode_kriteria'] ?>)</th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    foreach ($alternatif as $a): 
                        $id_a = $a['id_alternatif'];
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <b><?= htmlspecialchars($a['nama_kos'], ENT_QUOTES, 'UTF-8') ?></b>
                            <?php if (
                                isset($a['latitude'], $a['longitude'])
                                && $a['latitude'] !== null
                                && $a['longitude'] !== null
                                && $a['latitude'] !== ''
                                && $a['longitude'] !== ''
                            ):
                                $koordinat = (string) $a['latitude'] . ', ' . (string) $a['longitude'];
                                $urlMaps = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode(str_replace(' ', '', $koordinat));
                            ?>
                                <span class="d-block small text-muted"><?= htmlspecialchars($koordinat, ENT_QUOTES, 'UTF-8') ?></span>
                                <a
                                    href="<?= htmlspecialchars($urlMaps, ENT_QUOTES, 'UTF-8') ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="btn btn-outline-primary btn-sm mt-1"
                                >
                                    <i class="bi bi-geo-alt"></i> Buka Maps
                                </a>
                            <?php endif; ?>
                        </td>
                        <?php foreach ($kriteria as $k): 
                            $id_k = $k['id_kriteria'];
                            $val = isset($penilaian[$id_a][$id_k]) ? $penilaian[$id_a][$id_k] : 0;

                            // Format angka dengan pemisah ribuan titik (contoh: 600000 jadi 600.000)
                            // Jika nilainya kecil (seperti 0.8 atau 3), tampilkan biasa tanpa format ribuan
                            $val_display = ($val >= 1000) ? number_format($val, 0, ',', '.') : $val;
                        ?>
                        <td>
                            <input type="text" name="nilai[<?= $id_a ?>][<?= $id_k ?>]" class="form-control form-control-sm text-end" value="<?= $val_display ?>" required>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <button type="submit" name="simpan" class="btn btn-primary mt-3">Simpan penilaian</button>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>