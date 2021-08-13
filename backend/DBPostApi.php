<?php
include('DBConnection.php');

/**
 * Every function is acting as a path which will dictating the GET requests
 * Entry point here:
 */

$json = file_get_contents('php://input');
$json_obj = json_decode($json);



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

function insert_Person($conn, $fname, $lname, $phone, $email, $dob, $passport, $medicare, $address, $postalcode, $country, $city) {
    try {
        $query = $conn->prepare('INSERT into phoneaddress (phone, address) values (?,?)');
        $query->bind_param('ss', $phone, $address);
        $rq = $query->execute();
        if ($rq === false) {
            $sql_query = "SELECT address from phoneaddress where phone='$phone'";
            $result = $conn->query($sql_query);
            if (!empty($result) && $result->num_rows > 0) {
                $db_out = array();
                while ($row = $result->fetch_assoc()) {
                    array_push($db_out, $row);
                }
                if ($db_out[0]['address'] !== $address) {
                    return array('response' => 'Error. Address does not match.');
                }
            } elseif (empty($result)) {
                return array("response" => 'Fatal: Error');
            }
            
        }
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        return array('response' => 'Error. Failed to insert phoneaddress');
    }

    try {
        $pquery = $conn->prepare(
            'INSERT into 
            person (city, dateofbirth, firstname, lastname, emailaddress, postalCode, phone, medicarenumber, passport) 
            values (?,?,?,?,?,?,?,?,?)');
        $pquery->bind_param('sssssssss', $city, $dob, $fname, $lname, $email, $postalcode, $phone, $medicare, $passport);
        $rq = $pquery->execute();
        if ($rq === false) {
            return array('response' => 'Error. Failed to insert, person passport already exists.');
        }
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        return array('response' => 'Error. Failed to insert person.');
    }
    return array('response' => 'Success');
}

/**
 * pseudoRoutes
 * Assumed that all the data coming in has been checked on the frontend.
 * This is an application on a secure server. We do not worry about 'bad guys';
 */
function createPersonRoute($data) {
    if (count($data) < 11)  {
        return json_encode(array('response' => 'Error! Bad input!'));
    }
    $fname = $data[0]->value;
    $lname = $data[1]->value;
    $email = $data[2]->value;
    $phone = $data[3]->value;
    $dob = $data[4]->value;
    $passport = $data[5]->value;
    $medicare = $data[6]->value;
    $address = $data[7]->value;
    $city = $data[8]->value;
    $postalcode = $data[9]->value;
    $country = $data[10]->value;

    $db = new DBConnection();
    $conn = $db->getConnection();
    $result = insert_Person(
        $conn, 
        $fname, 
        $lname, 
        $phone, 
        $email, 
        $dob, 
        $passport, 
        $medicare, 
        $address, 
        $postalcode, 
        $country, 
        $city
    );
    if ($result['response'] == 'Success') {
        // extract the infection records
        // data here should be good, verified it is full on the frontend.
        $pid = $conn->insert_id;
        $failed  = false;
        for ($x = 11; $x < count($data); $x+=2) {
            try {
                $query = $conn->prepare(
                    "INSERT INTO infectionrecords (personID, infecteddate, typInfection) values (?,?,?)"
                );
                // Must have created "Unknown" in CovidInfections or else this will fail.
                $type = $data[$x+1]->value!==''?$data[$x+1]->value:'Unknown';
                $query->bind_param('iss', $pid, $data[$x]->value, $type);
                if ( $query->execute() == false ) {
                    throw new Exception("Error!");
                }

            } catch( Exception $e ){
                $conn->rollback();
                $failed = true;
            } finally {
                $conn->commit();
            }
            if ($failed == true) {
                return json_encode(array('response' => "Error. Failed to enter a new infection for person" . $pid));
            }
        }
        // Success if you get here
        return json_encode($result);
    }
    return json_encode($result);
}

function createPHWRoute($data) {
    if (count($data) != 12)  {
        return json_encode(array('response' => 'Error! Bad input!'));
    }

    // same problem here which may need resolving later
    // or not who the fuck knows
    // Extract the data from the JSON.
    $fname = $data[0]->value;
    $lname = $data[1]->value;
    $ssn = $data[2]->value;
    $email = $data[3]->value;
    $phone = $data[4]->value;
    $dob = $data[5]->value;
    $passport = $data[6]->value;
    $medicare = $data[7]->value;
    $address = $data[8]->value;
    $city = $data[9]->value;
    $postalcode = $data[10]->value;
    $country = $data[11]->value;
    // data handling?! ie. actually setting empties to null etc.
    $db = new DBConnection();
    $conn = $db->getConnection();

    $result = $conn->query("SELECT * from person where medicarenumber='$medicare'");
    if (!empty($result) && $result->num_rows > 0) {
        $db_out = array();
        while ($row = $result->fetch_assoc()) {
            array_push($db_out, $row);
        }
        // use this person and insert them into the health worker. 
        // Warn the user if any information changed to update the person
        try {
            $query = $conn->prepare(
                'INSERT into healthcareworker (personID, SSN) values (?,?)'
            );
            $query->bind_param('ss', $db_out[0]['personID'], $ssn);
            if ($query->execute() === false) {
                $conn->rollback();
                return json_encode(array('response' => 'Error. Person exists or SSN already exists'));
            }
            return json_encode(array(
                'response' => 'Warning', 
                'message' => "New worker created but found existing person. Please update the person if information has changed."));
        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
            return json_encode(array('response' => 'Error. Failed to insert person into hwc'));
        }
        
    } elseif (!empty($result) && $result->num_rows == 0) {
        // insert a new person
        $result = insert_Person(
            $conn, 
            $fname, 
            $lname, 
            $phone, 
            $email, 
            $dob, 
            $passport, 
            $medicare, 
            $address, 
            $postalcode, 
            $country, 
            $city
        );

        if ($result['response'] == 'Success') {
            // get back the last insert
            $pid = $conn->insert_id;

            try {
                $query = $conn->prepare(
                    'INSERT into healthcareworker (personID, SSN) values (?,?)'
                );
                $query->bind_param('is', $pid, $ssn);
                if ($query->execute() === false) {
                    return json_encode(array('response' => 'Error. Failed to insert, hwc exists'));
                }
            } catch (mysqli_sql_exception $e) {
                $conn->rollback();
                return json_encode(array('response' => 'Error. Failed to insert into hwc'));
            }

            return json_encode(array('response' => 'Success'));
        }
        return json_encode($result);
    }

    return json_encode(array('response' => 'Error. Faield to insert'));
     
}

function createPHFRoute($data) {
    if (count($data) != 7)  {
        return json_encode(array('response' => 'Error! Bad input!'));
    }

    $loc = $data[0]->value;
    $phone = $data[1]->value;
    $address = $data[2]->value;
    $postal = $data[3]->value;
    $city = $data[4]->value;
    $typeFacility = $data[5]->value;
    $website = $data[6]->value;

    // get the province from the postal code
    /**
     * TODO: not force the connection between postalcodes and provinces until
     * the user is making a new postalcode or adding an existing one. But like Ale said, 
     * this could be tricky since we should really have the postal codes existing from the beginning.
     */
    $db = new DBConnection();
    $conn = $db->getConnection();

    try {
        $query = $conn->prepare('INSERT into phoneaddress (phone, address) values (?,?)');
        $query->bind_param('ss', $phone, $address);
        $rq = $query->execute();
        if ($rq === false) {
            $sql_query = "SELECT address from phoneaddress where phone='$phone'";
            $result = $conn->query($sql_query);
            if (!empty($result) && $result->num_rows > 0) {
                $db_out = array();
                while ($row = $result->fetch_assoc()) {
                    array_push($db_out, $row);
                }
                if ($db_out[0]['address'] !== $address) {
                    $conn->rollback();
                    return json_encode(array('response' => 'Error. Address does not match.'));
                }
            } elseif (empty($result)) {
                $conn->rollback();
                return json_encode(array("response" => 'Fatal: Error'));
            }
        }
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        return json_encode(array('response' => 'Error. Failed to insert phoneaddress'));
    }

    try {
        $query = $conn->prepare(
            'INSERT into 
            vaccinationfacility (locName, phone, website, typeOfFacility, city, postalCode) 
            values (?,?,?,?,?,?,?)');
        $query->bind_param('sssssss', $loc, $phone, $website, $typeFacility, $city, $postal);
        $query->execute();

        return json_encode(array('response' => 'Success'));
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        return json_encode(array('response' => 'Error. Failed to insert'));
    } catch (Exception $e) {
        $conn->rollback();
        return json_encode(array('response' => 'Error. Unknown Error'));
    }

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
            $rel_query = $conn->prepare('INSERT into vaccinestatushistory (vaccineType, dateOfStatusChange, vaxStatus) values (?,?,?)');
            $rel_query->bind_param('sss', $name, $statuses[$i]['change'], $statuses[$i]['status']);
            $rel_query->execute();
        }

        return json_encode(array('response' => 'Success'));
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        return json_encode(array('response' => 'Error. Failed to insert'));
    } catch (Exception $e) {
        $conn->rollback();
        return json_encode(array('response' => 'Error. Unknown Error'));
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
        INSERT into group_age values ($group, $bottom , $top)
    SQL;    
    return json_encode(perform_insertion($sql_query));
}

function createProvince($data) {
    if (count($data) != 1) {
        return json_encode(array('response' => 'Error! Bad input!'));
    }
    $insertable = $data[0]->value;

    $sql_query = <<<SQL
        INSERT into province (province) values ("$insertable")
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