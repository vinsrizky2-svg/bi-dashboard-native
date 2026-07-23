<?php
require_once __DIR__ . '/../src/config.php';
require_login();

$pageTitle = 'Performa Alat';
ob_start();
?>

<script type="module"
  src="https://prod-apsoutheast-b.online.tableau.com/javascripts/api/tableau.embedding.3.latest.min.js">
</script>

<div class="page-header">
  <div>
    <h2>Performa Alat</h2>
    <p>Monitoring ketersediaan mekanikal seluruh unit</p>
  </div>
</div>

<tableau-viz
  src="https://prod-apsoutheast-b.online.tableau.com/t/reinzki21-4b36b3f909/views/DashboardPerforma/Performa"
  width="100%" height="1520"
  hide-tabs toolbar="hidden">
</tableau-viz>

<?php
$content = ob_get_clean();

// ── Profiling: catat penggunaan memori halaman dashboard ──
$memUsage = round(memory_get_usage() / 1024, 2);
$memPeak  = round(memory_get_peak_usage() / 1024, 2);
error_log("[profiling] dashboard_ma.php — Memory: {$memUsage} KB, Peak: {$memPeak} KB");

include 'layout.php';
?>
