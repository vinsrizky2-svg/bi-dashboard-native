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
include 'layout.php';
?>
