<?php

require_once('dbconn.php');

function getStudent($conn, $student_id) {
	$query = "
		SELECT iclicker_id, school_id, first_name, last_name, email, username 
		FROM students 
		WHERE student_id=?
	";
	$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
	$stmt->bind_param("i", $student_id);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	
	$stmt->bind_result($iclicker_id, $school_id, $firstname, $lastname, $email, $username);
	if (!$stmt->fetch()) {
		//raise an error
		//exit("No section exists for course_id $course_id");
	}
	$stmt->close();
	return array($student_id, $iclicker_id, $school_id, $firstname, $lastname, $email, $username);
}

function getSectionForCourseId($conn, $course_id) {
	// Returns: $section_id
	$query = "
		SELECT section_id
		FROM sections 
		WHERE course_id = ?;
	";
	$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
	$stmt->bind_param("i", $course_id);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	
	$stmt->bind_result($section_id);
	if (!$stmt->fetch()) {
		// raise an error
		//exit("No section exists for course_id $course_id");
	}
	$stmt->close();
	return $section_id;
}

function countSectionsByCourseId($conn, $course_id) {
	// Returns: $count
	$query = "
		SELECT count(*) 
		FROM sections 
		WHERE course_id = ?;
	";
	$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
	$stmt->bind_param("i", $course_id);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	
	$stmt->bind_result($count);
	$stmt->fetch();
	return $count;
}

function lookupSessionBySessionId($conn, $session_id) {
	$query = "
		SELECT session_id, section_id, session_date, session_tag, post_processed 
		FROM sessions 
		WHERE session_id = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'section' query. " . $conn->error);
	$stmt->bind_param("i", $session_id);
	$stmt->execute() or die("Couldn't execute 'section' query. " . $conn->error);
	
	$stmt->bind_result($session_id, $section_id, $session_date, $session_tag, $post_processed);
	$stmt->fetch();
	$stmt->close();
	return array($session_id, $section_id, $session_date, $session_tag, $post_processed);
}

function lookupCourseBySectionId($conn, $section_id) {
// Returns: list($course_id, $course_name, $course_number)

	$query = "
		SELECT courses.course_id, course_name, course_number
		FROM courses, sections
		WHERE 1
		AND courses.course_id = sections.course_id
		AND section_id = ?;
	";
	$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
	$stmt->bind_param("i", $section_id);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	
	$stmt->bind_result($course_id, $course_name, $course_number);
	$stmt->fetch();
	return array($course_id, $course_name, $course_number);
}

function lookupSectionID($student_id) {
	$query="
		SELECT s.section_id 
		FROM students s, registrations r
		WHERE 1
		AND s.section_id = r.section_id
		AND s.student_id = ?
	";

	$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
	$stmt->bind_param("i", $student_id);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	
	$stmt->bind_result($section_id);
	$stmt->fetch();
	$stmt->close();
	return $section_id;
}

function createAnswers($conn, $student_id, $section_id) {
	$query="
		create temporary table answercounts$section_id
		SELECT s.session_id, s.session_tag, s.session_date, r.student_id, count(*) AS answers
		FROM responses r, questions q, sessions s
		WHERE 1
		AND q.question_id = r.question_id
		AND q.session_id = s.session_id
		AND q.ignore_question = 0
		AND r.number_of_attempts > 0
		AND r.student_id = ?
		AND s.section_id = ?
		GROUP BY s.session_id
		ORDER BY s.session_tag
	";

	$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
	$stmt->bind_param("ii", $student_id, $section_id);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	$stmt->close();
}

function createCorrectCounts($conn, $student_id, $section_id) {
	$query="
		create temporary table correctCounts$section_id
		SELECT s.session_id, s.session_date, s.session_tag, r.student_id, count(*) AS numcorrect
		FROM questions q, responses r, sessions s
		WHERE 1
		AND q.question_id = r.question_id
		AND q.session_id = s.session_id
		AND q.correct_answer REGEXP r.response
		AND r.student_id = ?
		AND s.section_id = ?
		GROUP BY q.session_id, r.student_id
	";

	$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
	$stmt->bind_param("ii", $student_id, $section_id);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	$stmt->close();

}

function createQcounts($conn, $section_id) {
	$query="
		create temporary table qcounts$section_id
		SELECT s.session_id, s.session_tag, s.session_date, count(*) AS count
		FROM sessions s, questions q
		WHERE 1
		AND s.session_id = q.session_id
		AND q.ignore_question = 0
		AND q.single_question = 0
		AND s.section_id = ?
		GROUP BY s.session_id
	";

	$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
	$stmt->bind_param("i", $section_id);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	$stmt->close();
}

function printClickerParticipation($conn, $student_id, $section_id) {
	createQcounts($conn, $section_id);
	createAnswers($conn, $student_id, $section_id);
	createCorrectCounts($conn, $student_id, $section_id);

	$query = "
		SELECT threshold 
		FROM sections 
		WHERE section_id = ?
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
	$stmt->bind_param("i", $section_id);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	
	$stmt->bind_result($threshold);
	
	$stmt->fetch();
	$stmt->close();
	
	$query="
		SELECT q.session_id, q.session_tag, q.session_date, q.count, a.answers, a.answers/q.count, c.numcorrect
		FROM qcounts$section_id q 
		LEFT OUTER JOIN answercounts$section_id a
		ON q.session_id = a.session_id
		LEFT OUTER JOIN correctCounts$section_id c
		ON q.session_id = c.session_id
		ORDER BY q.session_tag asc
	";


	$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	$stmt->bind_result($session_id, $session_tag, $session_date, $qcount, $answers, $pct, $numcorrect);	
	
	echo "<table border=1><tr>";
	echo th('day');
	echo th('date');
	echo th('tag');
	echo th('total');
	echo th('answered');
	echo th('% answered');
	echo th('correct');
	echo "</tr>";

	while ($stmt->fetch()) {
		echo "<tr>";
		echo td(dayOfWeek($session_date));
		echo td($session_date);
		echo td($session_tag);
		echo td($qcount);
		if ($answers=='') {
			echo td('<font color=red>0</font>');
		} else {
			echo td($answers);
		}
		if ($pct < $threshold) {
			echo td("<font color=red>$pct</font>");
		} else {
			echo td($pct);
		}
		if ($numcorrect=='') {
			echo td('<font color=red>0</font>');
		} else {
			echo td($numcorrect);
		}
		echo "</tr>";
	}
	$stmt->close();

}

function getSectionIdByStudentId($conn, $student_id) {
	$query="
		SELECT section_id
		FROM registrations
		WHERE student_id = ?
	";
	$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
	$stmt->bind_param("i", $student_id);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	$stmt->bind_result($section_id);
	$stmt->fetch();
	$stmt->close();
	return $section_id;
}
/**s
function deselectCheckbox(obj) {
   var fries = document.getElementsByName('fries');
   if(obj.id =='hotdog') //Or check for obj.type == 'radio'
   {
      for(var i=0; i<fries.length; i++)
        fries[i].checked = true;
   }
   else{
      for(var i=0; i<fries.length; i++){
         if(fries[i].id != obj.id){
           fries[i].checked = !obj.checked;
           break;
         }
      }
   }
   for(var i=0; i<ignore.length; i++){
   if ($single_question == 1 and $ignore_question == 1){
		$single_question = 0;
   }
   onclick = deselectCheckbox(this)
   
}
*/
?>