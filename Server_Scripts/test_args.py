import sys
# from httplib2 import Http
import httplib2
import json
from json import dumps
# print (sys.argv[1])

# Test Function : Might Delete Later
def callArg(string):
	print("PYTHON OUT: " + string)

	
# This is to be attached to some kind of error exception handling/logging/management process 
def error(string):
	print("{ Python-Error : '" + string + " '}")
	

# General Log Function
def log(string):
	file = open("testfile.txt", "a")
	file.write(string + " \n")
	

def callAPI(string):
    url = 'https://chat.googleapis.com/v1/spaces/AAAAczrkOQs/messages?key=AIzaSyDdI0hCZtE6vySjMm-WEfRq3CPzqKqqsHI&token=F0SympQEg-g22McdRm6hoRw65gjRrnEmSQZqV-EMPyc%3D'
    bot_message = {
        'text' : string}

    message_headers = { 'Content-Type': 'application/json; charset=UTF-8'}

    http_obj = httplib2.Http()

    response = http_obj.request(
        uri=url,
        method='POST',
        headers=message_headers,
        body=dumps(bot_message),
    )
    # result = response[1].decode()
	# print(response.encode())
	# json.loads((h.request(url, 'GET')[1]).decode())
	# json.loads((h.request(url, 'GET')[1]).decode())
	# result = json.loads(response.decode())
    result = json.loads((response[1]).decode())
    print(result)
	
	
# Main Startup Script
if len(sys.argv) != 2: 
	error("More Than Two Args")
else:
	log(sys.argv[1])
	# callArg(sys.argv[1])
	callAPI(sys.argv[1])
