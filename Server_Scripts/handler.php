<?php
/**
 * Created by PhpStorm.
 * User: Jonathan
 * Date: 4/1/2019
 * Time: 5:18 PM
 */
date_default_timezone_set('US/Eastern');
$rootURL = "https://localhost/rapidsupport/";
$localMysql = "";
require("dbauth.php");
try{
    $opt = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $localMysql = new PDO("mysql:host=$mysql_host;
    dbname=$mysql_dbname;
    port=$mysql_port;",
        $mysql_dbuser,
        $mysql_dbpass,
        $opt);

}catch(PDOException $e){
    echo json_encode(array('error' => $e ->getMessage()));
    //logToDb("PDO Error: ".$e->getMessage(), 3);
}

/*
 * GET action management
 * */
if(isset($_GET['action']) && $_GET['action'] == 'newTicket'){
    createTicket();
}

if(isset($_GET['action']) && $_GET['action'] == 'checkActive'){
    checkIfActive();
}

if(isset($_GET['action']) && $_GET['action'] == 'closeTicket'){
    closeTicket();
}

if(isset($_GET['action']) && $_GET['action'] == 'getId'){
    getRecordById();
}

/**
 * @param $sql
 * string of sql to be executed
 * @param $parameters
 * parameter array to be applied to PDO object executing query
 * @return array|bool
 * returns boolean if query didn't run
 */
function runQuery($sql, $parameters){
    GLOBAL $localMysql;
    if(is_a($localMysql, 'PDO') && method_exists($localMysql, 'prepare')){
        $stmt = $localMysql->prepare($sql);
        if(method_exists($stmt, 'execute') && method_exists($stmt, 'fetchAll')){
            if($parameters == null){
                $stmt->execute();
                $results = $stmt->fetchAll();
            }else{
                $statement = $localMysql->prepare($sql);
                $results = $statement->execute($parameters);
            }
            return $results;
        }else{
            //error_message("Executed with no Results");
            //logToDb("Query $sql Executed with no results", 5);
            return false;
        }
    }else{
        //error_message("no PDO object created");
        //logToDb("no PDO Object Exists for Query: $sql", 3);
        return false;
    }
}

/**
 * @return array
 * { 'client_ip' :  'X'}
 * Where X is the client IP address  of the client unless in the case of
 * the IP being the  server where LOCALHOST is returned
 */
