<?php
require_once __DIR__ . '/../src/config.php';
require_login();

$currentRole = $_SESSION['user']['role'] ?? '';
$isAdmin     = $currentRole === 'admin';
$isManager   = $currentRole === 'manager';

if (!$isAdmin && !$isManager) {
    http_response_code(403);
    echo '<h2 style="font-family:sans-serif;padding:40px;color:#fff">Akses ditolak.</h2>';
    exit;
}

$pageTitle = 'Kelola Akun';
$db        = getDB();
$success   = '';
$error     = '';
$action    = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // TAMBAH USER
    if ($action === 'add') {
        $name  = trim($_POST['name']  ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password']   ?? '';
        $role  = $_POST['role']       ?? 'staff';

        // Manager hanya bisa tambah staff
        if ($isManager && $role !== 'staff') {
            $error = 'Manager hanya dapat menambahkan akun dengan role Staff.';
        } elseif (!$name || !$email || !$pass) {
            $error = 'Nama, email, dan password wajib diisi.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Format email tidak valid.';
        } elseif (strlen($pass) < 6) {
            $error = 'Password minimal 6 karakter.';
        } elseif ($isAdmin && !in_array($role, ['admin','manager','staff'])) {
            $error = 'Role tidak valid.';
        } else {
            try {
                $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
                $db->prepare('INSERT INTO users (name,email,password,role) VALUES (:name,:email,:password,:role)')
                   ->execute([':name'=>$name,':email'=>$email,':password'=>$hash,':role'=>$role]);
                $success = "Akun <strong>{$email}</strong> berhasil ditambahkan.";
            } catch (PDOException $e) {
                error_log('[manage_users.php] Insert user gagal: ' . $e->getMessage());
                $error = str_contains($e->getMessage(), 'unique')
                    ? 'Email sudah digunakan.'
                    : 'Terjadi kesalahan. Silakan coba lagi.';
            }
        }
    }

    // RESET PASSWORD
    elseif ($action === 'reset_password') {
        $id      = (int)($_POST['user_id'] ?? 0);
        $newPass = $_POST['new_password'] ?? '';

        if ($isManager) {
            $t = $db->prepare('SELECT role FROM users WHERE id=:id');
            $t->execute([':id'=>$id]);
            $tu = $t->fetch();
            if ($tu && in_array($tu['role'], ['admin','manager'])) {
                $error = 'Manager tidak dapat mereset password Admin atau Manager lain.';
            }
        }

        if (!$error) {
            if ($id <= 0 || strlen($newPass) < 6) {
                $error = 'Password minimal 6 karakter.';
            } else {
                $hash = password_hash($newPass, PASSWORD_BCRYPT, ['cost'=>12]);
                $db->prepare('UPDATE users SET password=:p WHERE id=:id')->execute([':p'=>$hash,':id'=>$id]);
                $success = 'Password berhasil direset.';
            }
        }
    }

    // TOGGLE AKTIF
    elseif ($action === 'toggle_active') {
        $id = (int)($_POST['user_id'] ?? 0);
        if ($id === (int)($_SESSION['user']['id'] ?? 0)) {
            $error = 'Tidak dapat menonaktifkan akun yang sedang aktif.';
        } elseif ($isManager) {
            $t = $db->prepare('SELECT role FROM users WHERE id=:id');
            $t->execute([':id'=>$id]);
            $tu = $t->fetch();
            if ($tu && in_array($tu['role'], ['admin','manager'])) {
                $error = 'Manager tidak dapat mengubah status Admin atau Manager lain.';
            }
        }
        if (!$error) {
            $db->prepare('UPDATE users SET is_active = NOT is_active WHERE id=:id')->execute([':id'=>$id]);
            $success = 'Status akun berhasil diubah.';
        }
    }

    // HAPUS (admin only)
    elseif ($action === 'delete') {
        if (!$isAdmin) {
            $error = 'Hanya Admin yang dapat menghapus akun.';
        } else {
            $id = (int)($_POST['user_id'] ?? 0);
            if ($id === (int)($_SESSION['user']['id'] ?? 0)) {
                $error = 'Tidak dapat menghapus akun yang sedang digunakan.';
            } else {
                $db->prepare('DELETE FROM users WHERE id=:id')->execute([':id'=>$id]);
                $success = 'Akun berhasil dihapus.';
            }
        }
    }
}

$users = $db->query('SELECT id,name,email,role,is_active,created_at,last_login FROM users ORDER BY created_at ASC')->fetchAll();

