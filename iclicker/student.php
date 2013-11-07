<?php
require_once("pageutils.php");
require_once("dbutils.php");
require_once("loginutils.php");
$conn = connect();
	
$student_id=$_GET['student_id'];
$section_id=$_GET['section_id'];

list($student_id, $iclicker_id, $school_id, $firstname, $lastname, $email, $username)=getStudent($conn, $student_id);

if (!isCookieValidLoginWithType($conn, "admin")) {
	header("Location: home.php");
}
	
createHead("Student");

echo "<div>\n";

echo "<h2>username: $username <br> clicker: $iclicker_id</h2>";

// create a temporary table of counts
createQcounts($conn, $section_id);

createAnswers($conn, $student_id, $section_id);

$query="
select q.session_id, q.session_tag, q.session_date, q.count, a.answers
from qcounts q left outer join answercounts a
	on q.session_id = a.session_id
order by q.session_tag asc
";


$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
$stmt->execute() or die("Couldn't execute query. " . $conn->error);
$stmt->bind_result($session_id, $session_tag, $session_date, $qcount, $answers);

echo "<table border=1><tr>";
echo th('day');
echo th('date');
echo th('tag');
echo th('total');
echo th('answered');
echo "</tr>";

while ($stmt->fetch()) {
	echo "<tr>";
	echo td(dayOfWeek($session_date));
	echo td($session_date);
	echo td($session_tag);
	echo td($qcount);
	if ($answers=='') {
		echo td(0);
	} else {
		echo td($answers);
	}
	echo "</tr>";
}

$stmt->close();

$conn->close();
createFooter();

?>