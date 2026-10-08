<?php
session_start();
require_once '../owner/database/connection.php';

// arah halaman setelah login, sesuai role
function halamanRole($role)
{
    if ($role === 'admin') return '../owner/index.php';
    if ($role === 'kasir') return '../kasir/index.php';
    return '../maanejer%20caabang/index.php'; // manajer
}

if (isset($_SESSION['user'])) {
    header('Location: ' . halamanRole($_SESSION['user']['role']));
    exit;
}

$pesan = '';
if (isset($_POST['email'])) {
    $db = new Database();
    $stmt = $db->conn->prepare("SELECT id, nama, email, password, role, branch_id FROM users WHERE email = ?");
    $stmt->bind_param('s', $_POST['email']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($_POST['password'], $user['password'])) {
        session_regenerate_id(true);
        unset($user['password']);
        $_SESSION['user'] = $user;
        header('Location: ' . halamanRole($user['role']));
        exit;
    }
    $pesan = 'Email atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - E-POS</title>
    <link rel="stylesheet" href="../owner/template/src/assets/css/main.css">
</head>
<body class="bg-light">
    <div class="container d-flex align-items-center justify-content-center" style="min-height:100vh">
        <div class="card shadow-sm" style="width:100%;max-width:400px">
            <div class="card-body p-4">
                <h1 class="fs-3 mb-1">E-POS</h1>
                <p class="text-secondary mb-4">Masuk ke akun kamu</p>
                <?php if ($pesan) { ?><div class="alert alert-danger"><?= htmlspecialchars($pesan) ?></div><?php } ?>
                <form method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="email" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" id="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Masuk</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
