<?php
require_once __DIR__ . '/../src/config.php';
require_login();

$pageTitle = 'Physical Availability';
ob_start();
?>

<script type="module"
  src="https://prod-apsoutheast-b.online.tableau.com/javascripts/api/tableau.embedding.3.latest.min.js">
</script>

<div class="page-header">
  <div>
    <h2>Physical Availability</h2>
    <p>Monitoring ketersediaan fisik seluruh unit</p>
  </div>
</div>

<tableau-viz
  src="https://prod-apsoutheast-b.online.tableau.com/t/fing70215-a4d44b1bbc/views/VisualisasiFIX1/DASHBOARDPA"
  width="100%" height="1520"
  hide-tabs toolbar="hidden">
</tableau-viz>

<?php
$content = ob_get_clean();
include 'layout.php';
?>
