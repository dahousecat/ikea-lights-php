## Setup

Coming soon

## Instructions

Coming soon

## Example coap-client commands

### Get auth token

Request:

    coap-client -m post -u "Client_identity" -k "rRUskVp6iLWoN7nd" -e '{"9090":"IDENTITY"}' "coaps://192.168.86.44:5684/15011/9063"
    
Response:

    {"9091":"rsALiY4ffIYh5FXr","9029":"1.13.0021"}

### Get list of ids

Request:

    coap-client -m get -u "IDENTITY" -k "1wRD9Xs09WrlWqN3" "coaps://192.168.86.44:5684/15001"

Response:

    //

### Turn light off

    coap-client -m put -u "IDENTITY" -k "1wRD9Xs09WrlWqN3" -e '{ "3311": [{ "5850": 0 }] }' "coaps://192.168.86.44:5684/15001/65546"
   

