<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "admin")) {
		header("Location: home.php");
	}

	$section_id = $_GET["section_id"];
	
	createHeader("Administrator Settings");
	
	$query = "
		SELECT course_name, section_number 
		FROM courses, sections 
		WHERE 1
		AND section_id = ?
		AND courses.course_id = sections.course_id
	";
	$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
	$stmt->bind_param("i", $section_id);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	
	$stmt->bind_result($course_name, $section_number);
	$stmt->fetch();
	$stmt->close();
	
	$query = "
		SELECT display_screen, threshold 
		FROM sections 
		WHERE section_id = ?
	";
	$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
	$stmt->bind_param("i", $section_id);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	
	$stmt->bind_result($display_screen, $threshold);

	$checked = "";
	$full = '';
	$left = '';
	$right = '';
		
	while ($stmt->fetch()) {

		if ($display_screen == 'full'){
			$full = "checked";
		}
		if ($display_screen == 'right'){
			$right = "checked";
		}
		if ($display_screen == 'left'){
			$left = "checked";
		}
	}
	
?>
<body>
	<div>
		<h1>Administrator Settings</h1>
			<h2><?= "$course_name"?> <br> Section <?= "$section_number"?></h2>
			<h5>These settings are only changed for this section.</h5>
			<table class='collection'>
			<form action='endeditadminsettings.php' method='post'>
			<input type="hidden" name="section_id" value="<?= $_GET["section_id"] ?>"/>
			<tr>
					<td>Screen Display</td>
					<td><input id='1' type='radio' name='display_screen' value='full' <?= $full?> ><label for='1'>Full Screen</label></td>
					<td><input id='2' type='radio' name='display_screen' value='left' <?= $left?> ><label for='2'>Left Half</label></td>
					<td><input id='3' type='radio' name='display_screen' value='right' <?= $right?> ><label for='3'>Right Half</label></td>
				</tr>
				
				<tr>
					<td>Passing Threshold For Students(0-100%): </td>
					<td><input type='text' name='threshold' value='<?= $threshold?>'></td>
				</tr>

				<tr>
					<td></td><td></td>
					<td><input type='submit' value='Update Settings'></td>
					<td></td>
				</tr>		
			</table>
	</div>
</body>
<?php
	$conn->close();
	createFooter();
?>