<?php

require_once('dbconn.php');

function getStudent($conn, $student_id) {
	$query="select iclicker_id, school_id, first_name, last_name, email, username from students where student_id=?";
	$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
	$stmt->bind_param("i", $student_id);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	
	$stmt->bind_result($iclicker_id, $school_id, $firstname, $lastname, $email, $username);
	if (!$stmt->fetch()) {
		// raise an error
		//exit("No section exists for course_id $course_id");
	}
	$stmt->close();
	return array($student_id, $iclicker_id, $school_id, $firstname, $lastname, $email, $username);
}

function getSectionForCourseId($conn, $course_id) {
	// Returns: $section_id
	$query = "
		SELECT section_id
		FROM sections WHERE
		course_id = ?;
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
		FROM sections WHERE
		course_id = ?;
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
		SELECT  session_id, section_id, session_date, session_tag, post_processed FROM sessions WHERE session_id = ?;
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
		WHERE courses.course_id = sections.course_id
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
select s.section_id 
from students s, registrations r
where s.section_id = r.section_id
and s.student_id = ?
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
create temporary table answercounts
select s.session_id, s.session_tag, s.session_date, r.student_id, count(*) as answers
from responses r, questions q, sessions s
where 1
and q.question_id = r.question_id
and q.session_id = s.session_id
and q.ignore_question = 0
and r.number_of_attempts > 0
and r.student_id = ?
and s.section_id = ?
group by s.session_id
order by s.session_tag
";

	$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
	$stmt->bind_param("ii", $student_id, $section_id);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	$stmt->close();
}

function createCorrectCounts($conn, $student_id, $section_id) {
	$query="
create temporary table correctCounts
SELECT s.session_id, s.session_date, s.session_tag, r.student_id, count(*) as numcorrect
FROM questions q, responses r, sessions s
WHERE q.question_id = r.question_id
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
create temporary table qcounts
select s.session_id, s.session_tag, s.session_date, count(*) as count
from sessions s, questions q
where 1
and s.session_id = q.session_id
and q.ignore_question = 0
and s.section_id = ?
group by s.session_id
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

	$query="
select q.session_id, q.session_tag, q.session_date, q.count, a.answers, a.answers/q.count, c.numcorrect
from qcounts q 
	left outer join answercounts a
		on q.session_id = a.session_id
	left outer join correctCounts c
		on q.session_id = c.session_id
order by q.session_tag asc
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
	echo th('pct answ');
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
		if ($pct < 0.75) {
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
select section_id
from registrations
where student_id = ?
";
	$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
	$stmt->bind_param("i", $student_id);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	$stmt->bind_result($section_id);
	$stmt->fetch();
	$stmt->close();
	return $section_id;
}

?>