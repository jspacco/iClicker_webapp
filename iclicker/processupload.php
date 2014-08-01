<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
		
	checkAdmin($conn);

	/*
	How to increase memory in php.ini:

	http://stackoverflow.com/questions/4399138/upper-memory-limit-for-php-apache

	How to do error checking for the file upload and post sizes:

	http://www.flynsarmy.com/2013/10/_files-and-_post-empty-in-php-when-uploading-large-files/

	Eventually we want clearer error messages about this.
	*/

	if (empty($_FILES) || empty($_POST) ){
		echo "Error" . ini_get("upload_max_filesize") . "    " . ini_get("memory_limit") . "    " . ini_get("post_max_size");
	}

	$section_id = $_POST['section_id']; 

	createHeader("Submitting upload...", true, "<a href=\"section.php?section_id=$section_id\"> Back to Sessions and Assignments</a>");

	echo "<br><br> section id: $section_id <br><br>";

	if (!isset($_FILES["file"])) {
		echo "Error with file uploading. Exiting...<br>";
		exit();
	}

	if ($_FILES["file"]["error"] > 0) {
		echo "Error: " . $_FILES["file"]["error"] . ". Exiting...<br>";
		exit();
	} 

	echo "Upload: " . $_FILES["file"]["name"] . "<br>";
	echo "Type: " . $_FILES["file"]["type"] . "<br>";
	echo "Size: " . $_FILES["file"]["size"] . "<br>";
	echo "Stored in " . $_FILES["file"]["tmp_name"] . "<br>";
	echo "<br>";
			
	$file = $_FILES["file"]["tmp_name"];
	echo "Attempting to unzip " . $file . "<br>";
	$zip = zip_open($file);

	if (!is_resource($zip)) {
		echo "Couldn't unzip file. Error: " . $zip . "<br>";
		exit();
	}

	echo "Unzipped file successfully.<br>";
	echo "<br>";
	while ($zip_entry = zip_read($zip)) {
		if (zip_entry_open($zip, $zip_entry, "r")) {
			echo "<b>" . zip_entry_name($zip_entry) . "</b><br>";
			$charL=substr(zip_entry_name($zip_entry), -15, -14);	
			//echo "$charL";
			switch (substr(zip_entry_name($zip_entry), -4)) {
				case ".CSV":
				case ".csv":
					$res = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
				if (zip_entry_filesize($zip_entry)==0) {
					echo "Empty, skipping!<br><br>";
					continue;
				}
				//Checking if a certain character is L, if it is not then ignore the file.
				if (strcasecmp($charL, "L")!=0) {
					echo "Ignoring config file, remoteID file, etc.<br><br>";
					continue;
				}
				//echo "res is $res<br><br><p><p>";
				if ($res !== FALSE && $res !== "") {
					// uploading the csv
					echo "zip entry name: " . zip_entry_name($zip_entry) . "<br><p><p>";
					$path = explode("/", zip_entry_name($zip_entry));

					$filename=array_pop($path);
					$filename=str_replace(".csv", "", $filename);

					echo "filename is $filename<br>";

					$session_tag=substr($filename, 0, 11);

					echo "session tag: $session_tag<p><p><p>";

					$session_year = substr($filename, 1, 2);
					$session_month = substr($filename, 3, 2);
					$session_day = substr($filename, 5, 2);
					$session_hour = substr($filename, 7, 2);
					$session_minute = substr($filename, 9, 2);
					$session_date = $session_month . "/" . $session_day . "/" . $session_year . " " . $session_hour . ":" . $session_minute;
									
					echo "Date: " . $session_date . "<br><br>";

					$rows = explode("\n", $res);
									
					// print the rows (for debugging)
					// $count = count($rows);
					// for ($i = 0; $i < $count; $i++) {
					// echo $i . "    " . $rows[$i] . "<br>";
					// }
									
					//
					//	Setup question information
					//
					$num_questions = 0;
									
					$questions = array();
					// get the question names
					$elements = explode(",", $rows[1]);
					$count = count($elements);
					for ($i = 3; $i < $count; $i += 6) {
						$questions[($i - 3) / 6] = array("question_name" => $elements[$i]);
						$num_questions++;
					}
					// get the start time
					$elements = explode(",", $rows[2]);
					$count = count($elements);
					for ($i = 3; $i < $count; $i += 6) {
						$questions[($i - 3) / 6]["start_time"] = $elements[$i];
					}
					// get the stop time
					$elements = explode(",", $rows[3]);
					$count = count($elements);
					for ($i = 3; $i < $count; $i += 6) {
						$questions[($i - 3) / 6]["stop_time"] = $elements[$i];
					}
					// get the correct answers
					$elements = explode(",", $rows[4]);
					$count = count($elements);
					for ($i = 3; $i < $count; $i += 6) {
						$questions[($i - 3) / 6]["correct_answer"] = $elements[$i];
					}
									
					// FOR DEBUGGING
					// echo "<b>QUESTIONS</b><br>";
					// var_dump($questions);
					// echo "<br>";
									
					//Setup student information									
					$responses = array();
					//Have to find where the student responses start, because .csv only has rows for responses received
					// (i.e. if no one picks A, there's no row for the response A)
					$start = 5; // starting row if there's no responses
					while (TRUE) {
						$elements = explode(",", $rows[$start]);
						if (substr($elements[0], 0, 8) === "Response") {
							$start++;
						} else {
							// start of the student rows
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
						// create a response entry for each student and questions combination
						for ($j = 0; $j < $num_questions; $j++) {
							$responses[($i * $num_questions) + $j] = array("iclicker_id" => $elements[0]);
							$responses[($i * $num_questions) + $j]["response"] = $elements[3 + ($j * 6)];
							$responses[($i * $num_questions) + $j]["final_answer_time"] = $elements[5 + ($j * 6)];
							$responses[($i * $num_questions) + $j]["number_of_attempts"] = $elements[6 + ($j * 6)];
							$responses[($i * $num_questions) + $j]["first_response"] = $elements[7 + ($j * 6)];
							$responses[($i * $num_questions) + $j]["time"] = $elements[8 + ($j * 6)];
						}
					}
									
					// echo "<b>RESPONSES</b><br>";
					// var_dump($responses);
					// echo "<br>";
									
					//Put everything in the database									
					echo "<br>";
									
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
							INSERT INTO sessions (section_id, session_date, session_tag)
							VALUES (?, ?, ?)
						";
										
						$stmt = $conn->prepare($query) or die("Couldn't prepare 'sessions insert' statement. " . $conn->error);
						$stmt->bind_param("iss", $section_id, $session_date, $session_tag);
						$stmt->execute() or die("Couldn't execute 'sessions insert' statement. " . $conn->error);
						$stmt->close();

						// Is this actually asking for last_update_id()?
						$query = "
							SELECT session_id 
							FROM sessions 
							WHERE 1
							AND section_id = ? 
							AND session_date = ?
						";
										
						$stmt = $conn->prepare($query) or die("Couldn't prepare 'sessions select id' statement. " . $conn->error);
						$stmt->bind_param("is", $section_id, $session_date);
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
										
						// get the question_id's
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
								WHERE question_id = ?
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
						}
										
					}
									
					//Questions									
					for ($i = 0; $i < $num_questions; $i++) {
						//We have to create the variables up here, otherwise get a cannot pass parameter error if we create in bind_param
						//$basename = substr($filename, 4); // the base of all screen and chart picture filenames
						$basename = $filename;
						$question_number = $i + 1;
						$screen_picture = $basename . "_Q" . ($i + 1) . ".jpg";
						$chart_picture = $basename . "_C" . ($i + 1) . ".jpg";
										
						if (!isset($questions[$i]["correct_answer"])) {
							$questions[$i]["correct_answer"]="";
						}
						if (!isset($questions[$i]["start_time"])) {
							$questions[$i]["start_time"]="";
						}
						if (!isset($questions[$i]["stop_time"])) {
							$questions[$i]["stop_time"]="";
						}
						
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
							//There is no record for this student, have to create one
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
										
						//Have to select the question_id
						$question_number = ($i % $num_questions) + 1;
										
						$query = "
							SELECT question_id 
							FROM questions 
							WHERE 1
							AND session_id = ? 
							AND	question_number = ?
						";
										
						$stmt = $conn->prepare($query) or die("Couldn't prepare 'questions select' statement. " . $conn->error);
						$stmt->bind_param("ii", $session_id, $question_number);
						$stmt->execute() or die("Couldn't prepare 'questions select' statement. " . $conn->error);										
						$stmt->bind_result($question_id);
						$stmt->fetch();										
						$stmt->close();
										
						$query = "
							INSERT INTO responses (question_id, student_id, number_of_attempts, first_response, time, response, final_answer_time)
							VALUES (?, ?, ?, ?, ?, ?, ?)
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
						if (!file_exists("pictures/$section_id")){
							mkdir ("pictures/$section_id");
						}
						
						$dest = "pictures/$section_id/" . $path["basename"];
						echo "Copying " . zip_entry_name($zip_entry) . " to " . $dest . ".<br>";
														
						file_put_contents($dest, $res);
						chmod($dest, 0744);
					}
				break;
				default:
					echo "Unsupported filetype for file: " . zip_entry_name($zip_entry) . "<br>";
					break;
			}
		}
		zip_entry_close($zip_entry);
	}			
	zip_close($zip);

	$conn->close();
	createFooter();
?>