function getIp(){
    if (!empty($_SERVER['HTTP_CLIENT_IP'])){   //check ip from share internet
        $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){   //to check ip is pass from proxy
        $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else {
        $ip=$_SERVER['REMOTE_ADDR'];
    }
    if($ip == "::1"){ //new name for ipv6 loopback
        $ip = "LOCALHOST";
    }
    return array(
        'client_ip' => $ip
    );
}

/**
 * @param $fname - Patron First Name
 * @param $lname - Patron Last Namne
 * -- $username - Patron User Name
 * @param $room - Patron Room#
 * @param $issue - Patron Issue
 * @param $description - Patron Description of Problem
 * @param $shaid - Patron SHA-ID for link
 * @return string Returns the string formatted for Google Hangouts to be passed to
 * postMessage()
 */
//function formatMessage($fname, $lname, $username, $room, $issue, $shaid){
function formatMessage($fname, $lname, $room, $issue, $description, $token){
    //"Regular *BOLD*\nShe says it's important! Something is OOO!\n  <https://tdi.library.appstate.edu|the best site ever>"}
    $submitString = "*Client :* $fname $lname\n*Room :* $room\n*Problem-Type :* $issue\n*Problem-Description :* $description\n<http://localhost/rapidsupport/?ticket_key=$token|View/Reply>";
    return $submitString;
}

function postMessage($message){
    return postMessageDetail($message, null);
}

function postMessageToThread($message, $thread){
    return postMessageDetail($message, $thread);
}



/**
 * @param $messageText - The text to be sent by the Bot
 * @param $thread - The thread ID as established by the original GChat callback
 * @return - Reply from Google API - or error if an error has occurred.
 * Dictates what processed and generated text gets sent to the
 * Google Hangouts instance
 */
function postMessageDetail($messageText, $thread){
    //$phpTestMessage = "Subsequent Message ID Acquisition";
    /*
     *   Formatting doc: https://developers.google.com/hangouts/chat/reference/message-formats/basic
     *   Formatting example:
     *   'text' : "Hey <users/113916484004579202414>, Katherine is looking for *YOU*\nShe says it's important! Something is OOO!\n  <https://tdi.library.appstate.edu|the best site ever>"}
     *
     * */
    //TACO TRUCK CHAT
    //$url = 'https://chat.googleapis.com/v1/spaces/AAAAczrkOQs/messages?key=AIzaSyDdI0hCZtE6vySjMm-WEfRq3CPzqKqqsHI&token=F0SympQEg-g22McdRm6hoRw65gjRrnEmSQZqV-EMPyc%3D';
    //TEST ROOM CHAT

    //spaces/SPACE_ID/threads/THREAD_ID"
    //testing thread id: nxNlkU4is3o
    $url = 'https://chat.googleapis.com/v1/spaces/AAAAdLhr9ag/messages?key=AIzaSyDdI0hCZtE6vySjMm-WEfRq3CPzqKqqsHI&token=pGZR2K9Huug9fOAFRqxLyOeg4joo-fXsCM3wF7dfDO8%3D';
    //$url = 'https://chat.googleapis.com/v1/spaces/AAAAdLhr9ag/messages?key=AIzaSyDdI0hCZtE6vySjMm-WEfRq3CPzqKqqsHI&token=pGZR2K9Huug9fOAFRqxLyOeg4joo-fXsCM3wF7dfDO8%3D';
    $curl = curl_init();
    //$body = json_encode(array('text' => $phpTestMessage));
    if($thread == null){
        $body = json_encode(array(
            'text' => $messageText//,
            /*
             * This code here allows for the implementation of threads.
             * */
            //'thread' => array('name' => 'spaces/AAAAdLhr9ag/threads/Gc7lTW1kbD8')
        ));
    }else{
        $body = json_encode(array(
            'text' => $messageText,
            'thread' => array('name' => $thread)
        ));
    }
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json; charset=UTF-8"
        ),
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if($err){
        //    logToDb("Unable to make API Call. Reason".$err, 4);
        // error($err);
        return array('error' => $err);
    }else{
        //Response is in a string format:
        //return json_encode(json_decode($response), true);
        return json_decode($response);
    }
    //if(isset($response['error'])){
    //    logToDb("API Error: ".$response['error'], 4);
    //}
    //return json_decode($response, true);

    //$json = json_encode(json_decode($response), true);
    /*
     RETURN OBJECT EXAMPLE
{
    name: "spaces/AAAAczrkOQs/messages/d8RuZK9dLqw.d8RuZK9dLqw",
    sender: {
        name: "users/114022495153014004089",
        displayName: "Jonathan_Test",
        avatarUrl: "",
        email: "",
        type: "BOT"
    },
    text: "This means PHP optimization is finished SUCCESSFULLY!",
    cards: [ ],
    previewText: "",
    annotations: [ ],
    thread: {
        name: "spaces/AAAAczrkOQs/threads/d8RuZK9dLqw"
    },
    space: {
        name: "spaces/AAAAczrkOQs",
        type: "ROOM",
        displayName: "Taco Truck"
    },
    fallbackText: "",
    argumentText: "This means PHP optimization is finished SUCCESSFULLY!",
    createTime: "2019-03-25T19:42:34.141711Z"
}*/
}

function createTicket(){
    $_POST = json_decode(file_get_contents('php://input'), true);
    $error = false;
    $ip = getIp()['client_ip'];
    $firstName = "";
    $lastName = "";
    //$username = "";
    $roomNumber = "";
    $problemType = "";
    $problemDescription = "";

    if(isset($_POST['firstName'])){
        $firstName = $_POST['firstName'];
    }else{
        $error = true;
    }
    if(isset($_POST['lastName'])){
        $lastName = $_POST['lastName'];
    }else{
        $error = true;
    }
    if(isset($_POST['roomNumber'])){
        $roomNumber = $_POST['roomNumber'];
    }else{
        $error = true;
    }
    if(isset($_POST['problemType'])){
        $problemType = $_POST['problemType'];
    }else{
        $error = true;
    }
    if(isset($_POST['problemDescription'])){
        $problemDescription = $_POST['problemDescription'];
    }else{
        $error = true;
    }

    /*
     * TODO: Remove this test code
     *
     *
    //TODO: Remove this line
    $error = false;
    if(isset($_GET['firstName'])){
        $firstName = $_GET['firstName'];
    }else{
        $error = true;
    }
    if(isset($_GET['lastName'])){
        $lastName = $_GET['lastName'];
    }else{
        $error = true;
    }
    if(isset($_GET['roomNumber'])){
        $roomNumber = $_GET['roomNumber'];
    }else{
        $error = true;
    }
    if(isset($_GET['problemType'])){
        $problemType = $_GET['problemType'];
    }else{
        $error = true;
    }

    if(isset($_GET['problemDescription'])){
        $problemDescription = $_GET['problemDescription'];
    }else{
        $error = true;
    }*/
    //TODO: Remove this line
    //$error = false;
    //END TEST CODE

    // TODO: Restore if conditional after the test code is removed
    if(!$error){
        //$sql = "insert into rapid_support.incidents(ip, fname, lname, room_number, description, incident_type) values ('$ip', '$firstName', '$lastName', '$roomNumber', '$problemDescription', '$problemType');";
        //$sql = "insert into incidents(ip, fname, lname, room_number, description, incident_type) values ('calculated_ip_address', 'firstname', 'lastname', 'room', 'description', 'type');";
        $sql = "select create_request('$ip', '$firstName', '$lastName', '$roomNumber', '$problemDescription', '$problemType') as 'key'";
        $results = runQuery($sql, null);
        $key = $results[0]['key'];
        $sql = "select id from rapid_support.incidents where ticket_key = '$key'";
        $results = runQuery($sql, null);
        $id = $results[0]['id'];
        $reply = postMessage(formatMessage($firstName, $lastName, $roomNumber, $problemType, $problemDescription, $key));
        $reply = json_decode(json_encode($reply), true);
        $thread = $reply['thread']['name'];
        $sql = "select add_thread('$thread', '$key')";
        runQuery($sql, null);
        echo json_encode(array('message' => 'Message Sent Successfully', 'id' => $id));
    }else{
        echo json_encode(array('message' => 'Error: Input Error'));
    }
}

