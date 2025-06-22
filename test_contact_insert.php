<?php
include 'config/connect.php';

echo "=== CONTACT NUMBER TEST ===\n";

// Test 1: Check current database column definition
echo "\n1. Current column definition:\n";
$result = $conn->query("DESCRIBE events");
while ($row = $result->fetch_assoc()) {
    if ($row['Field'] == 'contact_number') {
        echo "   contact_number: " . $row['Type'] . "\n";
        break;
    }
}

// Test 2: Test inserting a new phone number
echo "\n2. Testing insert with long phone number:\n";
$test_phone = "011-65546909";
echo "   Phone to insert: '$test_phone' (length: " . strlen($test_phone) . ")\n";

// Simulate the exact insert from add_events.php
$event_name = "Test Event";
$event_start_date = "2025-07-01";
$event_end_date = "2025-07-01";
$event_venue = "Test Venue";
$event_description = "Test Description";
$event_type = "other";
$is_mandatory = 0;
$auto_register_members = 0;
$visibility = "public";
$max_participants = null;
$registration_deadline = "2025-06-30";
$contact_number = $test_phone;
$group_id = null;
$eligible_years = null;
$created_by = "test123";

$stmt = $conn->prepare("INSERT INTO events (event_name, event_start_date, event_end_date, event_venue, event_description, event_type, is_mandatory, auto_register_members, visibility, max_participants, registration_deadline, contact_number, group_id, eligible_years, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if ($stmt) {
    $stmt->bind_param("ssssssiisisssss", $event_name, $event_start_date, $event_end_date, $event_venue, $event_description, $event_type, $is_mandatory, $auto_register_members, $visibility, $max_participants, $registration_deadline, $contact_number, $group_id, $eligible_years, $created_by);
    
    if ($stmt->execute()) {
        $new_id = $conn->insert_id;
        echo "   ✅ Insert successful. Event ID: $new_id\n";
        
        // Check what was actually stored
        $check = $conn->query("SELECT contact_number FROM events WHERE event_id = $new_id");
        $stored = $check->fetch_assoc();
        echo "   Stored phone: '" . $stored['contact_number'] . "' (length: " . strlen($stored['contact_number']) . ")\n";
        
        // Clean up test data
        $conn->query("DELETE FROM events WHERE event_id = $new_id");
        echo "   🧹 Test data cleaned up\n";
    } else {
        echo "   ❌ Insert failed: " . $stmt->error . "\n";
    }
} else {
    echo "   ❌ Prepare failed: " . $conn->error . "\n";
}

// Test 3: Check if there are any warnings
echo "\n3. Checking for MySQL warnings:\n";
$warnings = $conn->query("SHOW WARNINGS");
if ($warnings && $warnings->num_rows > 0) {
    while ($warning = $warnings->fetch_assoc()) {
        echo "   " . $warning['Level'] . ": " . $warning['Message'] . "\n";
    }
} else {
    echo "   No warnings\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>
