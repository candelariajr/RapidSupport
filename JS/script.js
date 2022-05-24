(function(){
    $('.option-button').click(function(){
        $("#problemReply").html($(this).html());
    });
}());

function showHelpForm(){
    // document.getElementById("supportForm").style.diplay = "block";
    $('body').css('overflow-y' ,'hidden');
    $('#supportForm').show(400);
    $('#showHelpButtonContainer').hide(700);

    //document.getElementById("supportForm").setAttribute("style", "display: block;");
    //document.getElementById("showHelpButtonContainer").setAttribute("style", "display:none");
}

function renderModal(title, content){
    document.getElementById("modalTitle").innerHTML = title;
    document.getElementById("modalBody").innerHTML = content;
    $('#modalDialog').modal({
        keyboard: true
    }).modal('show');
}

var replyState = true;
var renderHelpFormReply = function(response){
    var messageString = response['message'];
    //if(response['id'] !== undefined){
    //    messageString += " Id: " + response['id'];
    //}
    renderModal("Message", messageString);
    var postBody = {'id' : response['id]']};
    var getReplyInterval = setInterval(function(){
        if(!replyState){
            getReplyInterval = null;
        }
        //postAjax("Server_Scripts/handler.php?action=getId", postBody, populateCurrentTicket);
    }, 1000);
};

var populateCurrentTicket = function(response){
    var currentTicket = $("#currentTicket");
    currentTicket.html("");
    currentTicket.html(response['ticket_open']);
    if(response['ticket_open'] === "CLOSED"){
        replyState = false;
    }
};

var verifyHelpWarnState = true;
function verifyHelpForm(){
    var inputErrorText = "Input Error";
    var shortDescriptionWarningThreshold = 30;
    var formValid = true;
    var postBody = {
        'firstName' : '',
        'lastName' : '',
        'roomNumber' : '',
        'problemType' : '',
        'problemDescription' : ''
    };

    //verify firstName
    var firstName = document.getElementById("firstName");
    if (formValid && firstName.value.length === 0){
        renderModal(inputErrorText, "First Name is Blank");
        formValid = false;
    }else{
        postBody['firstName'] = firstName.value;
    }

    //verify lastName
    var lastName = document.getElementById("lastName");
    if (formValid && lastName.value.length === 0){
        renderModal(inputErrorText, "Last Name is Blank");
        formValid = false;
    }else{
        postBody['lastName'] = lastName.value;
    }

    //verify roomNumber
    var roomNumber = document.getElementById("roomNumber");
    if (formValid && roomNumber.value.length === 0){
        renderModal(inputErrorText, "Room Number is Blank");
        formValid = false;
    }else{
        postBody['roomNumber'] = roomNumber.value;
    }

    //verify problemType
    var select = document.getElementById("problemType");
    if (formValid && select.selectedIndex === 0){
        renderModal(inputErrorText, "No Problem Category Selected");
        formValid = false;
    }else{
        postBody['problemType'] = select.value;
    }

    //verify problemDescription and submit
    var problemDescription = document.getElementById("problemDescription");
    if (formValid && problemDescription.value.length === 0){
        renderModal(inputErrorText, "No Problem Description Provided");
        formValid = false;
    }
    if (formValid && problemDescription.value.length < shortDescriptionWarningThreshold && verifyHelpWarnState){
        //document.getElementById("sendAnyway").style.display = "block";
        renderModal("Input Warning", "The Problem Description is short. Your request can still be sent. Requests with more details are responded to more effectively.");
        verifyHelpWarnState = false;
    }else if(formValid && !verifyHelpWarnState){
        postBody['problemDescription'] = problemDescription.value;
        $("#supportForm").hide(200);
        postAjax("Server_Scripts/handler.php?action=newTicket", postBody, renderHelpFormReply);
    }
    if(formValid && problemDescription.value.length >= shortDescriptionWarningThreshold){
        postBody['problemDescription'] = problemDescription.value;
        $("#supportForm").hide(200);
        var stuff = "stuff";
        postAjax("Server_Scripts/handler.php?action=newTicket", postBody, renderHelpFormReply);
        //sendHelpForm(postBody);
    }
}

function sendHelpForm(postBody){
    $("#supportForm").hide(200);
    var request = new XMLHttpRequest();
    var url = "Server_Scripts/handler.php?action=newTicket";
    request.open("POST", url, true);
    request.setRequestHeader("Content-Type", "application/json");
    request.onreadystatechange = function () {
        if (request.readyState === 4 && request.status === 200) {
            //var jsonData = JSON.parse(request.response);
            renderModal("Reply", request.response);
            console.log(request.response);
        }
    };
    request.send(JSON.stringify(postBody));

    /** FUCK YOU JQUERY
    console.log(postBody);
    $.ajax({
        url: "Server_Scripts/handler.php?action=checkActive",
        dataType : 'json',
        type: 'post',
        data: postBody,
        done: (function(data){
            renderModal("Done", data);
        })
    });
    **/
}

function postAjax(url, postBody, callback){
    //console.log(url);
    //console.log(postBody);
    //console.log(callback);
    //console.log("----------------");
    var request = new XMLHttpRequest();
    request.open("POST", url, true);
    request.setRequestHeader("Content-Type", "application/json");
    request.onreadystatechange = function () {
        if (request.readyState === 4 && request.status === 200) {
            //var jsonData = JSON.parse(request.response);
            //renderModal("Reply", JSON.parse(request.response)['message']);
            callback(JSON.parse(request.response));
            //console.log(callback);
        }
    };
    request.send(JSON.stringify(postBody));
}

function verifySupportReply(){
    var $_GET = {};
    if(document.location.toString().indexOf('?') !== -1) {
        var query = document.location
            .toString()
            // get the query string
            .replace(/^.*?\?/, '')
            // and remove any existing hash string (thanks, @vrijdenker)
            .replace(/#.*$/, '')
            .split('&');

        for(var i=0, l=query.length; i<l; i++) {
            var aux = decodeURIComponent(query[i]).split('=');
            $_GET[aux[0]] = aux[1];
        }
    }
    var ticketKey = $_GET['ticket_key'];
    var supportErrorText = "Input Error";
    var supportReply = document.getElementById("problemReply");
    var postBody = {
        'ticket_key' : ticketKey,
        'reply' : supportReply.value
    };
    if(supportReply.value.length === 0){
        renderModal(supportErrorText, "The reply is empty");
    }
    else{
        postAjax("Server_Scripts/handler.php?action=closeTicket", postBody, renderSupportReply);
    }
}

var renderSupportReply = function(response){
    renderModal("Reply", response['message']);
};