// ── Profiling: catat penggunaan memori setelah query daftar user ──
$memUsage = round(memory_get_usage() / 1024, 2);
$memPeak  = round(memory_get_peak_usage() / 1024, 2);
error_log("[profiling] manage_users.php — Total user: " . count($users) . ", Memory: {$memUsage} KB, Peak: {$memPeak} KB");


ob_start();
?>

<div class="page-header">
  <div>
    <h2>Kelola Akun</h2>
    <p><?= $isAdmin ? 'Tambah, nonaktifkan, reset password, atau hapus pengguna' : 'Tambah akun staff dan reset password pengguna' ?></p>
  </div>
</div>

<?php if ($success): ?><div class="alert-success"><?= $success ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<!-- FORM TAMBAH -->
<div class="card-dashboard" style="margin-bottom:20px;">
  <h2>Tambah Akun Baru</h2>
  <form method="post" class="user-form">
    <input type="hidden" name="action" value="add">
    <div class="form-row">
      <div class="form-col">
        <label>Nama Lengkap</label>
        <input type="text" name="name" placeholder="Nama lengkap" required>
      </div>
      <div class="form-col">
        <label>Email</label>
        <input type="email" name="email" placeholder="email@perusahaan.com" required>
      </div>
      <div class="form-col">
        <label>Password</label>
        <input type="password" name="password" placeholder="Min. 6 karakter" required>
      </div>
      <div class="form-col">
        <label>Role</label>
        <select name="role">
          <option value="staff">Staff</option>
          <?php if ($isAdmin): ?>
          <option value="manager">Manager</option>
          <option value="admin">Admin</option>
          <?php endif; ?>
        </select>
      </div>
      <div class="form-col form-col-btn">
        <button type="submit" class="btn-primary">+ Tambah Akun</button>
      </div>
    </div>
  </form>
</div>

<!-- TABEL USER -->
<div class="card-dashboard">
  <h2>Daftar Akun (<?= count($users) ?>)</h2>

  <?php if ($isManager): ?>
  <div style="margin-bottom:14px;padding:10px 14px;background:rgba(59,130,246,.07);border:1px solid rgba(59,130,246,.15);border-radius:8px;font-size:12.5px;color:var(--text-secondary);">
    ℹ️ Sebagai Manager, kamu dapat reset password & nonaktifkan akun Staff. Untuk aksi lainnya hubungi Admin.
  </div>
  <?php endif; ?>

  <div style="overflow-x:auto;">
    <table class="user-table">
      <thead>
        <tr>
          <th>#</th><th>Nama</th><th>Email</th><th>Role</th>
          <th>Status</th><th>Login Terakhir</th><th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $i => $u):
          $isOwn      = $u['id'] === (int)($_SESSION['user']['id'] ?? 0);
          $tgtAdmin   = $u['role'] === 'admin';
          $tgtMgr     = $u['role'] === 'manager';
          $canReset   = $isAdmin || ($isManager && !$tgtAdmin && !$tgtMgr);
          $canToggle  = !$isOwn && ($isAdmin || ($isManager && !$tgtAdmin && !$tgtMgr));
          $canDelete  = $isAdmin && !$isOwn;
        ?>
        <tr class="<?= !$u['is_active'] ? 'row-inactive' : '' ?>">
          <td><?= $i+1 ?></td>
          <td><?= htmlspecialchars($u['name']) ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><span class="badge badge-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
          <td><span class="badge <?= $u['is_active'] ? 'badge-active' : 'badge-inactive' ?>"><?= $u['is_active'] ? 'Aktif' : 'Nonaktif' ?></span></td>
          <td style="font-size:12px;color:var(--text-muted);"><?= $u['last_login'] ? date('d M Y H:i', strtotime($u['last_login'])) : '—' ?></td>
          <td>
            <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
              <?php if ($isOwn): ?>
                <span style="font-size:11px;color:var(--text-muted);">(akun Anda)</span>
              <?php else: ?>
                <?php if ($canReset): ?>
                <button class="btn-sm btn-outline" onclick="resetPassword(<?= $u['id'] ?>,'<?= htmlspecialchars($u['email'],ENT_QUOTES) ?>')">🔑 Reset</button>
                <?php endif; ?>
                <?php if ($canToggle): ?>
                <form method="post" style="display:inline">
                  <input type="hidden" name="action" value="toggle_active">
                  <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                  <button type="submit" class="btn-sm <?= $u['is_active'] ? 'btn-warn' : 'btn-success' ?>"><?= $u['is_active'] ? '⏸ Nonaktifkan' : '▶ Aktifkan' ?></button>
                </form>
                <?php endif; ?>
                <?php if ($canDelete): ?>
                <form method="post" style="display:inline" onsubmit="return confirm('Hapus akun <?= htmlspecialchars($u['email'],ENT_QUOTES) ?>?')">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                  <button type="submit" class="btn-sm btn-danger">🗑 Hapus</button>
                </form>
                <?php endif; ?>
                <?php if (!$canReset && !$canToggle && !$canDelete): ?>
                  <span style="font-size:11px;color:var(--text-muted);">—</span>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<form method="post" id="resetForm" style="display:none;">
  <input type="hidden" name="action" value="reset_password">
  <input type="hidden" name="user_id" id="resetUserId">
  <input type="hidden" name="new_password" id="resetNewPassword">
