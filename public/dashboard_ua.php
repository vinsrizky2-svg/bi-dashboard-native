<?php
require_once __DIR__ . '/../src/config.php';
require_login();

$pageTitle = 'Use of Availability';
ob_start();
?>

<script type="module"
  src="https://prod-apsoutheast-b.online.tableau.com/javascripts/api/tableau.embedding.3.latest.min.js">
</script>

<div class="page-header">
  <div>
    <h2>Use of Availability</h2>
    <p>Monitoring penggunaan ketersediaan seluruh unit</p>
  </div>
</div>

<tableau-viz
  src="https://prod-apsoutheast-b.online.tableau.com/t/fing70215-a4d44b1bbc/views/VisualisasiFIX1/DASHBOARDUA"
  width="100%" height="1520"
  hide-tabs toolbar="hidden">
</tableau-viz>

<?php
$content = ob_get_clean();
include 'layout.php';
?>
