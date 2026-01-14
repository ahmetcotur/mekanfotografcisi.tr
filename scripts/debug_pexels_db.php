<?php
require_once __DIR__ . '/../includes/database.php';

$db = new DatabaseClient();

try {
    $all = $db->query("SELECT id, image_url, is_visible FROM pexels_images");
    echo "Total images in DB: " . count($all) . "\n";

    $active = 0;
    $passive = 0;
    foreach ($all as $row) {
        if ($row['is_visible'] === true || $row['is_visible'] === 't' || $row['is_visible'] === 1) {
            $active++;
        } else {
            $passive++;
            echo "Passive Image: " . $row['image_url'] . " (ID: " . $row['id'] . ", Raw Value: " . var_export($row['is_visible'], true) . ")\n";
        }
    }

    echo "Active: $active, Passive: $passive\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
