<?php
require_once __DIR__ . '/../../src/config.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

$type   = $_GET['type']   ?? '';
$tahun  = $_GET['tahun']  ?? '';
$lokasi = $_GET['lokasi'] ?? '';
$bulan  = $_GET['bulan']  ?? '';
$jenis  = $_GET['jenis']  ?? '';

$db = getDB();

function buildWhere(array $filters): array {
    $conds  = [];
    $params = [];
    if (!empty($filters['tahun'])) {
        $conds[]          = 'EXTRACT(YEAR FROM waktu_bulan) = :tahun';
        $params[':tahun'] = (int)$filters['tahun'];
    }
    if (!empty($filters['lokasi'])) {
        $conds[]           = 'lokasi_kerja_alat = :lokasi';
        $params[':lokasi'] = $filters['lokasi'];
    }
    if (!empty($filters['bulan'])) {
        $conds[]          = 'EXTRACT(MONTH FROM waktu_bulan) = :bulan';
        $params[':bulan'] = (int)$filters['bulan'];
    }
    if (!empty($filters['jenis'])) {
        $conds[]          = 'jenis_alatberat = :jenis';
        $params[':jenis'] = $filters['jenis'];
    }
    $where = count($conds) ? 'WHERE ' . implode(' AND ', $conds) : '';
    return [$where, $params];
}

try {
    switch ($type) {

        case 'tahun':
            $rows = $db->query("
                SELECT DISTINCT EXTRACT(YEAR FROM waktu_bulan)::INT AS tahun
                FROM data_ketersediaan ORDER BY tahun ASC
            ")->fetchAll(PDO::FETCH_COLUMN);
            echo json_encode($rows);
            break;

        case 'lokasi':
            [$where, $params] = buildWhere(['tahun' => $tahun]);
            $stmt = $db->prepare("
                SELECT DISTINCT lokasi_kerja_alat, provinsi
                FROM data_ketersediaan $where
                ORDER BY provinsi ASC, lokasi_kerja_alat ASC
            ");
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
            break;

        case 'bulan':
            [$where, $params] = buildWhere(['tahun' => $tahun, 'lokasi' => $lokasi]);
            $stmt = $db->prepare("
                SELECT DISTINCT
                    EXTRACT(MONTH FROM waktu_bulan)::INT  AS bulan_num,
                    TRIM(TO_CHAR(waktu_bulan, 'TMMonth')) AS bulan_nama
                FROM data_ketersediaan $where
                ORDER BY bulan_num ASC
            ");
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
            break;

        case 'jenis':
            [$where, $params] = buildWhere(['tahun' => $tahun, 'lokasi' => $lokasi, 'bulan' => $bulan]);
            $stmt = $db->prepare("
                SELECT DISTINCT jenis_alatberat
                FROM data_ketersediaan $where
                ORDER BY jenis_alatberat ASC
            ");
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));
            break;

        case 'nomor':
            [$where, $params] = buildWhere(['tahun' => $tahun, 'lokasi' => $lokasi, 'bulan' => $bulan, 'jenis' => $jenis]);
            $stmt = $db->prepare("
                SELECT DISTINCT nomor_alatberat
                FROM data_ketersediaan $where
                ORDER BY nomor_alatberat ASC
            ");
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));
            break;

        case 'provinsi':
            // Only relevant values — dimulai dari lokasi kerja alat
            [$where, $params] = buildWhere([
                'tahun'  => $tahun,
                'lokasi' => $lokasi,
                'bulan'  => $bulan,
                'jenis'  => $jenis,
            ]);
            $stmt = $db->prepare("
                SELECT DISTINCT provinsi
                FROM data_ketersediaan $where
                ORDER BY provinsi ASC
            ");
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Parameter type tidak valid.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Query gagal.', 'detail' => $e->getMessage()]);
}
