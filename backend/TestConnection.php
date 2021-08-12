<?php

include('DBConnection.php');

$db = new DBConnection();
$conn = $db->getConnection();

if ($conn->ping()) {
    echo ("Our connection is ok!\n"); 
  } else {
     echo ("Error: %s\n", $conn->error); 
  }
