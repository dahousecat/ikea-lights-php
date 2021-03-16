## Setup

First install the coap-client:

```
sudo apt-get install build-essential autoconf automake libtool
git clone --recursive https://github.com/obgm/libcoap.git
cd libcoap
git checkout dtls
git submodule update --init --recursive
./autogen.sh
./configure --disable-documentation --disable-shared
make
sudo make install
```

Then clone this project and run `composer install`

Copy `conf.ini.example` to `conf.ini`.

Add your security_id and hubs IP address. 

Run:

    php light.php list

This will return something like:

```
ID      NAME                 POWER   BRIGHTNESS   TYPE
65549   Table lamp           On      40           TRADFRI bulb E14 W op/ch 400lm
65554   Conservatory light   On      50           TRADFRI bulb E27 WS opal 1000lm
65546   Ceiling light        On      31           TRADFRI bulb E27 WS opal 1000lm
65548   Hall light           On      50           TRADFRI bulb E27 WS clear 950lm
65538   Floor lamp           On      10           TRADFRI bulb E27 WS clear 950lm
65552   Kitchen light        Off     50           TRADFRI bulb E27 WS opal 1000lm
```

It will save the names and ids to `bulbs.ini`.

Now you can refer to each bulb by the name or id.

## Instructions

### Controlling a bulb

Here is an example to control a bulb:

    php light.php "Table lamp" on 50% warm

`php light.php` just calls the script.

The first argument is bulb name. 
Can also pass bulb id.

Second argument is power state. 
Can be `on` or `off`. 

Third argument is brightness level. 
A percentage from 0 to 100. 
Using a percent sign is optional.

Fourth argument is the colour. 
Can be `cold`, `normal` or `warm`.

The bulb name or id must be the first argument, but the order of
the other arguments doesn't matter.

For example this is fine: 

    php light.php "Table lamp" warm on 50%

You can also exclude any you don't need.

For example the following are all valid:

    php light.php "Table lamp" off
    php light.php "Table lamp" 20%
    php light.php "Table lamp" warm 50%

### Getting a bulb status

Run:

    php light.php "Table lamp" status

This will return something like:

```
ID:           65549
NAME:         Table lamp
POWER:        On
BRIGHTNESS:   40
TYPE:         TRADFRI bulb E14 W op/ch 400lm
```
    
## Notes

If you already have a user and auth_token you can add that instead of a security id to the conf.ini like this:

```
user = YOUR_USER
auth_token = YOUR_AUTH_TOKEN
```
