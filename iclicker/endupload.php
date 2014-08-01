<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "admin")) {
		header("Location: home.php");
	}
	
	createHeader("Submitting upload...");
?>
<body>
	<div>
<?php
	if (!isset($_FILES["file"])) {
		echo "Error with file uploading. Exiting...<br>";
		exit();
	}

	if ($_FILES["file"]["error"] > 0) {
		echo "Error: " . $_FILES["file"]["error"] . ". Exiting...<br>";
		exit();
	} else {
		echo "Upload: " . $_FILES["file"]["name"] . "<br>";
		echo "Type: " . $_FILES["file"]["type"] . "<br>";
		echo "Size: " . $_FILES["file"]["size"] . "<br>";
		echo "Stored in: " . $_FILES["file"]["tmp_name"] . "<br>";
		echo "<br>";
		
		$file = $_FILES["file"]["tmp_name"];
		echo "Attempting to unzip " . $file . ".<br>";
		$zip = zip_open($file);
		if (is_resource($zip)) {
			echo "Unzipped file successfully.<br>";
			echo "<br>";
			while ($zip_entry = zip_read($zip)) {
				if (zip_entry_open($zip, $zip_entry, "r")) {
					echo "<b>" . zip_entry_name($zip_entry) . "</b><br>";
					
					switch (substr(zip_entry_name($zip_entry), -4)) {
						case ".CSV":
						case ".csv":
							$res = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
							if ($res !== FALSE && $res !== "") {
								//Uploading the csv
								$path = explode("/", zip_entry_name($zip_entry));
								$foldername = explode("-", $path[0]); // split by / then by - to get course information
								$course_name = $foldername[0];
								if (sizeof($foldername) < 3) {
									$course_number = 0;
									$section_number = 0;
								} else {
									$course_number = $foldername[1];
									$section_number = $foldername[2];
								}
								$filename = $path[1];
								$session_year = substr($filename, 5, 2);
								$session_month = substr($filename, 7, 2);
								$session_day = substr($filename, 9, 2);
								$session_hour = substr($filename, 11, 2);
								$session_minute = substr($filename, 13, 2);
								$session_date = $session_month . "/" . $session_day . "/" . $session_year . " " . $session_hour . ":" . $session_minute;
								
								echo $course_name . " " . $course_number . " section " . $section_number . "<br>";
								echo "Date: " . $session_date . "<br>";
								
								$rows = explode("\n", $res);
								
								// print the rows (for debugging)
								// $count = count($rows);
								// for ($i = 0; $i < $count; $i++) {
									// echo $i . "    " . $rows[$i] . "<br>";
								// }
								
								//Setup question information
								$num_questions = 0;
								$questions = array();
								//Get the question names
								$elements = explode(",", $rows[1]);
								$count = count($elements);
								for ($i = 3; $i < $count; $i += 6) {
									$questions[($i - 3) / 6] = array("question_name" => $elements[$i]);
									$num_questions++;
								}
								//Get the start time
								$elements = explode(",", $rows[2]);
								$count = count($elements);
								for ($i = 3; $i < $count; $i += 6) {
									$questions[($i - 3) / 6]["start_time"] = $elements[$i];
								}
								//Get the stop time
								$elements = explode(",", $rows[3]);
								$count = count($elements);
								for ($i = 3; $i < $count; $i += 6) {
									$questions[($i - 3) / 6]["stop_time"] = $elements[$i];
								}
								//Get the correct answers
								$elements = explode(",", $rows[4]);
								$count = count($elements);
								for ($i = 3; $i < $count; $i += 6) {
									$questions[($i - 3) / 6]["correct_answer"] = $elements[$i];
								}
								
								// For Debugging
								// echo "<b>QUESTIONS</b><br>";
								// var_dump($questions);
								// echo "<br>";

								//	Setup student information
								$responses = array();
								//We have to find where the student responses start, because .csv only has rows for responses received
								//(i.e. if no one picks A, there's no row for the response A)
								$start = 5; //Starting row if there's no responses
								while (TRUE) {
									$elements = explode(",", $rows[$start]);
									if (substr($elements[0], 0, 8) === "Response") {
										$start++;
									} else {
										//Start of the student rows
										break;
									}
									if ($start >= count($rows) - 1) { // -1 because last row is blank
										echo "<b>Couldn't find student response rows! Exiting...</b><br>";
										exit();
									}
								}
								$count = count($rows) - 1;
								for ($i = 0; $start + $i < $count; $i++) {
									$elements = explode(",", $rows[$start + $i]);
									//Create a response entry for each student and questions combination
									for ($j = 0; $j < $num_questions; $j++) {
										$responses[($i * $num_questions) + $j] = array("iclicker_id" => $elements[0]);
										$responses[($i * $num_questions) + $j]["response"] = $elements[3 + ($j * 6)];
										$responses[($i * $num_questions) + $j]["final_answer_time"] = $elements[5 + ($j * 6)];
										$responses[($i * $num_questions) + $j]["number_of_attempts"] = $elements[6 + ($j * 6)];
										$responses[($i * $num_questions) + $j]["first_response"] = $elements[7 + ($j * 6)];
										$responses[($i * $num_questions) + $j]["time"] = $elements[8 + ($j * 6)];
									}
								}
								// For Debugging
								// echo "<b>RESPONSES</b><br>";
								// var_dump($responses);
								// echo "<br>";
							
								//Put everything in the database							
								echo "<br>";
								
								require_once("dbutils.php");
								$conn = connect();
								
								//	courses
								
								//First we check if the course is in the database
								$course_id;
								
								$query = "
									SELECT course_id, course_name, course_number 
									FROM courses 
									WHERE 1
									AND course_name = ? 
									AND	course_number = ?
								";
								
								$stmt = $conn->prepare($query) or die("Couldn't prepare 'courses' statement. " . $conn->error);
								$stmt->bind_param("ss", $course_name, $course_number);
								$stmt->execute() or die("Couldn't execute 'courses' statement. " . $conn->error);
								$stmt->store_result();
								
								if ($stmt->num_rows == 0) {
									//This course is new, so we need to insert it into the database
									
									$query = "
										INSERT INTO courses (course_name, course_number) 
										VALUES (?, ?)
									";
									
									$stmt = $conn->prepare($query) or die("Couldn't prepare 'courses insert' statement. " . $conn->error);
									$stmt->bind_param("ss", $course_name, $course_number);
									$stmt->execute() or die("Couldn't execute 'courses insert' statement. " . $conn->error);
									$stmt->close();

									$query = "
										SELECT course_id 
										FROM courses 
										WHERE 1
										AND course_name = ? 
										AND	course_number = ?;
									";
									
									$stmt = $conn->prepare($query) or die("Couldn't prepare 'courses id select' statement. " . $conn->error);
									$stmt->bind_param("ss", $course_name, $course_number);
									$stmt->execute() or die("Couldn't execute 'courses id select' statement. " . $conn->error);
									$stmt->bind_result($course_id);
									$stmt->fetch();									
									$stmt->close();
								} else {
									$stmt->bind_result($course_id, $course_name, $course_number);
									$stmt->fetch();
								}
								
								//Sections (similar to how we did courses: check if there's already a record, otherwise create one; then get the primary key)
								$section_id;
								
								$query = "
									SELECT section_id, course_id, section_number 
									FROM sections 
									WHERE 1
									AND course_id = ? 
									AND	section_number = ?
								";
								
								$stmt = $conn->prepare($query) or die("Couldn't prepare 'sections' statement. " . $conn->error);
								$stmt->bind_param("ii", $course_id, $section_number);
								$stmt->execute() or die("Couldn't execute 'sections' statement. " . $conn->error);	
								$stmt->store_result();
								
								if ($stmt->num_rows == 0) {
									
									$query = "
										INSERT INTO sections (course_id, section_number, year_offered)
										VALUES (?, ?, ?)
									";
									
									$stmt = $conn->prepare($query) or die("Couldn't prepare 'sections insert' statement. " . $conn->error);
									$stmt->bind_param("iii", $course_id, $section_number, $session_year);
									$stmt->execute() or die("Couldn't execute 'sections insert' statement. " . $conn->error);
									$stmt->close();
									
									$query = "
										SELECT section_id 
										FROM sections 
										WHERE 1
										AND course_id = ?
										AND section_number = ?
										AND year_offered = ?
									";
									
									$stmt = $conn->prepare($query) or die("Couldn't prepare 'sections select id' statement. " . $conn->error);
									$stmt->bind_param("iii", $course_id, $section_number, $session_year);
									$stmt->execute() or die("Couldn't execute 'sections select id' statement. " . $conn->error);
									$stmt->bind_result($section_id);
									$stmt->fetch();									
									$stmt->close();
								} else {
									$stmt->bind_result($section_id, $course_id, $section_number);
									$stmt->fetch();
								}
								
								//Sessions								
								$session_id;
								
								$query = "
									SELECT session_id 
									FROM sessions 
									WHERE 1
									AND section_id = ?
									AND	session_date = ?
								";
								
								$stmt = $conn->prepare($query) or die("Couldn't prepare 'sessions' statement. " . $conn->error);
								$stmt->bind_param("is", $section_id, $session_date);
								$stmt->execute() or die("Couldn't execute 'sessions' statement. " . $conn->error);
								$stmt->store_result();
								
								if ($stmt->num_rows == 0) {
								
									$query = "
										INSERT INTO sessions (section_id, session_date)
										VALUES (?, ?)
									";
									
									$stmt = $conn->prepare($query) or die("Couldn't prepare 'sessions insert' statement. " . $conn->error);
									$stmt->bind_param("is", $section_id, $session_date);
									$stmt->execute() or die("Couldn't execute 'sessions insert' statement. " . $conn->error);
									$stmt->close();
									
									$query = "
										SELECT session_id 
										FROM sessions 
										WHERE section_id = ?;
									";
									
									$stmt = $conn->prepare($query) or die("Couldn't prepare 'sessions select id' statement. " . $conn->error);
									$stmt->bind_param("i", $section_id);
									$stmt->execute() or die("Couldn't execute 'sessions select id' statement. " . $conn->error);									
									$stmt->bind_result($session_id);
									$stmt->fetch();
									$stmt->close();
								} else {
									echo "Data for this session already exists, removing...<br>";									
									$stmt->bind_result($session_id);
									$stmt->fetch();
									$stmt->close();
									
									//Data for this session already exists, delete current session data and upload new session data
									//Get the question_id's
									
									$query = "
										SELECT question_id 
										FROM questions 
										WHERE session_id = ?
									";
									
									$stmt = $conn->prepare($query) or die("Couldn't prepare 'questions select session id' statement. " . $conn->error);
									$stmt->bind_param("i", $session_id);
									$stmt->execute() or die("Couldn't execute 'questions select session id' statement. " . $conn->error);								
									$stmt->bind_result($question_id);
									
									$q_ids = array();
									
									while ($stmt->fetch()) {
										array_push($q_ids, $question_id);
									}
									$stmt->close();
									
									foreach ($q_ids as $question_id) {

										$query = "
											DELETE FROM responses 
											WHERE question_id = ?;
										";
										
										$stmt = $conn->prepare($query) or die("Couldn't prepare 'responses delete' statement. " . $conn->error);
										$stmt->bind_param("i", $question_id);
										$stmt->execute() or die("Couldn't prepare 'responses delete' statement. " . $conn->error);
										$stmt->close();
										
										$query = "
											DELETE FROM questions 
											WHERE question_id = ?
										";
										
										$stmt = $conn->prepare($query) or die("Couldn't prepare 'questions delete' statement. " . $conn->error);
										$stmt->bind_param("i", $question_id);
										$stmt->execute() or die("Couldn't prepare 'questions delete' statement. " . $conn->error);
										$stmt->close();
										
										// not deleting students for now
									}
									
								}

								//Questions								
								for ($i = 0; $i < $num_questions; $i++) {
									//We have to create the variables up here, otherwise get a cannot pass parameter error if we create in bind_param
									$basename = substr($filename, 4); //The base of all screen and chart picture filenames
									$question_number = $i + 1;
									$screen_picture = $basename . "_Q" . ($i + 1) . ".jpg";
									$chart_picture = $basename . "_C" . ($i + 1) . ".jpg";
									
									$query = "
										INSERT INTO questions (session_id, question_number, question_name, screen_picture, chart_picture, correct_answer, start_time, stop_time)
										VALUES (?, ?, ?, ?, ?, ?, ?, ?)
									";
									
									$stmt = $conn->prepare($query) or die("Couldn't prepare 'questions' statement. " . $conn->error);
									$stmt->bind_param("iissssss", $session_id, $question_number, $questions[$i]["question_name"], $screen_picture, $chart_picture, $questions[$i]["correct_answer"], $questions[$i]["start_time"], $questions[$i]["stop_time"]);
									$stmt->execute() or die("Couldn't execute 'questions' statement. " . $conn->error);
									$stmt->close();
								}

								//Students and responses								
								$count = count($responses);
								for ($i = 0; $i < $count; $i++) {
									//See if there is already a student entry
									$student_id;
									
									$query = "
										SELECT student_id 
										FROM students 
										WHERE iclicker_id = ?
									";
									
									$stmt = $conn->prepare($query) or die("Couldn't prepare 'students' statement. " . $conn->error);
									$stmt->bind_param("s", $responses[$i]["iclicker_id"]);
									$stmt->execute() or die("Couldn't prepare 'students' statement. " . $conn->error);									
									$stmt->store_result();
									
									if ($stmt->num_rows == 0) {
										//No record for this student, have to create one
										
										$query = "
											INSERT INTO students (iclicker_id)
											VALUES (?)
										";
										
										$stmt = $conn->prepare($query) or die("Couldn't prepare 'students insert' statement. " . $conn->error);
										$stmt->bind_param("s", $responses[$i]["iclicker_id"]);
										$stmt->execute() or die("Couldn't execute 'students insert' statement. " . $conn->error);
										$stmt->close();
										
										$query = "
											SELECT student_id 
											FROM students 
											WHERE iclicker_id = ?
										";
										
										$stmt = $conn->prepare($query) or die("Couldn't prepare 'students' statement. " . $conn->error);
										$stmt->bind_param("i", $responses[$i]["iclicker_id"]);
										$stmt->execute() or die("Couldn't prepare 'students' statement. " . $conn->error);										
										$stmt->bind_result($student_id);
										$stmt->fetch();
										$stmt->close();
									} else {
										$stmt->bind_result($student_id);
										$stmt->fetch();
										$stmt->close();									
									}
									
									// have to select the question_id
									$question_number = ($i % $num_questions) + 1;
									
									$query = "
										SELECT question_id 
										FROM questions 
										WHERE 1
										AND session_id = ?
										AND question_number = ?
									";
									
									$stmt = $conn->prepare($query) or die("Couldn't prepare 'questions select' statement. " . $conn->error);
									$stmt->bind_param("ii", $session_id, $question_number);
									$stmt->execute() or die("Couldn't prepare 'questions select' statement. " . $conn->error);									
									$stmt->bind_result($question_id);
									$stmt->fetch();
									$stmt->close();
									
									$query = "
										INSERT INTO responses (question_id, student_id, number_of_attempts, first_response, time, response, final_answer_time)
										VALUES (?, ?, ?, ?, ?, ?, ?);
									";
									
									$stmt = $conn->prepare($query) or die("Couldn't prepare 'responses' statement. " . $conn->error);
									$stmt->bind_param("iiisdsd", $question_id, $student_id, $responses[$i]["number_of_attempts"], $responses[$i]["first_response"], $responses[$i]["time"], $responses[$i]["response"], $responses[$i]["final_answer_time"]);
									$stmt->execute() or die("Couldn't execute 'responses' statement. " . $conn->error);
									$stmt->close();
								}
								
							} else {
								echo "Error reading .csv file. Exiting...<br>";
								exit();
							}
							break;
						case ".JPG":
						case ".jpg":
							$res = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
							if ($res !== FALSE && $res !== "") {
								$path = pathinfo(zip_entry_name($zip_entry));
								$dest = "pictures/" . $path["basename"];
								
								echo "Copying " . zip_entry_name($zip_entry) . " to " . $dest . ".<br>";
								
								file_put_contents($dest, $res);
								chmod($dest, 0755);
							}
							break;
						default:
							echo "Unsupported filetype for file: " . zip_entry_name($zip_entry) . "<br>";
							break;
					}					
				}
				
				zip_entry_close($zip_entry);
			}
		} else {
			echo "Couldn't unzip file. Error: " . $zip . "<br>";
		}
		
		zip_close($zip);
	}
?>
	</div>
</body>
<?php
	$conn->close();
	createFooter();
?>