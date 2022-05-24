<!--
ENCOUNTER PAGE WITHOUT GET:
     1 - Determine if open or recently closed entity exists:


-->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quick Help App</title>
    <link rel="stylesheet" type="text/css" href="CSS/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="CSS/styles.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <span class="navbar-brand">Quick Help <?php if (isset($_GET['ticket_key'])):?> - Assisting
        <?php endif;?></span>
</nav>
<div class="row" id="currentOpenContainer">
    <div class="col">
        <div id="currentTicket">
            <!--
                This is where the result of startAjaxSequence is displayed
            -->
        </div>
    </div>
</div>
<!--
Rendered when there are no get objects : No tokens, no anything.
As if someone comes onto the site nimbly
-->
<?php if (empty($_POST) && empty($_GET)):?>
    <div class="container">
        <div class="row" id="showHelpButtonContainer">
            <div class="col">
                <br>
                <br>
                <h3>DISCLAIMER:</h3>
                <p>This is NOT a substitute for a support ticket. This is for emergencies and quick help only. Do not use this application for long-term projects or ongoing issues. If you submit an issue here that requires a ticket, you will be asked to put in a ticket instead.</p>
                <br><br>
                <button id="displayHelpForm" type="button" class="btn btn-success" onclick="showHelpForm()">I still need assistance</button>
            </div>
        </div>
        <br>
        <div class="row" id="supportForm">
            <br>
            <div class="col-lg-6">
                <form class="form-container">
                    <fieldset>
                        <div class="form-group">
                            <label for="firstName">First Name</label>
                            <input id="firstName" class="form-control" type="text" placeholder="Enter First Name" required>
                        </div>
                        <div class="form-group">
                            <label for="lastName">Last Name</label>
                            <input id="lastName" class="form-control" type="text" placeholder="Enter Last Name" required>
                        </div>
                        <div class="form-group">
                            <label for="roomNumber">Room Number</label>
                            <input id="roomNumber" class="form-control" type="text" placeholder="Enter Room Number" required>
                        </div>
                        <div class="form-group">
                            <label for="problemType">Select Problem Category</label>
                            <select name="problemType" class="form-control" id="problemType">
                                <option>Select Problem Category</option>
                                <option>Extron Equipment</option>
                                <option>Classroom Computer Equipment</option>
                                <option>Projector</option>
                                <option>Excessive Cheese</option>
                                <option>Audio</option>
                                <option>Video</option>
                                <option>Printer</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="problemDescription">Problem Description</label>
                            <textarea class="form-control" id="problemDescription" rows="5" placeholder="Enter Problem Description" required maxlength="512"></textarea>
                        </div>
                    </fieldset>
                    <button onclick="verifyHelpForm()" id="submitHelpForm" type="button" class="btn btn-primary">Submit Request</button>
                </form>
            </div>
        </div>
        <br>
    </div>
    <?php //if(is_array(checkIfActive())):?>
    <!--

    -->
        <script> //startAjaxSequence() </script>
    <?php //endif;?>
<?php endif;?>
<!--
Rendered if the ticket key is present:
-->
<?php if (isset($_GET['ticket_key'])):?>
    <?php
        require("Server_Scripts/handler.php");
        $ticket = $_GET['ticket_key'];
        $sql = "select * from incidents where ticket_key = '$ticket'";
        $result = runQuery($sql, null)[0];
        /*
            {"id":50,
            "creation_date":"2019-04-16 08:23:42",
            "ip":"LOCALHOST",
            "username":null,
            "fname":"fn",
            "lname":"ln",
            "room_number":"404",
            "description":"DESC",
            "incident_type":"probs",
            "ticket_key":"7d363805e77ba41805897cdfdd57176a42d608b8",
            "ticket_open":1,
            "expire_time":"2019-04-16 09:03:42"}
         * */
    ?>
    <div class="container">
        <br>
        <br>
        <div class="row">
            <div class="col">
                <form class="form-container">
                    <h4>Reported Problem</h4>
                    <hr>
                    <?php //echo json_encode($result)
                        date_default_timezone_set('US/Eastern');
                        $expireTime = new DateTime($result['expire_time']);
                        $now = new DateTime();
                        if($expireTime < $now){
                            $expired = true;
                        }else{
                            $expired = false;
                        }
                        if($result['ticket_open'] == 1){
                            $open = true;
                        }else{
                            $open = false;
                        }
                    ?>
                    <div>
                        <span class="problem-label">Creation Date: </span>
                        <?php echo " ".$result['creation_date'];?>
                    </div>
                    <div>
                        <span class="problem-label">Expire Date: </span><?php echo " ".$result['expire_time']." ";?>
                        <?php
                        if($expired){
                            echo "<span style='color:red;padding-left: 20px;'><b>Expired</b></span>";
                        }else{
                            echo "<span style='color:green;padding-left: 20px;'><b>Not Expired</b></span>";
                        }
                        ?>
                    </div>
                    <div>
                        <span class="problem-label">Name: </span>
                        <?php echo " ".$result['fname']." ".$result['lname']?>
                    </div>
                    <div>
                        <span class="problem-label">Room Number: </span>
                        <?php echo " ".$result['room_number']?>
                    </div>
                    <div>
                        <span class="problem-label">Incident Type: </span>
                        <?php echo " ".$result['incident_type']?>
                    </div>
                    <div>
                        <span class="problem-label">Description: </span>
                        <?php echo " ".$result['description']?>
                    </div>
                    <div>
                        <span class="problem-label">Status: </span>
                        <?php
                        if($open){
                            if($expired){
                                echo "Expired without reply";
                            }else{
                                echo "OPEN";
                            }
                        }else{
                            echo " "."CLOSED";
                        }?>
                    </div>
                    <?php if(!$expired && $open): ?>
                    <hr>
                    <fieldset>
                        <div class="form-group">
                            <label for="problemDescription"><h4>Issue Reply</h4></label>
                            <br>
                                Quick Reply
                            <br><br>
                            <div class="btn btn-success option-button">You'll have to put In a ticket for this issue</div>
                            <div class="btn btn-success option-button">This is an ongoing issue. It'll take some time to fix this.</div>
                            <div class="btn btn-success option-button">This is not a technology issue.</div>
                            <div class="btn btn-success option-button">Someone is on their way.</div>
                            <div class="btn btn-success option-button">You smell bad.</div><br>
                                Custom Reply
                            <br><br>
                            <textarea class="form-control" id="problemReply" rows="5" maxlength="512"></textarea>
                        </div>
                    </fieldset>
                    <button id="submitHelpReply" type="button" class="btn btn-primary" onclick="verifySupportReply()">Submit Reply</button>
                    <?php endif; ?>
                </form>
                <br>
                <br>
            </div>
        </div>
    </div>
<?php endif;?>
<!--
Modal Dialog: Rendered by the renderModal(title, content) function
-->
<div class="modal" id="modalDialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalBody">

            </div>
            <div class="modal-footer">
                <button id= "sendAnyway" type="button" class="btn btn-primary">Send As Is</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="JS/bootstrap.js"></script>
<script src="JS/script.js"></script>
</body>
</html>