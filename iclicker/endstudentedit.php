<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "student")) {
		header("Location: home.php");
	}
	
	$school_id = $_POST["school_id"];
	$email = $_POST["email"];
	$student_id = getStudentIdFromCookie($conn);
	
	$query = "
		SELECT section_id
		FROM registrations
		WHERE student_id = ?
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
	$stmt->bind_param("i", $student_id);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	$stmt->bind_result($section_id);
	
	$regsect = array();
	while ($stmt->fetch()){
		array_push($regsect, $section_id);
	};				
	
	$sect = $_POST["checkedcourses"];
	
	foreach ($sect as $section_id) {//for each section_id that is checked 
		if (!in_array($section_id, $regsect)) {	//if section_id is not registered insert into db
			$query = "
				INSERT INTO registrations (student_id, section_id)
				VALUES (?, ?)
			";
				
			$stmt = $conn->prepare($query) or die("Couldn't prepare  query. " . $conn->error);
			$stmt->bind_param("ii", $student_id, $section_id);
			$stmt->execute() or die("Couldn't execute 'new' query. " . $conn->error);
			$stmt->close();
		}
	}
	
	foreach ($regsect as $section_id) {//for each section_id that is not checked 
		if (!in_array($section_id, $sect)) {	//if section_id is not registered insert into db
			$query = "
				DELETE FROM registrations 
				WHERE 1
				AND student_id = ? 
				AND section_id = ?
			";
				
			$stmt = $conn->prepare($query) or die("Couldn't prepare  query. " . $conn->error);
			$stmt->bind_param("ii", $student_id, $section_id);
			$stmt->execute() or die("Couldn't execute 'new2' query. " . $conn->error);
			$stmt->close();
		}
	}
	
	$query = "
		UPDATE students 
		SET email = ?, school_id = ? 
		WHERE student_id = ?
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't execute 'email' query. " . $conn->error);
	$stmt->bind_param("ssi", $email, $school_id, $student_id);
	$result = $stmt->execute() or die("Couldn't execute 'email' query. " . $conn->error);
	
	if ($result) {
		header("Location: home.php");
	}
	
	createHeader("End Edit");
?>
<body>
	<div>
		<p>Edit was not successful!<br></p>
	</div>
</body>
<?php
	logs($conn, $student_id);
	$conn->close();
	createFooter();
?>