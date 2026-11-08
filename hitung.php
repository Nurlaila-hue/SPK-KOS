<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

// Ambil data kriteria
$kriteria = [];
$q_kriteria = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY id_kriteria ASC");

while ($row = mysqli_fetch_assoc($q_kriteria)) {
    $kriteria[$row['id_kriteria']] = $row;
}

// Ambil data alternatif kos
$alternatif = [];
$q_alt = mysqli_query($koneksi, "SELECT * FROM alternatif ORDER BY id_alternatif ASC");

while ($row = mysqli_fetch_assoc($q_alt)) {
    $alternatif[$row['id_alternatif']] = $row;
}

// Ambil nilai penilaian
$matriks = [];
$q_nilai = mysqli_query($koneksi, "SELECT * FROM penilaian");

while ($row = mysqli_fetch_assoc($q_nilai)) {
    $matriks[$row['id_alternatif']][$row['id_kriteria']] = $row['nilai'];
}

// Cari nilai pembagi untuk setiap kriteria
$min_max = [];

foreach ($kriteria as $id_k => $k) {
    $nilaiKriteria = [];

    foreach ($alternatif as $id_a => $a) {
        if (isset($matriks[$id_a][$id_k])) {
            $nilaiKriteria[] = (float) $matriks[$id_a][$id_k];
        }
    }

    if (!empty($nilaiKriteria)) {
        if ($k['jenis'] === 'benefit') {
            $min_max[$id_k] = max($nilaiKriteria);
        } else {
            $min_max[$id_k] = min($nilaiKriteria);
        }
    } else {
        $min_max[$id_k] = 0;
    }
}

// Normalisasi nilai dan hitung skor SAW
$normalisasi = [];
$hasil_akhir = [];

foreach ($alternatif as $id_a => $a) {
    $skor = 0;

    foreach ($kriteria as $id_k => $k) {
        $nilaiMentah = isset($matriks[$id_a][$id_k])
            ? (float) $matriks[$id_a][$id_k]
            : 0;

        $pembagi = $min_max[$id_k] ?? 0;

        if ($k['jenis'] === 'benefit') {
            $nilaiNormal = ($pembagi != 0)
                ? $nilaiMentah / $pembagi
                : 0;
        } else {
            $nilaiNormal = ($nilaiMentah != 0 && $pembagi != 0)
                ? $pembagi / $nilaiMentah
                : 0;
        }

        $normalisasi[$id_a][$id_k] = $nilaiNormal;
        $skor += $nilaiNormal * (float) $k['bobot'];
    }

    $hasil_akhir[] = [
        'id_alternatif' => $id_a,
        'nama_kos' => $a['nama_kos'],
        'latitude' => $a['latitude'] ?? null,
        'longitude' => $a['longitude'] ?? null,
        'skor' => $skor,
    ];
}

// Urutkan peringkat berdasarkan skor SAW tertinggi
usort($hasil_akhir, function ($a, $b) {
    return $b['skor'] <=> $a['skor'];
});

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hasil Rekomendasi Kos</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"
    >
    <link rel="stylesheet" href="app.css">
</head>
<body class="container py-4">

    <nav class="app-nav mb-4">
        <a href="index.php" class="btn btn-outline-primary btn-sm">Beranda</a>
        <a href="alternatif.php" class="btn btn-outline-primary btn-sm">Data Kos</a>
        <a href="kriteria.php" class="btn btn-outline-primary btn-sm">Kriteria & Bobot</a>
        <a href="penilaian.php" class="btn btn-outline-primary btn-sm">Penilaian</a>
        <a href="hitung.php" class="btn btn-success btn-sm">Hasil Rekomendasi</a>
    </nav>

    <div class="page-heading">
        <h2>Rekomendasi Tempat Kos</h2>
        <p>
            Bandingkan pilihan kos berdasarkan kriteria dan bobot
            menggunakan metode SAW.
        </p>
    </div>

    <h4 class="mb-3">Rincian Nilai Ternormalisasi</h4>

    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Nama Kos</th>
                    <?php foreach ($kriteria as $k): ?>
                        <th>
                            <?= htmlspecialchars(
                                $k['kode_kriteria'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($alternatif as $id_a => $a): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars(
                                $a['nama_kos'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </td>

                        <?php foreach ($kriteria as $id_k => $k): ?>
                            <td>
                                <?= round(
                                    $normalisasi[$id_a][$id_k] ?? 0,
                                    4
                                ) ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($alternatif)): ?>
                    <tr>
                        <td
                            colspan="<?= count($kriteria) + 1 ?>"
                            class="text-center text-muted py-4"
                        >
                            Belum ada data kos untuk dihitung.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <h4 class="mt-4 mb-3">Peringkat Pilihan Terbaik</h4>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Peringkat</th>
                    <th>Nama Kos</th>
                    <th>Skor SAW</th>
                    <th>Lokasi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($hasil_akhir as $index => $hasil): ?>
                    <tr>
                        <td>
                            <?php if ($index === 0): ?>
                                <span class="badge text-bg-warning">
                                    <i class="bi bi-trophy-fill"></i>
                                    Peringkat 1
                                </span>
                            <?php else: ?>
                                <?= $index + 1 ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= htmlspecialchars(
                                $hasil['nama_kos'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </td>
                        <td>
                            <strong><?= round($hasil['skor'], 4) ?></strong>
                        </td>
                        <td>
                            <?php
                            $adaKoordinat = $hasil['latitude'] !== null
                                && $hasil['longitude'] !== null
                                && $hasil['latitude'] !== ''
                                && $hasil['longitude'] !== '';
                            if ($adaKoordinat):
                                $koordinat = (string) $hasil['latitude'] . ', ' . (string) $hasil['longitude'];
                                $urlMaps = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode(str_replace(' ', '', $koordinat));
                            ?>
                                <span class="d-block small text-nowrap"><?= htmlspecialchars($koordinat, ENT_QUOTES, 'UTF-8') ?></span>
                                <a
                                    class="btn btn-outline-primary btn-sm mt-1"
                                    href="<?= htmlspecialchars($urlMaps, ENT_QUOTES, 'UTF-8') ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <i class="bi bi-geo-alt"></i> Buka Maps
                                </a>
                            <?php else: ?>
                                <span class="text-muted small">Koordinat belum tersedia</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($hasil_akhir)): ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            Belum ada hasil. Tambahkan data kos terlebih dahulu.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>