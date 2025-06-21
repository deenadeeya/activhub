<?php
// Test script to verify the class query works correctly
include_once 'config/connect.php';

echo "<h3>Testing Class Query</h3>";

// Test the query with class join
$query = "SELECT a.*, s.student_fname, s.student_class, c.class_name, t.teacher_fname as approved_by_name
          FROM cocu_activities a
          JOIN student s ON a.student_ic = s.student_ic
          LEFT JOIN class c ON s.student_class = c.class_id
          LEFT JOIN teacher t ON a.approved_by = t.teacher_ic
          ORDER BY a.created_at DESC
          LIMIT 5";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Database error: " . mysqli_error($conn));
}

echo "<table border='1'>";
echo "<tr><th>Student Name</th><th>Class ID</th><th>Class Name</th><th>Activity Name</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['student_fname']) . "</td>";
    echo "<td>" . htmlspecialchars($row['student_class']) . "</td>";
    echo "<td>" . htmlspecialchars($row['class_name'] ?? 'No class name') . "</td>";
    echo "<td>" . htmlspecialchars($row['activity_name']) . "</td>";
    echo "</tr>";
}

echo "</table>";

// Also test class table structure
echo "<h3>Class Table Structure</h3>";
$class_query = "SELECT * FROM class ORDER BY class_year, class_name";
$class_result = mysqli_query($conn, $class_query);

echo "<table border='1'>";
echo "<tr><th>Class ID</th><th>Class Year</th><th>Class Name</th><th>Head Teacher</th></tr>";

while ($class_row = $class_result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($class_row['class_id']) . "</td>";
    echo "<td>" . htmlspecialchars($class_row['class_year']) . "</td>";
    echo "<td>" . htmlspecialchars($class_row['class_name']) . "</td>";
    echo "<td>" . htmlspecialchars($class_row['head_teacher'] ?? 'N/A') . "</td>";
    echo "</tr>";
}

echo "</table>";
?>