/*
    *  echos an object:
    * {
    *      active_state : true/false
    *      //data is ONLY populated if active_state is true
    *      data: {
    *          k : v
    *      }
    * }
* */
function checkIfActive(){
    $active_state = true;
    $dt = new DateTime();
    $ip = getIp()['client_ip'];
    $sql = "select * from incidents where ip = '$ip' order by id desc limit 1";
    $results = runQuery($sql, false);
    if(sizeof($results) == 0){
        $active_state = false;
    }else{
        if($results[0]['ticket_open'] == 0) {
            $active_state = false;
        }
        $expireTime = new DateTime($results[0]['expire_time']);
        if($expireTime < $dt){
            $active_state = false;
        }
    }
    if($active_state){
        $returnArray = array(
            'active_state' => $active_state,
            'data' => array(
                'room_number' => $results[0]['room_number'],
                'incident_type' => $results[0]['incident_type'],
                'description' => $results[0]['description']
            ));
        echo json_encode($returnArray);
    }else{
        echo json_encode(array('active_state' => false));
    }
}

function closeTicket(){
    $_POST = json_decode(file_get_contents('php://input'), true);
    $error = false;
    $ticketKey = "";
    $reply = "";
    if(isset($_POST['ticket_key'])){
        $ticketKey = $_POST['ticket_key'];
    }else{
        $error = true;
    }

    if(isset($_POST['reply'])){
        $replyOrigin = "*ISSUE RESOLVED :* ".$_POST['reply'];
    }else{
        $error = true;
    }

    $sql = "Select id, ticket_thread from incidents where ticket_key = '$ticketKey'";
    $results = runQuery($sql, null);
    if(sizeof($results) == 0){
        $error = true;
    }
    if(!$error){
        $id = $results[0]['id'];
        $reply = $_POST['reply'];
        $thread = $results[0]['ticket_thread'];
        //$sql = "select(close_ticket($id, '$reply'))";
        $sql = "select(close_ticket($id, '$reply'))";
        runQuery($sql, null);
        postMessageToThread($replyOrigin, $thread);
        echo json_encode(array('message' => 'Resolved Successfully'));
    }
    if($error){
        echo json_encode(array('message' => 'An error has occurred'));
    }
}

function getRecordById(){
    $_POST = json_decode(file_get_contents('php://input'), true);
    $id = $_POST['id'];
    $error = false;

    $sql = "select room_number, incident_type, ticket_open, expire_time, description from rapid_support.incidents where id = $id";
    $results = runQuery($sql, null);
    $dt = new DateTime();
    $expireTime = new DateTime($results[0]['expire_time']);
    if(!$error){
        echo json_encode(array(
            'room_number' => $results[0]['room_number'],
            'incident_type' => $results[0]['incident_type'],
            'description' => $results[0]['description'],
            'ticket_open' => $results[0]['ticket_open'] == 1 ? "OPEN" : "CLOSED",
            'expired' => $expireTime > $dt ? false : true
        ));
    }else{
        echo json_encode(array(
            'message' => 'Post data is needed to get ID'
        ));
    }
}