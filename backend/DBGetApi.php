<?php
include('DBConnection.php');

/**
 * Every function is acting as a path which will dictating the GET requests
 * Entry point here:
 */

if($_GET['type'] == 'get_data') {
    echo get_data($_GET['entity']);
} elseif ($_GET['type'] == 'get_question')  {
    echo get_question($_GET['question']);
} else {
    echo json_encode(array('status' => '404', 'response' => 'no route found.'));
}

// pseudoRoutes
function returnJSONError() {
    return json_encode(array('response' => 'Error! No match found'));
}

function get_data($entity) {
    switch($entity) {
        case 'person':
            $sql_query = <<<'SQL'
                select * from person
                natural join posttoprovince
                natural join phoneaddress
                natural join citizenof
                order by personID
                SQL;
            return query_data($sql_query, $entity);
        case 'publicHealthWorker':
            $sql_query = <<<'SQL'
                select * from healthcareworker
                natural join person
                natural join posttoprovince
                natural join phoneaddress
                natural join citizenof
                order by personID
                SQL;
            return query_data($sql_query, $entity);
        case 'publicHealthFacility':
            $sql_query = <<<'SQL'
                select * from vaccinationfacility
                natural join posttoprovince
                natural join phoneaddress
                order by locID
                SQL;
            return query_data($sql_query, $entity);
        case 'vaccinationType':    
            $sql_query = <<<'SQL'
                SELECT * FROM vaccine
            SQL;
            return query_data($sql_query, $entity);
        case 'covid19InfectionVariantType':
            $sql_query = <<<'SQL'
                SELECT * FROM covidvariants
            SQL;
            return query_data($sql_query, $entity);
        case 'groupAge':
            $sql_query = <<<'SQL'
                SELECT * FROM group_age
            SQL;
            return query_data($sql_query, $entity);
        case 'province':   
        case 'Province':
            $sql_query = <<<'SQL'
                SELECT * FROM province p
            SQL;
            return query_data($sql_query, $entity);
        case 'CountryName':
            $sql_query = <<<'SQL'
                SELECT countryName FROM country c
            SQL;
            return query_data($sql_query, $entity);
        case 'PostalCode':
            $sql_query = <<<'SQL'
                SELECT postalCode FROM postalcode
            SQL;
            return query_data($sql_query, $entity);
        default: 
            return returnJSONError();
    }
}

