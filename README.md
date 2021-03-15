## Setup

Copy `conf.ini.example` to `conf.ini`.

Add your security_id and hubs IP address. 

Run:

    php light.php list
    
This will return a list of all your bulbs and save them to `bulbs.ini`

You can edit this file to replace auto generated names with your names.

E.g.

    kitchen = 65537
    lounge = 65538

## Instructions

Example:

    php light.php lounge on 50% warm

First argument is bulb name from `bulb.ini`. Required.

Second argument is power state. Can be on or off. Optional.

Third argument is brightness level. A percentage from 0 to 100. Using a percent sign is optional. Optional.

Fourth argument is the colour. Can be cold, normal or warm. Optional.

The order of 2nd, 3rd and 4th arguments does not matter.

You can also exclude any you don't need.

For example the following are all valid:

    php light.php lounge off
    php light.php lounge 20%
    php light.php lounge warm
    
## Notes

If you already have a user and auth_token you can add that instead of a security id to the conf.ini like this:

```
user = YOUR_USER
auth_token = YOUR_AUTH_TOKEN
```

## Example coap-client commands

These are what the php script is generating and running.

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
   

