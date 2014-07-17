<?php

function closeConn($conn) {
        $conn->close();
}

function connect() {
        $dbhost = 'localhost';
        $dbuser = 'root';
        $dbpass = '';
        $dbname = 'cs147';

        $conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname) or die ('Error connecting to mysql ' . mysqli_connect_error());
        return $conn;
}

?>