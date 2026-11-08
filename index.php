<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';

$count_alt = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM alternatif"))['total'];
$count_kri = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM kriteria"))['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - SPK Pemilihan Tempat Kos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="app.css">
</head>
<body class="container py-4">

    <!-- Header & User Info -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-speedometer2 text-primary me-2"></i> Ruang Kendali SPK Kos</h4>
        </div>
        <div class="bg-white px-3 py-2 rounded-pill shadow-sm border">
            <span class="text-muted small me-2">Selamat datang,</span><b><?= htmlspecialchars($_SESSION['nama_lengkap'], ENT_QUOTES, 'UTF-8') ?></b>
            <a href="logout.php" class="btn btn-outline-danger btn-sm ms-3 rounded-pill px-3"><i class="bi bi-box-arrow-right"></i> Keluar</a>
        </div>
    </div>

    <!-- Navigasi Utama -->
    <nav class="app-nav mb-4">
        <a href="index.php" class="btn btn-primary btn-sm px-3"><i class="bi bi-house-door"></i> Beranda</a>
        <a href="alternatif.php" class="btn btn-outline-secondary btn-sm px-3"><i class="bi bi-building"></i> Data Kos</a>
        <a href="kriteria.php" class="btn btn-outline-secondary btn-sm px-3"><i class="bi bi-list-check"></i> Kriteria & Bobot</a>
        <a href="penilaian.php" class="btn btn-outline-secondary btn-sm px-3"><i class="bi bi-pencil-square"></i> Penilaian</a>
        <a href="hitung.php" class="btn btn-success btn-sm px-3"><i class="bi bi-calculator"></i> Hasil Rekomendasi</a>
    </nav>

    <!-- Welcome Banner -->
    <div class="welcome-panel p-5 mb-4 bg-white rounded-4 shadow-sm border position-relative overflow-hidden">
        <div class="position-absolute end-0 bottom-0 opacity-10 pe-3 pb-3" style="font-size: 120px;">
            <i class="bi bi-house-check"></i>
        </div>
        <span class="badge text-bg-primary rounded-pill px-3 py-2 mb-3">Rekomendasi lebih terarah</span>
        <h2 class="fw-bold text-primary mb-2">Temukan kos yang paling sesuai untukmu</h2>
        <p class="text-muted lead fs-6 mb-0">Kelola pilihan kos, tentukan prioritas, lalu bandingkan hasilnya secara objektif dengan metode <b>Simple Additive Weighting (SAW)</b>.</p>
    </div>

    <!-- Cards Statistik -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="card card-stat bg-primary text-white shadow-sm p-3">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase small fw-semibold opacity-75">Pilihan kos terdata</h6>
                        <h2 class="display-4 fw-bold mb-0"><?= $count_alt ?></h2>
                    </div>
                    <div class="fs-1 opacity-50"><i class="bi bi-building"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card card-stat bg-info text-white shadow-sm p-3">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase small fw-semibold opacity-75">Aspek pertimbangan</h6>
                        <h2 class="display-4 fw-bold mb-0"><?= $count_kri ?></h2>
                    </div>
                    <div class="fs-1 opacity-50"><i class="bi bi-list-check"></i></div>
                </div>
            </div>
        </div>
    </div>

    <section class="mt-4">
        <div class="page-heading">
            <h4 class="fw-bold">Mulai dari sini</h4>
            <p>Ikuti langkah sederhana untuk mendapatkan rekomendasi kos.</p>
        </div>
        <div class="row g-3">
            <div class="col-md-4">
                <a href="alternatif.php" class="workflow-card">
                    <span class="workflow-icon text-primary bg-primary-subtle"><i class="bi bi-buildings"></i></span>
                    <span class="workflow-copy">
                        <strong>1. Kelola pilihan kos</strong>
                        <small>Tambahkan kos yang ingin dibandingkan.</small>
                    </span>
                    <i class="bi bi-arrow-up-right workflow-arrow"></i>
                </a>
            </div>
            <div class="col-md-4">
                <a href="penilaian.php" class="workflow-card">
                    <span class="workflow-icon text-success bg-success-subtle"><i class="bi bi-clipboard2-check"></i></span>
                    <span class="workflow-copy">
                        <strong>2. Beri penilaian</strong>
                        <small>Isi nilai setiap kos berdasarkan kriteria.</small>
                    </span>
                    <i class="bi bi-arrow-up-right workflow-arrow"></i>
                </a>
            </div>
            <div class="col-md-4">
                <a href="hitung.php" class="workflow-card">
                    <span class="workflow-icon text-warning bg-warning-subtle"><i class="bi bi-trophy"></i></span>
                    <span class="workflow-copy">
                        <strong>3. Lihat rekomendasi</strong>
                        <small>Bandingkan hasil peringkat metode SAW.</small>
                    </span>
                    <i class="bi bi-arrow-up-right workflow-arrow"></i>
                </a>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>