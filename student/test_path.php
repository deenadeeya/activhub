<?php
// Simple path test - place this in the student folder and access it via web browser
echo "<h1>Path Test</h1>";
echo "<p>Current directory: " . __DIR__ . "</p>";
echo "<p>Parent directory: " . dirname(__DIR__) . "</p>";
echo "<p>Cocurricular board file exists: " . (file_exists('../cocurricular/cocurricular_board.php') ? 'YES' : 'NO') . "</p>";
echo "<p>Full path to cocurricular board: " . realpath('../cocurricular/cocurricular_board.php') . "</p>";

echo "<br><a href='../cocurricular/cocurricular_board.php'>Test Link to Cocurricular Board</a>";
?>
