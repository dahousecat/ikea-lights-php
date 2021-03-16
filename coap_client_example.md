# Example coap-client commands

These are what the php script is generating and running.

## Get auth token

Request:

    coap-client -m post -u "Client_identity" -k "rRUskVp6iLWoN7nd" -e '{"9090":"IDENTITY"}' "coaps://192.168.86.44:5684/15011/9063"
    
Response:

    {"9091":"rsALiY4ffIYh5FXr","9029":"1.13.0021"}

## Get list of ids

Request:

    coap-client -m get -u "IDENTITY" -k "1wRD9Xs09WrlWqN3" "coaps://192.168.86.44:5684/15001"

Response:

```
v:1 t:CON c:GET i:d2d3 {} [ ]
decrypt_verify(): found 24 bytes cleartext
decrypt_verify(): found 60 bytes cleartext
[65549,65554,65546,65537,65551,65548,65538,65552]
```

## Turn light off

Request:

    coap-client -m put -u "IDENTITY" -k "1wRD9Xs09WrlWqN3" -e '{ "3311": [{ "5850": 0 }] }' "coaps://192.168.86.44:5684/15001/65546"

## Get bulb status
    
Request:
    
    coap-client -m get -u "IDENTITY" -k "1wRD9Xs09WrlWqN3" "coaps://192.168.86.44:5684/15001/65546"
   
Response: 

```
v:1 t:CON c:GET i:c5fb {} [ ]
decrypt_verify(): found 24 bytes cleartext
decrypt_verify(): found 323 bytes cleartext
{"9001":"Ceiling light","9003":65546,"9002":1600711566,"3311":[{"5850":1,"5849":2,"5851":79,"5717":0,"5711":454,"5709":32886,"5710":27217,"5706":"efd275","9003":0}],"9020":1615878625,"9019":1,"9054":0,"3":{"0":"IKEA of Sweden","1":"TRADFRI bulb E27 WS opal 1000lm","2":"","3":"2.0.029","6":1,"7":16900},"5750":2}
```
