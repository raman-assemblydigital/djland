<?php
/**
 * User: Evan
 * Date: 5/25/2015
 */
session_start();
require_once("../../headers/security_header.php");

$request = $_SERVER['REQUEST_METHOD'];
if( permission_level() >= $djland_permission_levels['staff'] ) {
    switch($request){
        case "GET":
            //Get Permissions for a user
            if(isset($_GET['member_id'])){
                
                //Create Query
                $query = "SELECT u.username AS username";
                foreach($djland_permission_levels as $level=>$value){
                    if($level != 'operator') $query.=", gm.{$level} AS {$level}";
                }
                $query.=" FROM group_members AS gm INNER JOIN user AS u ON u.userid = gm.userid INNER JOIN membership AS m ON u.member_id = m.id WHERE m.id=:member_id";
                
                //Prepare statement
                $statement = $pdo_db->prepare($query);
                
                //Bind variables to values in query
                $statement->bindValue(':member_id', $_GET['member_id']);
                
                //Try to execute statement
                try {
                    $statement->execute();
                    http_response_code(200);
                    $result = $statement ->fetchAll(PDO::FETCH_ASSOC);
                    
                    //Create return object with data stored in 'permissions' index
                    $permissions = new stdClass();
                    $permissions ->permissions = $result[0];
                    echo json_encode($permissions);
                    
                } catch (PDOException $e) {
                    http_response_code(500);
                    echo json_encode($e->getMessage());
                }
            }else{
                http_response_code(400);
                echo json_encode("Missing member id");
            }
            break;
        case "POST":
            //Update Existing permissions
            if(isset($_POST['member_id']) && isset($_POST['permissions'])){
                
                //Create associative array from membership year object passed  
                $permissions = json_decode($_POST['permissions'],true);

                //Create Query
                $query = "UPDATE group_members SET ";
                $keys = array_keys($permissions);
                $end = end($keys);
                foreach($permissions as $level => $value){
                    
                    $query .= $level." =:".$level;
                    //no comma on last entry
                    if($level != $end){
                        $query .= ",";
                    }
                    
                }
                $query.=" WHERE userid = (SELECT u.userid FROM user AS u INNER JOIN membership AS m ON m.id = u.member_id WHERE m.id=:member_id LIMIT 1)";
                
                //Prepare statement
                $statement = $pdo_db->prepare($query);
                //Bind variables to values in query
                $statement->bindValue(':member_id', $_POST['member_id']);
                foreach($permissions as $level=>$value){
                    $statement->bindValue(':'.$level,$permissions[$level]);
                }
                
                //Try to execute statement
                try {
                    $statement->execute();
                    http_response_code(200);
                    echo json_encode(true);
                    
                } catch (PDOException $e) {
                    http_response_code(500);
                    echo json_encode($e->getMessage());
                }
            }
            else{
                http_response_code(400);
                echo json_encode("Missing user id or membership year");
            }
            break;
        case "PUT":
            //PHP doesn't handle PUT or DELETE.
            parse_str(file_get_contents('php://input'), $_PUT);

            //Insert Permissions
            if(isset($_PUT['member_id']) && isset($_PUT['levels'])){

                //Create associative array from membership year object passed  
                $permissions = json_decode($_PUT['member'],true);

                //Create Query
                $query = "INSERT INTO group_members SET userid=(SELECT u.userid AS userid FROM user AS u INNER JOIN membership as m ON m.id = u.member_id WHERE m.id=:member_id)";
                foreach($djland_permission_levels as $level=>$value){
                    if($level != 'operator') $query.=", {$level}=:{$level}";
                }

                //Prepare statement
                $statement = $pdo_db->prepare($query);

                //Bind variables to values in query
                $statement->bindValue(':member_id', $_PUT['member_id']);
                foreach($djland_permission_levels as $level=>$value){
                    if($level != 'operator') $statement->bindValue(':{$level}',$permissions[$level]);
                }
                
                //Try to execute statement
                try {
                    $statement->execute();
                    http_response_code(201);
                    echo json_encode(true);
                    
                } catch (PDOException $e) {
                    http_response_code(500);
                    echo json_encode($e->getMessage());
                }
            }else{
                http_response_code(400);
                echo json_encode("Missing user id");
            }
            break;
        case "DELETE":
            http_response_code(405);
            echo json_encode("Not Allowed");
            break;
        default:
            http_response_code(400);
            echo json_encode($e->getMessage());
            break;
    }
}else{
    http_response_code(401);
    echo json_encode("You do not have permission");
}
?>