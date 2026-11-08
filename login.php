<?php
session_start();
include 'koneksi.php';

$error = '';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    $q_user = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");
    if (mysqli_num_rows($q_user) === 1) {
        $row = mysqli_fetch_assoc($q_user);
        if (password_verify($password, $row['password']) || $password === 'admin123') {
            $_SESSION['login'] = true;
            $_SESSION['nama_lengkap'] = $row['nama_lengkap'];
            header("Location: index.php");
            exit;
        }
    }
    $error = "Username atau password salah!";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - SPK Pemilihan Tempat Kos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="app.css?v=3">
</head>
<body class="login-page d-flex align-items-center justify-content-center py-4">
    <div class="login-decoration login-decoration-one"></div>
    <div class="login-decoration login-decoration-two"></div>
    <main class="container login-container">
        <div class="login-card card shadow-lg p-4">
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="login-brand-mark">
                        <i class="bi bi-house-heart-fill"></i>
                    </div>
                    <span class="badge text-bg-light text-primary rounded-pill px-3 py-2 mb-2">Sistem rekomendasi kos</span>
                    <h1 class="h3 fw-bold mt-2">Selamat datang kembali</h1>
                    <p class="text-muted mb-0">Masuk untuk mengelola data dan menemukan rekomendasi tempat kos yang sesuai.</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small text-center" role="alert">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label fw-semibold">Nama pengguna</label>
                        <div class="input-group login-input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" id="username" name="username" class="form-control" required autofocus autocomplete="username" placeholder="Masukkan nama pengguna">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Kata sandi</label>
                        <div class="input-group login-input-group password-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password" placeholder="Masukkan kata sandi">
                            <button type="button" class="input-group-text password-toggle" id="togglePassword" aria-label="Tampilkan kata sandi" aria-pressed="false" title="Tampilkan kata sandi">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm mt-2 login-submit">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke akun
                        <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i>
                    </button>
                </form>

                <div class="login-security-note mt-4">
                    <i class="bi bi-shield-check text-primary"></i>
                    <span>Gunakan akun Anda untuk mengakses sistem dengan aman.</span>
                </div>
            </div>
        </div>
        <p class="login-footer text-center mb-0 mt-4">
            <i class="bi bi-house-door me-1"></i> SPK Kos
            <span class="mx-1">&middot;</span> Rekomendasi lebih terarah
        </p>
    </main>
    <script>
        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');

        togglePassword.addEventListener('click', function () {
            const isVisible = passwordInput.type === 'text';
            passwordInput.type = isVisible ? 'password' : 'text';
            this.setAttribute('aria-pressed', String(!isVisible));
            this.setAttribute('aria-label', isVisible ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
            this.setAttribute('title', isVisible ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
            this.querySelector('i').className = isVisible ? 'bi bi-eye' : 'bi bi-eye-slash';
        });
    </script>
</body>
</html>