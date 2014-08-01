<?php
require_once("pageutils.php");
require_once("dbutils.php");
require_once("loginutils.php");
$conn = connect();
	
if (!isCookieValidLoginWithType($conn, "admin")) {
	header("Location: home.php");
}

$display_screen = $_POST["display_screen"];
$threshold = $_POST["threshold"];
$section_id = $_POST["section_id"];

//Update Administrator Settings

	$query = "
		UPDATE sections 
		SET display_screen = ?, threshold = ?
		WHERE section_id = ?
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare update statement" . $conn->error);
	$stmt->bind_param('sdi', $display_screen, $threshold, $section_id);
	$stmt->execute() or die("Couldn't execute update statement" . $conn->error);

header("Location: adminsettings.php?section_id=$_POST[section_id]&message=Updated Settings!");

$conn->close();
?>