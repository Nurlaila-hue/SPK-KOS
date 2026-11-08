<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';

// --- PROSES SIMPAN / UPDATE BOBOT KRITERIA ---
if (isset($_POST['simpan'])) {
    foreach ($_POST['bobot'] as $id_k => $b) {
        $jenis = $_POST['jenis'][$id_k];
        $nama  = mysqli_real_escape_string($koneksi, $_POST['nama_kriteria'][$id_k]);
        
        mysqli_query($koneksi, "UPDATE kriteria SET nama_kriteria = '$nama', jenis = '$jenis', bobot = '$b' WHERE id_kriteria = '$id_k'");
    }
    header("Location: kriteria.php?status=sukses");
}

// Ambil Seluruh Data Kriteria
$q_kriteria = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY id_kriteria ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Kriteria & Bobot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="app.css">
</head>
<body class="container py-4">

    <!-- Navigasi Utama -->
    <nav class="app-nav mb-4">
        <a href="index.php" class="btn btn-outline-primary btn-sm">Beranda</a>
        <a href="alternatif.php" class="btn btn-outline-primary btn-sm">Data Kos</a>
        <a href="kriteria.php" class="btn btn-primary btn-sm">Kriteria & Bobot</a>
        <a href="penilaian.php" class="btn btn-outline-primary btn-sm">Penilaian</a>
        <a href="hitung.php" class="btn btn-success btn-sm">Hasil Rekomendasi</a>
    </nav>

    <div class="page-heading"><h2>Atur Kriteria & Bobot</h2><p>Tentukan aspek yang penting dan seberapa besar pengaruhnya terhadap hasil rekomendasi. Total bobot idealnya <b>1,00 (100%)</b>.</p></div>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'sukses'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Kriteria dan bobot berhasil diperbarui.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="kriteria.php" method="POST">
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th width="60">Kode</th>
                        <th>Nama Kriteria</th>
                        <th width="180">Jenis penilaian</th>
                        <th width="180">Bobot Preferensi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_bobot = 0;
                    while ($k = mysqli_fetch_assoc($q_kriteria)): 
                        $total_bobot += $k['bobot'];
                    ?>
                    <tr>
                        <td><span class="badge bg-secondary"><?= $k['kode_kriteria'] ?></span></td>
                        <td>
                            <input type="text" name="nama_kriteria[<?= $k['id_kriteria'] ?>]" class="form-control form-control-sm" value="<?= $k['nama_kriteria'] ?>" required>
                        </td>
                        <td>
                            <select name="jenis[<?= $k['id_kriteria'] ?>]" class="form-select form-select-sm">
                                <option value="benefit" <?= $k['jenis'] == 'benefit' ? 'selected' : '' ?>>Benefit (lebih besar lebih baik)</option>
                                <option value="cost" <?= $k['jenis'] == 'cost' ? 'selected' : '' ?>>Cost (lebih kecil lebih baik)</option>
                            </select>
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" max="1" name="bobot[<?= $k['id_kriteria'] ?>]" class="form-control form-control-sm" value="<?= $k['bobot'] ?>" required>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td colspan="3" class="text-end">Total Bobot:</td>
                        <td>
                            <span class="<?= round($total_bobot, 2) == 1.00 ? 'text-success' : 'text-danger' ?>">
                                <?= round($total_bobot, 2) ?>
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <button type="submit" name="simpan" class="btn btn-primary mt-3">Simpan pengaturan</button>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>