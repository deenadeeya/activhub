<?php
include 'config/connect.php';

// Test current database structure for contact_number field
echo "=== CHECKING EVENTS TABLE STRUCTURE ===\n";
$result = $conn->query("DESCRIBE events");
while ($row = $result->fetch_assoc()) {
    if ($row['Field'] == 'contact_number') {
        echo "✅ contact_number field: " . $row['Type'] . "\n";
        break;
    }
}

// Test current database structure for advisor_ic field
echo "\n=== CHECKING COCURRICULAR_GROUPS TABLE STRUCTURE ===\n";
$result2 = $conn->query("DESCRIBE cocurricular_groups");
while ($row = $result2->fetch_assoc()) {
    if ($row['Field'] == 'advisor_ic') {
        echo "✅ advisor_ic field: " . $row['Type'] . "\n";
        break;
    }
}

// Check actual data in events table
echo "\n=== CHECKING ACTUAL DATA ===\n";
$result3 = $conn->query("SELECT event_name, contact_number FROM events WHERE contact_number IS NOT NULL LIMIT 3");
while ($row = $result3->fetch_assoc()) {
    echo "Event: " . $row['event_name'] . " | Contact: " . $row['contact_number'] . " | Length: " . strlen($row['contact_number']) . "\n";
}
?>