</form>

<style>
.alert-success,.alert-error{padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px}
.alert-success{background:rgba(5,150,105,.08);border:1px solid rgba(5,150,105,.2);color:#059669}
.alert-error{background:rgba(220,38,38,.07);border:1px solid rgba(220,38,38,.18);color:#dc2626}
.user-form .form-row{display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end}
.user-form .form-col{flex:1 1 160px;display:flex;flex-direction:column;gap:5px}
.user-form .form-col-btn{flex:0 0 auto}
.user-form label{font-size:10px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px}
.user-form input,.user-form select{padding:9px 12px;border:1px solid var(--border-strong);border-radius:7px;font-family:inherit;font-size:13px;background:var(--bg-surface);color:var(--text-primary);transition:all .2s}
.user-form input:focus,.user-form select:focus{outline:none;border-color:var(--cobalt);box-shadow:0 0 0 2px var(--cobalt-dim);background:var(--bg-card)}
.btn-primary{background:var(--cobalt);color:#fff;border:none;border-radius:7px;padding:9px 16px;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap;transition:all .2s}
.btn-primary:hover{background:#2563eb}
.user-table{width:100%;border-collapse:collapse;font-size:13px}
.user-table th{text-align:left;padding:9px 12px;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);border-bottom:1px solid var(--border);background:var(--bg-surface)}
.user-table td{padding:10px 12px;border-bottom:1px solid var(--border);vertical-align:middle}
.user-table tr:last-child td{border-bottom:none}
.user-table tr:hover td{background:var(--bg-card-hover)}
.row-inactive td{opacity:.45}
.badge{display:inline-block;padding:3px 9px;border-radius:99px;font-size:10px;font-weight:600}
.badge-admin{background:rgba(26,86,219,.1);color:#1a56db}
.badge-manager{background:rgba(201,150,42,.12);color:#a1791f}
.badge-staff{background:rgba(71,85,105,.1);color:#475569}
.badge-active{background:rgba(5,150,105,.1);color:#059669}
.badge-inactive{background:rgba(220,38,38,.08);color:#dc2626}
.btn-sm{padding:5px 10px;border-radius:6px;font-size:11.5px;font-weight:500;cursor:pointer;border:1px solid transparent;transition:all .15s;white-space:nowrap}
.btn-outline{background:transparent;border-color:var(--border-strong);color:var(--text-secondary)}
.btn-outline:hover{border-color:var(--cobalt);color:var(--cobalt);background:var(--cobalt-dim)}
.btn-warn{background:rgba(217,119,6,.08);border-color:rgba(217,119,6,.25);color:#b45309}
.btn-warn:hover{background:rgba(217,119,6,.16)}
.btn-success{background:rgba(5,150,105,.08);border-color:rgba(5,150,105,.25);color:#059669}
.btn-success:hover{background:rgba(5,150,105,.16)}
.btn-danger{background:rgba(220,38,38,.08);border-color:rgba(220,38,38,.22);color:#dc2626}
.btn-danger:hover{background:rgba(220,38,38,.16)}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function resetPassword(userId, email) {
  Swal.fire({
    title: 'Reset Password',
    html: 'Password baru untuk <strong>' + email + '</strong>',
    input: 'password',
    inputPlaceholder: 'Password baru (min. 6 karakter)',
    inputAttributes: {minlength:6, autocomplete:'new-password'},
    showCancelButton: true,
    confirmButtonText: 'Reset',
    cancelButtonText: 'Batal',
    preConfirm: function(val) {
      if (!val || val.length < 6) Swal.showValidationMessage('Password minimal 6 karakter');
      return val;
    }
  }).then(function(r) {
    if (r.isConfirmed) {
      document.getElementById('resetUserId').value = userId;
      document.getElementById('resetNewPassword').value = r.value;
      document.getElementById('resetForm').submit();
    }
  });
}
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
?>
