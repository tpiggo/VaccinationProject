<?php

// Need to change this based on the db we are using.
$servername = "localhost"; 
$username = "root";
$password = "";
$dbname = "hso";
// Not necessarily needed, but we can always have it.
$file_name = "db_output.txt";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    // In append mode, our personal DB log file
    $of = fopen($file_name, 'a');

    if ($conn->connect_error) {
        fwrite($of, date("Y-m-d h:i:sa") . ": Failed to connect to $servername, $dbname! \n");
        echo json_encode(array('response' => 'mysqli is NOT connected and working'));
        fclose($of);
        die("Connection failed: " -> $conn->connect_error);
    }

    // In append mode
    $of = fopen($file_name, 'a');

    fwrite($of, date("Y-m-d h:i:sa") . ": Connection is working to $servername, $dbname! \n");

    $query = "SELECT * FROM managers";
    $result = $conn->query($query); 

    if (!empty($result) && $result->num_rows > 0) {
        $to_ret = array();
        while ($row = $result->fetch_assoc()) {
            array_push($to_ret, $row);
        }
        echo json_encode(array('response' => $to_ret));
    } else {
        echo json_encode(array('response' => 'None found!'));
    }
    fclose($of);
    $conn->close();
} catch (Exception $e) {
    echo json_encode(array('response' => 'Error!'));
} 
