<?php
/**
 * CONFIG VALUES:
 *  Db username, password
 *  seed
 *
 * POST: If $_POST['message'] exists - do the following:
 *
 * 1- Create a new row in the incident table:
 *      id - auto generated
 *      username $_POST['username'] <- either this name or
 *      fname $_POST['fname'] <- these two names
 *      lname $_POST['lname']
 *      initial_comment $_POST['message']
 *      ticket_key md5(auto generated id, (auto generated id + salt))
 *      open 1
 *      message_id null
 *      ip_address $_POST['ip_address']
 *      session_end (now() + 1 hour)
 *
 * 2- Send link to the GApps Suite with appropriate connection parameters -
 *      Message contains a native link to this site with a GET['ticket_key']
 *
 * 3- Keep Making AJAX posts to the URL GET: $_GET['status']
 *
 * GET: If $_GET['ticket_key'] exists - do the following:
 *      1- Open form:
 *          Form has the following options:
 *          * Tech Support Is Coming
 *          * This is a long term issue that will need to be investigated further
 *          * CUSTOM_RESPONSE
 *          Submission Of Form Does the Following:
 *              1- Adds an entry to the support reply table
 *              id - auto generated
 *              option 1 - (t/f)
 *              option 2 - (t/f)
 *              text_reply - text from form
 *              sets open to 0 for the parent entry
 *              sets session_end (now() + 30 minutes)
 *
 * IF NOTHING EXISTS OR AJAX ENTRY:
 *      1 - see if session exists for this computer:
 *      see if ip address is linked to entry:
 *      NOTE THIS IS DUPLICATE LOGIC FROM BELOW - JUST REWORDING IT
 *          YES
 *              open = 0
 *                  session_end > now()   //session is still open
 *                      Render the site with the message info AND option of creating a new alert
 *                  session_end < now()   //session was closed
 *                      Render the site with just the option of creating a new alert
 *              open = 1
 *                  session_end > now()   //session is still open
 *                      Render the message info
 *                  session_end < now()   //session was closed (open but timed out)
 *                      Display that there is an open request for this machine. (Render Text)
 *          NO
 *          Go to create new message
 *
 *
 *      NOTE THIS IS DUPLICATE LOGIC FROM ABOVE - JUST REMOVING THIS
 * if ip address is linked to an entry of session_end > now()
 *          AND open = 0
 *
 *      if ip address is linked to an entry where open = 1
 *              then render "A request is open for this machine" render outgoing message (but not user info)
 *      if ip address is linked to an entry of session_end < now()
 *              render the option of creating a new ticket
 *
 *
 * CLIENT SIDE:
 *      Keep A bottom bar of a tech support summary of the last incident reported
 *      to this ip address on the screen if applicable
 *
 *
 */
if (isset($_GET["id"])){
    echo "reply";
}



//if(isset($_POST["issue"])){
    // $testText = $_GET['test_text'];

    //$testText = "[Object][object]";
    //$command = escapeshellcmd('python test_args.py "'.$testText.'"');
    //$output = shell_exec($command);
    //$cleanedOutput = cleanOutput($output);
    //echo json_encode($cleanedOutput);
    //postMessage("Test Message");
    //echo json_encode($_POST);
//}



function formatMessage($fname, $lname, $username, $room, $issue, $shaid){
    return "";
}

/**
 * @param $messageText
 * Dictates what processed and generated text gets sent to the
 * Google Hangouts instance
 */
function postMessage($messageText){
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
    $url = 'https://chat.googleapis.com/v1/spaces/AAAAdLhr9ag/messages?key=AIzaSyDdI0hCZtE6vySjMm-WEfRq3CPzqKqqsHI&token=pGZR2K9Huug9fOAFRqxLyOeg4joo-fXsCM3wF7dfDO8%3D';
    $curl = curl_init();
    //$body = json_encode(array('text' => $phpTestMessage));
    $body = json_encode(array('text' => $messageText));
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
        error($err);
    }else{
        echo "";
    }
    //if(isset($response['error'])){
    //    logToDb("API Error: ".$response['error'], 4);
    //}
    //return json_decode($response, true);
    $json = json_encode(json_decode($response), true);
    /*
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
}


     * */
}


/**
 * @param $errorMessage
 * This message is logged to a file table with a timestamp
 */
function error($errorMessage){
    //TODO: Implement this
    echo "Error";
}


/**
 * @return array
 * { 'client_ip' :  'X'}
 * Where X is the client IP address  of the client unless in the case of
 * the IP being the  server where LOCALHOST is returned
 */
function getParams(){
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

?>