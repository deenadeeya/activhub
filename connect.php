<?php
	
	$conn = mysqli_connect("localhost", "root", "") or die("dbconfig:cannot connect");
	mysqli_select_db($conn,"activhub") or die ("dbconfig: cannot select DB");
?>