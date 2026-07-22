<?php
if (!isset($pageTitle)) $pageTitle = 'BI Dashboard';
if (!isset($content))   $content  = '';
$current_page = basename($_SERVER['PHP_SELF']);

// Ambil info user dari session
$email    = $_SESSION['user']['email'] ?? 'Guest';
$name     = $_SESSION['user']['name']  ?? $email;
$initials = strtoupper(substr($name, 0, 2));
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?> — BI Dashboard</title>
  <link rel="stylesheet" href="assets/style.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<!-- ── SIDEBAR ── -->
<div class="sidebar" id="sidebar">

  <h2><span>BI Dashboard</span></h2>

  <nav style="flex:1; overflow-y:auto; padding: 10px 0;">

    <a href="dashboard_ma.php" class="<?= $current_page === 'dashboard_ma.php' ? 'active' : '' ?>">
      <span class="nav-icon">◈</span>
      <span>Performa Alat</span>
    </a>

    <?php if (in_array($_SESSION['user']['role'] ?? '', ['admin','manager'])): ?>
    <a href="manage_users.php" class="<?= $current_page === 'manage_users.php' ? 'active' : '' ?>">
      <span class="nav-icon">👤</span>
      <span>Kelola Akun</span>
    </a>
    <?php endif; ?>

  </nav>

  <div class="sidebar-footer">
    <a href="#" class="logout-link" style="display:flex;align-items:center;gap:12px;padding:11px 20px;color:var(--text-muted);text-decoration:none;font-size:14px;transition:all .2s;border:none;border-radius:0;">
      <span class="nav-icon">⏻</span>
      <span>Logout</span>
    </a>
  </div>

</div>

<!-- ── HEADER BAR ── -->
<div class="header-bar" id="headerBar">

  <button class="btn-toggle" id="btnToggle">☰</button>

  <div class="header-breadcrumb">
    BI Dashboard <span>/</span> <?= htmlspecialchars($pageTitle) ?>
  </div>

  <div class="header-spacer"></div>

  <div class="live-badge">
    <div class="live-dot"></div>
    Live
  </div>

  <div class="user-info">
    <div class="user-avatar"><?= htmlspecialchars($initials) ?></div>
    <span class="user-email"><?= htmlspecialchars($name) ?></span>
    <a href="#" class="logout-link">Keluar</a>
  </div>

</div>

<!-- ── CONTENT ── -->
<div class="content" id="mainContent">
  <?= $content ?>
</div>

<script>
  // Sidebar collapse
  document.getElementById('btnToggle').addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('collapsed');
    document.getElementById('headerBar').classList.toggle('collapsed');
    document.getElementById('mainContent').classList.toggle('collapsed');
  });

  // SweetAlert logout — semua .logout-link
  document.querySelectorAll('.logout-link, .sidebar-footer a').forEach(function(link) {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      Swal.fire({
        title: 'Konfirmasi Logout',
        text: 'Apakah Anda yakin ingin keluar dari sistem?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Keluar',
        cancelButtonText: 'Batal'
      }).then(function(result) {
        if (result.isConfirmed) window.location.href = 'logout.php';
      });
    });
  });
</script>
</body>
</html>
