<?php
// Check PHP extensions required for Excel import
echo "<h2>PHP Extension Check</h2>";

$required_extensions = [
    'zip' => 'ZipArchive class (required for .xlsx files)',
    'xml' => 'XML extension (required for Excel parsing)',
    'xmlreader' => 'XMLReader class (required for Excel parsing)',
    'gd' => 'GD extension (optional, for image handling)'
];

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Extension</th><th>Status</th><th>Description</th></tr>";

foreach ($required_extensions as $ext => $desc) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? "<span style='color: green;'>✓ Loaded</span>" : "<span style='color: red;'>✗ Not Loaded</span>";
    echo "<tr><td>$ext</td><td>$status</td><td>$desc</td></tr>";
}

echo "</table>";

// Check if ZipArchive class exists
echo "<h3>ZipArchive Class Check:</h3>";
if (class_exists('ZipArchive')) {
    echo "<span style='color: green;'>✓ ZipArchive class is available</span>";
} else {
    echo "<span style='color: red;'>✗ ZipArchive class is NOT available</span>";
}

// Show PHP version
echo "<h3>PHP Version: " . phpversion() . "</h3>";

// Show loaded extensions
echo "<h3>All Loaded Extensions:</h3>";
$extensions = get_loaded_extensions();
sort($extensions);
echo "<p>" . implode(', ', $extensions) . "</p>";
?>
