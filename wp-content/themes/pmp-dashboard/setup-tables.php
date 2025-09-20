<?php
/**
 * Manual Database Setup Script
 * Run this once to create the content management tables
 */

// Include WordPress
require_once('../../../wp-config.php');

echo "<h2>🔧 Creating Content Management Tables</h2>";

// Create the tables
PMP_Content_Database::create_tables();

// Verify tables were created
if (PMP_Content_Database::verify_tables()) {
    echo "<p style='color: green;'>✅ All 7 content management tables created successfully!</p>";
    
    // Show table stats
    $stats = PMP_Content_Database::get_table_stats();
    echo "<h3>📊 Table Statistics:</h3>";
    foreach ($stats as $table => $count) {
        echo "<p>• {$table}: {$count} rows</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Some tables failed to create. Check error logs.</p>";
}

echo "<p><a href='../../../wp-admin/'>← Back to WordPress Admin</a></p>";
?>