function get_question($question) {
    switch($question) {
        case 'Q12':
            $sql_query = <<<'SQL'
                SELECT  p.firstname, p.lastname, p.dateofbirth, p.emailaddress, p.phone, 
                    p.city,vax.dateAdministered, vax.vaxType, infRec.typInfection as 'Infected with' 
            
                FROM Vaccination vax
                    NATURAL JOIN person p
                    NATURAL JOIN phoneaddress phAddr
                    left join infectionrecords infRec 
                        on infRec.personID = p.personID
                    where (dateAdministered-dateofbirth)/10000>=(select bottomRange from group_age where agegroup=3) AND p.personID IN (Select personID
                    From Vaccination
                    group by personID
                having MAX(doseNum)=1)
                SQL;
            return query_data($sql_query, $question);
        case 'Q13':
            $sql_query = <<<'SQL'
                SELECT p.firstname, p.lastname, p.dateofbirth, p.emailaddress, p.phone, 
                p.city,vax.dateAdministered, vax.vaxType, infRec.typInfection as 'Infected with' 
                FROM vaccination vax
                NATURAL JOIN person p
                NATURAL JOIN phoneaddress phAddr
                left join infectionrecords infRec 
                    on infRec.personID = p.personID
                where p.city='montreal' AND p.personid in 
                (
                select personID
                from vaccination
                group by personID
                having count(distinct(vaxType))>=2
                )
            SQL;
            return query_data($sql_query, $question);
        case 'Q14':
            $sql_query = <<<'SQL'
                with personAndAmountInfectedPerVariant AS(
                    select personID, typInfection, count(*) AS numberoftimesinfected
                    from infectionrecords
                    group by personID
                )
                SELECT firstname, lastname, dateofbirth, emailaddress, phone, 
                city, dateAdministered, vaxType, numberoftimesinfected
                FROM vaccination 
                NATURAL JOIN person
                NATURAL JOIN personAndAmountInfectedPerVariant
                NATURAL JOIN phoneaddress
                Where personID IN (
                select personID
                from infectionrecords 
                group by personID 
                having count(DISTINCT(typinfection))>=2)
            
                SQL;
            return query_data($sql_query, $question);
        case 'Q15':
            $sql_query = <<<'SQL'
                select postprov.provinceName, inv.vaxtype, sum(inv.amount) as 'Num of Vax'
                from vaccinationfacility vaxFac
                    join posttoprovince postprov 
                        on postprov.postalCode = vaxFac.postalCode
                    join inventory inv 
                        on inv.locID = vaxFac.locID
                group by postprov.provinceName,inv.vaxtype
                order by postprov.provinceName ASC, 'Num of Vax' Desc
            SQL;
            return query_data($sql_query, $question);
        case 'Q16':
            $sql_query = <<<'SQL'
                select postprov.provinceName, vax.vaxType, count(*) as 'Number Vaccinated'
                from vaccination vax
                    join vaccinationfacility vaxFac 
                        on vax.locID = vaxFac.locID
                    join posttoprovince postprov 
                        on postprov.postalCode = vaxFac.postalCode
                where '2020-01-01' <= vax.dateAdministered and vax.dateAdministered <= '2021-07-22'
                group by postprov.provinceName, vax.vaxType
                SQL;
            return query_data($sql_query, $question);
        case 'Q17':
            $sql_query = <<<'SQL'
                select vaxFac.city, vax.vaxType, count(*) as 'Number Vaccinated'
                from vaccination vax
                    join vaccinationfacility vaxFac 
                        on vax.locID = vaxFac.locID
                    join posttoprovince postprov 
                        on postprov.postalCode = vaxFac.postalCode
                where '2020-01-01' <= vax.dateAdministered and vax.dateAdministered <= '2021-07-22' and postprov.provinceName = 'Quebec'
                group by postprov.provinceName, vax.vaxType
                SQL;
            return query_data($sql_query, $question);
        case 'Q18':
            $sql_query = <<<'SQL'
                with vaxInv as(
                    select vaxFac.locID,vaxtype,sum(amount) as vaxAmnt
                    from vaccinationfacility vaxFac
                    join inventory inv 
                        on inv.locID = vaxFac.locID
                ),
                numEmply as (
                    select vaxFac.locID, count(*) as cnt
                    from vaccinationfacility vaxFac 
                        join employeeworkrecord emplrec
                            on emplrec.locID = vaxFac.locID
                ),
                numShip as(
                    select vaxFac.locID, count(*) as cnt ,sum(amount) as amnt
                    from vaccinationfacility vaxFac 
                        join shipment ship
                            on ship.locID = vaxFac.locID
                ),
                numTrnsfrIn as(
                    select vaxFac.locID, count(*) as cnt ,sum(amount) as amnt
                    from vaccinationfacility vaxFac 
                        join transfer trnsfr
                            on trnsfr.receivinglocID = vaxFac.locID
                ),
                numTrnsfrOut as(
                    select vaxFac.locID, count(*) as cnt ,sum(amount) as amnt
                    from vaccinationfacility vaxFac 
                        join transfer trnsfr
                            on trnsfr.sendinglocID = vaxFac.locID
                ),
                cntVaxed as(
                    select vaxFac.locID, count(*) as cnt
                    from vaccinationfacility vaxFac
                        join vaccination vax 
                            on vaxFac.locID = vax.locID
                )
                select vaxFac.locName,phnAddr.address, vaxFac.typeofFacility,vaxFac.phone,
                    numEmply.cnt as 'Number of Employees',
                    numShip.cnt as 'Number of Shipments',numShip.amnt as 'Amount Recieved',
                    trnOut.cnt as 'Number of Transfers Out', trnOut.amnt as 'Amount Transferred Out',
                    trnIn.cnt as 'Number of Transfers In', trnIn.amnt as 'Amount Transferred In',
                    vaxInv.vaxType as 'Vaccine Type', vaxInv.vaxAmnt as 'Inventory Count',
                    cntVaxed.cnt as 'Number of Vaccinations Administered'

                from vaccinationfacility vaxFac 
                    join phoneaddress phnAddr
                        on phnAddr.phone = vaxFac.phone
                    join employeeworkrecord emplrec
                        on emplrec.locID = vaxFac.locID
                    join vaxInv 
                        on vaxInv.locID = vaxFac.locID
                    join numEmply
                        on numEmply.locID = vaxFac.locID
                    join numShip 
                        on numShip.locID = vaxFac.locID
                    join numTrnsfrIn trnIn
                        on trnIn.locID = vaxFac.locID
                    join numTrnsfrOut trnOut
                        on trnOut.locID = vaxFac.locID
                    join cntVaxed
                        on cntVaxed.locID = vaxFac.locID
                where vaxFac.city = 'Montreal'
                SQL;
            return query_data($sql_query, $question);
        case 'Q19':
            $sql_query = <<<'SQL'
                Select hlthWrkr.EmployeeID,hlthWrkr.SSN, p.firstName, p.lastName, p.dateofbirth, p.medicarenumber,
                p.phone, phnAddr.address, p.city, postprov.provinceName, p.postalCode, citzOf.countryName, p.emailAddress,  
                wrkRcrd.locID, wrkRcrd.startDate, wrkRcrd.endDate
                from healthcareworker hlthWrkr
                    join person p
                        on hlthWrkr.personID = p.personID
                    join employeeworkrecord wrkRcrd
                        on hlthWrkr.employeeID = wrkRcrd.employeeID
                    join vaccinationfacility vaxFac
                        on vaxFac.locID = wrkRcrd.locID
                    join phoneaddress phnAddr
                        on phnAddr.phone = p.phone
                    join posttoprovince postprov
                        on postprov.postalCode = p.postalCode
                    join citizenof citzOf
                        on citzOf.personID = p.personID
                SQL;
            return query_data($sql_query, $question);
        case 'Q20':
            $sql_query = <<<'SQL'
                Select hlthWrkr.EmployeeID, p.firstname, p.lastName, p.dateofbirth, p.phone, p.city, p.emailAddress, wrkRcrd.locID
                from healthcareworker hlthWrkr
                    join employeeworkrecord wrkRcrd
                        on hlthWrkr.employeeID = wrkRcrd.employeeID
                    join person p
                        on p.personID = hlthWrkr.personID
            SQL;
            return query_data($sql_query, $question);
        default: 
            return returnJSONError();
    }
}

// Accessor function for our database
function query_data($sql_query, $entity) {
    $output;
    try {
        $conn = new DBConnection();
        $result = $conn->query($sql_query); 
        if (!empty($result) && $result->num_rows > 0) {
            $to_ret = array();
            while ($row = $result->fetch_assoc()) {
                array_push($to_ret, $row);
            }
            $output = json_encode(array('response' => array('data' => $to_ret, 'name' => $entity)));
        } else {
            $output = json_encode(array('response' => 'None found!'));
        }
    } catch (\Exception $e) {
        $output = json_encode(array('response' => 'Error!'));
    }

    return $output;
}