#!/usr/bin/python3
# from httplib2 import Http
import httplib2
from json import dumps

#
# Hangouts Chat incoming webhook quickstart
#
def main():
    url = 'https://chat.googleapis.com/v1/spaces/AAAAczrkOQs/messages?key=AIzaSyDdI0hCZtE6vySjMm-WEfRq3CPzqKqqsHI&token=F0SympQEg-g22McdRm6hoRw65gjRrnEmSQZqV-EMPyc%3D'
    bot_message = {
        'text' : 'Minimal Python Invocation Argument Test'}

    message_headers = { 'Content-Type': 'application/json; charset=UTF-8'}

    # http_obj = Http()
    http_obj = httplib2.Http()

    response = http_obj.request(
        uri=url,
        method='POST',
        headers=message_headers,
        body=dumps(bot_message),
    )

    print(response)

if __name__ == '__main__':
    main()
