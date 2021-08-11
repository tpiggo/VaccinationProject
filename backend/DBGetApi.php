<?php
include('DBConnection.php');

/**
 * Every function is acting as a path which will dictating the GET requests
 * Entry point here:
 */

if ($_GET['type'] == 'get_rows') {
    echo get_rows($_GET['entity']);
} else {
    echo json_encode(array('status' => '404', 'response' => 'no route found.'));
}


// Get the types of rows and return them
function get_rows($entity) {
    switch($entity) {
        case 'person':
            $sql_query = <<<'SQL'
                SELECT
                    column_name,
                    column_type    # or data_type 
                FROM information_schema.columns 
                WHERE table_name='person' or table_name='posttoprovince' or table_name='province' or table_name='phoneaddress' or table_name='citizenOf' or table_name='infectionrecords'
                group by column_name
                SQL;
            return query_data($sql_query);
        case 'public-health-worker':
            $sql_query = <<<'SQL'
                SELECT
                    column_name,
                    column_type    # or data_type 
                FROM information_schema.columns 
                WHERE table_name='person' or table_name='posttoprovince' or table_name='province' or table_name='phoneaddress' or table_name='citizenOf' or table_name='healthCareWorker'
                group by column_name
                SQL;
            return query_data($sql_query);
        case 'public-health-facility':
            $sql_query = <<<'SQL'
                SELECT
                    column_name,
                    column_type    # or data_type 
                FROM information_schema.columns 
                where table_name='vaccinationFacility' or table_name='posttoprovince' or table_name='phoneaddress'
                group by column_name;
                SQL;
            return query_data($sql_query);
        case 'vaccination-type':
            $sql_query = <<<'SQL'
                SELECT
                    column_name,
                    column_type    # or data_type 
                FROM information_schema.columns 
                where table_name='vaccine' or table_name='vaccienstatushistory'
                group by column_name
                SQL;
            return query_data($sql_query);
        case 'covid-19-infection-variant-type':
            $sql_query = <<<'SQL'
                SELECT
                    column_name,
                    column_type    # or data_type 
                FROM information_schema.columns 
                WHERE table_name='covidvariants'
                group by column_name
                SQL;
            return query_data($sql_query);
        case 'group-age':
            $sql_query = <<<'SQL'
                SELECT
                    column_name,
                    column_type    # or data_type 
                FROM information_schema.columns 
                WHERE table_name='group_age'
                group by column_name
                SQL;
            return query_data($sql_query);
        default:
            return json_encode(array('response' => 'Error! No match found'));
    } 
}
    


// Accessor function for our database
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