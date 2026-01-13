<?php
require_once __DIR__ . '/includes/database.php';
$db = new DatabaseClient();

// Check if a 'forms' or 'messages' table exists or creating one is needed
// I'll list all tables first
$tables = $db->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
print_r($tables);
