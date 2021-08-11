<?php
include('DBConnection.php');

$_GET['']

// Accessor function
function query_data($sql_query) {
    $file_name = "db_output.txt";
    $of = fopen($file_name, 'a');
    $output;
    try {
        $conn = new DBConnection();
        $result = $conn->query($sql_query); 
        
        if (!empty($result) && $result->num_rows > 0) {
            $to_ret = array();
            while ($row = $result->fetch_assoc()) {
                array_push($to_ret, $row);
            }
            $output = json_encode(array('response' => $to_ret));
        } else {
            $output = json_encode(array('response' => 'None found!'));
        }
    } catch (\Exception $e) {
        $output = json_encode(array('response' => 'Error!'));
        fwrite($of, date("Y-m-d h:i:sa") . ": Error!! \n");
    } finally {
        fclose($of);
    }
    return $output;
}