<?php
require_once __DIR__ . '/../src/config.php';

if (!empty($_SESSION['user'])) {
    header('Location: dashboard_ma.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($email === '' || $pass === '') {
        $error = 'Email dan password wajib diisi.';
    } else {
        try {
            $db   = getDB();
            $stmt = $db->prepare(
                'SELECT id, name, email, password, role
                 FROM users
                 WHERE email = :email AND is_active = TRUE
                 LIMIT 1'
            );
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if (!$user) {
                $error = 'email_tidak_terdaftar';
            } elseif (!password_verify($pass, $user['password'])) {
                $error = 'password_salah';
            } else {
                $db->prepare('UPDATE users SET last_login = NOW() WHERE id = :id')
                   ->execute([':id' => $user['id']]);
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id'    => $user['id'],
                    'name'  => $user['name'],
                    'email' => $user['email'],
                    'role'  => $user['role'],
                ];
                header('Location: dashboard_ma.php');
                exit;
            }
        } catch (Exception $e) {
            error_log('[login.php] Login error: ' . $e->getMessage());
            $error = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login — BI Dashboard</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&family=Lora:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --cobalt:        #1a56db;
      --cobalt-dim:    rgba(26,86,219,.07);
      --cobalt-border: rgba(26,86,219,.18);
      --text:          #0f172a;
      --muted:         #64748b;
      --border:        #e2e8f0;
      --bg:            #f0f4f9;
    }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: var(--bg);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      position: relative;
      overflow: hidden;
    }

    body::before {
      content: '';
      position: fixed; inset: 0;
      background-image: radial-gradient(circle, #c8d4e8 1px, transparent 1px);
      background-size: 24px 24px;
      opacity: .4;
      pointer-events: none;
    }

    body::after {
      content: '';
      position: fixed;
      width: 600px; height: 600px;
      background: radial-gradient(circle, rgba(26,86,219,.06) 0%, transparent 65%);
      top: -150px; right: -150px;
      pointer-events: none;
    }

    .blob-bottom {
      position: fixed;
      width: 500px; height: 500px;
      background: radial-gradient(circle, rgba(8,145,178,.05) 0%, transparent 65%);
      bottom: -120px; left: -100px;
      pointer-events: none;
    }

    .login-wrap {
      position: relative; z-index: 1;
      width: 100%; max-width: 420px;
    }

    .login-card {
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 20px;
      padding: 40px 36px;
      box-shadow: 0 8px 40px rgba(15,23,42,.08), 0 2px 8px rgba(15,23,42,.04);
      position: relative;
      animation: cardIn .4s cubic-bezier(.4,0,.2,1) both;
    }

    @keyframes cardIn {
      from { opacity:0; transform: translateY(16px); }
      to   { opacity:1; transform: translateY(0); }
    }

    .login-card::before {
      content: '';
      position: absolute;
      top: 0; left: 36px; right: 36px; height: 2px;
      background: linear-gradient(90deg, var(--cobalt), #60a5fa);
      border-radius: 99px;
    }

    .login-logo { text-align: center; margin-bottom: 28px; }

    .logo-fallback {
      display: inline-flex; align-items: center; gap: 10px;
      font-family: 'Lora', serif; font-size: 22px; color: var(--text);
    }

    .logo-icon {
      width: 38px; height: 38px;
      background: var(--cobalt); border-radius: 9px;
      display: flex; align-items: center; justify-content: center;
    }

    .login-heading { text-align: center; margin-bottom: 28px; }

    .login-heading h1 {
      font-family: 'Lora', serif;
      font-size: 24px; font-weight: 500;
      color: var(--text); margin-bottom: 4px;
    }

    .login-heading p { font-size: 13px; color: var(--muted); }

    .form-group { margin-bottom: 14px; }

    label {
      display: block; font-size: 11px; font-weight: 600;
      color: var(--muted); text-transform: uppercase;
      letter-spacing: .7px; margin-bottom: 6px;
    }

    input[type=email],
    input[type=password] {
      width: 100%; padding: 11px 14px;
      background: var(--bg);
      border: 1px solid var(--border);
      border-radius: 9px;
      color: var(--text);
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: 14px; transition: all .2s;
    }

    input:focus {
      outline: none;
      border-color: var(--cobalt);
      background: #fff;
      box-shadow: 0 0 0 3px var(--cobalt-dim);
    }

    input::placeholder { color: #94a3b8; }

    .form-options {
      display: flex; justify-content: space-between;
      align-items: center; font-size: 12.5px;
      margin: 6px 0 20px; flex-wrap: wrap; gap: 8px;
    }

    .form-options label {
      text-transform: none; letter-spacing: 0;
      font-weight: 400; color: var(--muted);
      display: flex; align-items: center; gap: 6px;
      cursor: pointer; margin: 0;
    }

    .form-options a { color: var(--cobalt); text-decoration: none; font-size: 12.5px; }
    .form-options a:hover { text-decoration: underline; }

    .btn-login {
      width: 100%; padding: 12px;
      background: var(--cobalt);
      border: none; border-radius: 9px;
      color: #fff;
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: 14px; font-weight: 600;
      cursor: pointer; letter-spacing: .1px;
      transition: all .2s;
    }

    .btn-login:hover { background: #1648c7; }
    .btn-login:active { transform: scale(.98); }

    .login-footer {
      text-align: center; margin-top: 20px;
      font-size: 11.5px; color: #94a3b8;
    }
  </style>
</head>
<body>
<div class="blob-bottom"></div>

<div class="login-wrap">
  <div class="login-card">

    <div class="login-logo">
      <?php if (file_exists(__DIR__ . '/assets/logo_sbprmvbg.png')): ?>
        <img src="assets/logo_sbprmvbg.png" alt="Logo" style="max-width:140px;">
      <?php else: ?>
        <div class="logo-fallback">
          <div class="logo-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
              <rect x="3"  y="12" width="4" height="9"  rx="1" fill="white"/>
              <rect x="9"  y="7"  width="4" height="14" rx="1" fill="white"/>
              <rect x="15" y="3"  width="4" height="18" rx="1" fill="white"/>
            </svg>
          </div>
          BI Dashboard
        </div>
      <?php endif; ?>
    </div>

    <div class="login-heading">
      <h1>Selamat Datang</h1>
      <p>Masuk ke sistem Business Intelligence</p>
    </div>

    <form method="post">
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email"
               placeholder="nama@perusahaan.com"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               autocomplete="email" required>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password"
               placeholder="••••••••"
               autocomplete="current-password" required>
      </div>

      <div class="form-options">
        <label>
          <input type="checkbox" name="remember" style="accent-color:var(--cobalt)">
          Ingat saya
        </label>
        <a href="#">Lupa password?</a>
      </div>

      <button type="submit" class="btn-login">Masuk</button>
    </form>

  </div>
</div>

<?php if ($error === 'email_tidak_terdaftar'): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  Swal.fire({
    icon: 'error',
    title: 'Email Tidak Ditemukan',
    text: 'Email yang Anda masukkan tidak terdaftar dalam sistem.',
    confirmButtonColor: '#1a56db',
    confirmButtonText: 'Coba Lagi'
  });
});
</script>
<?php elseif ($error === 'password_salah'): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  Swal.fire({
    icon: 'error',
    title: 'Password Salah',
    text: 'Password yang Anda masukkan salah. Silakan coba lagi.',
    confirmButtonColor: '#1a56db',
    confirmButtonText: 'Coba Lagi'
  });
});
</script>
<?php elseif ($error): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  Swal.fire({
    icon: 'error',
    title: 'Login Gagal',
    text: '<?= addslashes($error) ?>',
    confirmButtonColor: '#1a56db'
  });
});
</script>
<?php endif; ?>

<?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  Swal.fire({
    toast: true, position: 'top-end', icon: 'success',
    title: 'Berhasil logout',
    showConfirmButton: false, timer: 2500, timerProgressBar: true
  });
});
</script>
<?php endif; ?>
</body>
</html>
