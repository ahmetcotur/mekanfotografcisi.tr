<?php
/**
 * Verification Script: Pexels Visibility Fix
 */
require_once __DIR__ . '/../includes/database.php';
spl_autoload_register(function ($class) {
    if (strpos($class, 'Core\\') === 0) {
        include __DIR__ . '/../includes/Core/' . str_replace('\\', '/', substr($class, 5)) . '.php';
    }
});

$service = new Core\PexelsService();
$activePhotos = $service->getActivePhotos();

echo "Total Active Photos in Service: " . count($activePhotos) . "\n";

// These IDs were found to be passive in the previous debug run
$passiveIds = [17, 18, 21, 13, 2, 16, 48, 49, 75, 79, 80];

$foundPassiveCount = 0;
foreach ($activePhotos as $photo) {
    if (in_array($photo['id'], $passiveIds)) {
        echo "❌ ERROR: Passive Photo ID " . $photo['id'] . " found in active list!\n";
        $foundPassiveCount++;
    }
}

if ($foundPassiveCount === 0 && count($activePhotos) > 0) {
    echo "✅ SUCCESS: No passive photos found in the active list.\n";
} else if (count($activePhotos) === 0) {
    echo "⚠️ WARNING: No active photos found at all. Check if any are marked active in DB.\n";
} else {
    echo "❌ FAILURE: Found $foundPassiveCount passive photos.\n";
}
