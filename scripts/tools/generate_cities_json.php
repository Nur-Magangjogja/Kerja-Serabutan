<?php
/**
 * Script: generate_cities_json.php
 * Menggenerate cities.json dari data regencies di indonesia.sql
 * Jalankan: php scripts/generate_cities_json.php
 */

$sqlFile  = __DIR__ . '/../database/seeders/sql/indonesia.sql';
$outDir   = __DIR__ . '/../database/seeders/data';
$outCities = $outDir . '/cities.json';

if (!file_exists($sqlFile)) {
    die("File tidak ditemukan: $sqlFile\n");
}

if (!is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

$sql = file_get_contents($sqlFile);

// ── Parse provinces ─────────────────────────────────────────────────────────
preg_match('/INSERT INTO `provinces`[^;]+;/s', $sql, $m);
$provinces = [];
if ($m) {
    preg_match_all('/\((\d+),\s*\'([^\']+)\'\)/', $m[0], $rows, PREG_SET_ORDER);
    foreach ($rows as $r) {
        $provinces[(int)$r[1]] = trim($r[2]);
    }
}
echo "Provinces parsed: " . count($provinces) . "\n";

// ── Parse regencies → cities ─────────────────────────────────────────────────
preg_match('/INSERT INTO `regencies`[^;]+;/s', $sql, $m2);
$cities = [];
if ($m2) {
    preg_match_all('/\((\d+),\s*(\d+),\s*\'([^\']+)\',\s*\'([^\']+)\'\)/', $m2[0], $rows2, PREG_SET_ORDER);
    foreach ($rows2 as $idx => $r) {
        $id          = (int)$r[1];
        $provinceId  = (int)$r[2];
        $name        = trim($r[3]);
        $type        = trim($r[4]);
        $provinceName = $provinces[$provinceId] ?? null;

        $cities[] = [
            'id'          => $id,
            'name'        => $name,
            'type'        => $type,
            'province'    => $provinceName,
            'code'        => (string)$id,
            'latitude'    => null,
            'longitude'   => null,
        ];
    }
}
echo "Cities parsed: " . count($cities) . "\n";

file_put_contents($outCities, json_encode($cities, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "✅ cities.json berhasil dibuat di: $outCities\n";

// ── Parse kecamatan.sql → districts.json ─────────────────────────────────────
$kecFile    = __DIR__ . '/../database/seeders/sql/kecamatan.sql';
$outDistricts = $outDir . '/districts.json';

if (!file_exists($kecFile)) {
    echo "⚠️  kecamatan.sql tidak ditemukan — districts.json tidak dibuat.\n";
    exit(0);
}

echo "Parsing kecamatan.sql (bisa memakan waktu)...\n";
$kecSql = file_get_contents($kecFile);

// Format: ('110101', '1101', 'Bakongan')
preg_match_all("/\('([^']+)',\s*'([^']+)',\s*'([^']+)'\)/", $kecSql, $kecRows, PREG_SET_ORDER);

$districts = [];
foreach ($kecRows as $r) {
    $districts[] = [
        'code'      => $r[1],   // id kecamatan mis. '110101'
        'city_code' => $r[2],   // regency_id mis. '1101'
        'name'      => $r[3],   // nama kecamatan
    ];
}
echo "Districts parsed: " . count($districts) . "\n";

if (count($districts) > 0) {
    file_put_contents($outDistricts, json_encode($districts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "✅ districts.json berhasil dibuat di: $outDistricts\n";
} else {
    echo "⚠️  Tidak ada data district yang berhasil di-parse.\n";
}
