<?php
include('DBConnection.php');

/**
 * Every function is acting as a path which will dictating the GET requests
 * Entry point here:
 */


// $log_file_name = "db_output.txt";
// $of = fopen($log_file_name, 'a');
$of2 = fopen('post_output.txt','a' );
fwrite($of2, "---Start---- \n");
$json = file_get_contents('php://input');
$json_obj = json_decode($json);
fwrite($of2, "data. " . json_encode($json_obj->data) . "\n");
fwrite($of2, "type. " . $json_obj->type . "\n");


switch($json_obj->type) {
    case 'person':
        echo createPersonRoute($json_obj->data);
        break;
    case 'publicHealthWorker':
        echo createPHWRoute($json_obj->data);
        break;
    case 'publicHealthFacility':
        echo createPHFRoute($json_obj->data);
        break;
    case 'vaccinationType':
        echo createVaxTypeRoute($json_obj->data);
        break;
    case 'groupAge':
        echo createGroupAgeRoute($json_obj->data);
        break;
    case 'province': 
        echo createProvince($json_obj->data);
        break;
    case 'covid19InfectionVariantType':
        echo createVariant($json_obj->data);
        break;
    default:
        echo json_encode(array('response' => 'Error! Type did not match'));
        break;
}

fclose($of2);
/**
 * pseudoRoutes
 * Assumed that all the data coming in has been checked on the frontend.
 * This is an application on a secure server. We do not worry about 'bad guys';
 */
function createPersonRoute($data) {
    
    return json_encode(array('response' => 'Hit the person route'));
}

function createPHWRoute($data) {

    return json_encode(array('response' => 'Hit the phw route'));
}

function createPHFRoute($data) {

    return json_encode(array('response' => 'Hit the phf route'));
}

function createVaxTypeRoute($data) {
    if (count($data) < 3 or count($data) % 2 != 1)  {
        return json_encode(array('response' => 'Error! Bad input!'));
    }
    $name = $data[0]->value;
    $statuses = array();
    for ($x = 1; $x < count($data); $x+=2) {
        $statuses[$x/2] = array('change' => $data[$x]->value, 'status' => $data[$x+1]->value);
    }

    $dOfA = '2000-01-01';
    $dOfS = '2000-01-01';
    // find the last approval and last suspension
    for ($x = count($statuses)-1; $x >= 0; $x--){
        if ($statuses[$x]['status'] == 'approved' && $statuses[$x]['change']> $dOfA) {
            $dOfA = $statuses[$x]['change'];
        } elseif ($statuses[$x]['status'] == 'suspended' && $statuses[$x]['change']> $dOfS) {
            $dOfS = $statuses[$x]['change'];
        }
    }

    if ($dOfA == '2000-01-01') {
        $dOfA = null;
    }

    if ($dOfS == '2000-01-01') {
        $dOfS = null;
    }

    $status = $dOfS>$dOfA?'suspended':'approved';
    // The vaccine has been built. Now Perform the necessary transactions
    $dbconn = new DBConnection();
    $conn = $dbconn->getConnection();
    try {
        $query = $conn->prepare('INSERT into vaccine (vaxType, dateOfApproval, dateOfSuspension, vaxStatus) values (?,?,?,?)');
        $query->bind_param('ssss', $name, $dOfA, $dOfS, $status);
        $query->execute();

        // Add all the statuses
        for($i = 0; $i <  count($statuses); $i++) {
            $date = $statuses[$i]['change'];
            $stat = $statuses[$i]['status'];
            $rel_query = $conn->prepare('INSERT into vaccinestatushistory (vaccineType, dateOfStatusChange, vaxStatus) values (?,?,?)');
            $rel_query->bind_param('sss', $name, $date, $stat);
            $rel_query->execute();
        }

        return json_encode(array('response' => 'Success'));
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        return json_encode(array('response' => 'Error. Failed to insert'));
    }
}

function createGroupAgeRoute($data) {
    if (count($data) != 3 ) {
        return json_encode(array('response' => 'Error! Bad input!'));
    }
    $group = $data[0]->value;
    $bottom  = $data[1]->value;
    $top = $data[2]->value;
    $sql_query = <<<SQL
        INSERT into Group_Age values ($group, $bottom , $top)
    SQL;    
    return json_encode(perform_insertion($sql_query));
}

function createProvince($data) {
    if (count($data) != 1) {
        return json_encode(array('response' => 'Error! Bad input!'));
    }
    $insertable = $data[0]->value;

    $sql_query = <<<SQL
        INSERT into Province (province) values ("$insertable")
        SQL;
    return json_encode(perform_insertion($sql_query));
}

function createVariant($data) {
    if (count($data) != 1) {
        return json_encode(array('response' => 'Error! Bad input!'));
    }

    $insertable = $data[0]->value;
    $sql_query = <<<SQL
        INSERT into covidvariants values ("$insertable")
        SQL;

    $of2 = fopen('post_output.txt','a' );
    fwrite($of2, $sql_query . "\n");
    fclose($of2);
    return json_encode(perform_insertion($sql_query));
}

function perform_insertion($statement) {
    $dbconn = new DBConnection();
    $conn = $dbconn->getConnection();
    // on the connection, we need to check if the insertion worked.
    // Check affected_rows
    if ($conn->query($statement) == FALSE) {
        return array('response' => 'Failed to insert.');
    }
    return array('response' => 'Success');
}