<?php

namespace IkeaLightsPhp;

use Exception;
use IkeaLightsPhp\Traits\IniTrait;

class LightController
{
    use IniTrait;

    const PORT = '5684';
    const DIMMER_CODE = '3311';
    const ON_OFF_CODE = '5850';
    const BRIGHTNESS_CODE = '5851';
    const CONTROL_SINGLE_BULB = '15001';
    const CONTROL_GROUP = '15004';

    protected string $conf_file = 'conf.ini';

    protected string $bulb_file = 'bulbs.ini';

    protected array $config;

    protected int $bulb_id;

    /**
     * LightController constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->config = $this->loadIni($this->conf_file);
        $this->validateConfig();

        if (empty($this->config['user']) || empty($this->config['auth_token'])) {
            $this->getAuthToken();
        }
    }

    /**
     * Get an auth token and save it to conf file.
     * @throws Exception
     */
    protected function getAuthToken()
    {

        $new_user = uniqid();

        $body = '{"9090":"' . $new_user . '"}';

        $uri = $this->baseUri() . '15011/9063';

        $request = new CoapRequest([
            'method' => 'post',
            'user'   => 'Client_identity',
            'key'    => $this->config['security_id'],
            'body'   => $body,
            'uri'    => $uri,
        ]);

        $response = $request->send();
        $response = array_pop($response);
        $data     = (array)json_decode($response);

        $auth_token = trim($data['9091']);

        if (strlen($auth_token) !== 16) {
            throw new Exception('Malformed auth token returned: ' . $this->config['auth_token']);
        }

        // Update local and stored conf values.
        $this->config['user']       = $new_user;
        $this->config['auth_token'] = $auth_token;

        $this->writeIni($this->conf_file, [
            'user'       => $new_user,
            'auth_token' => $auth_token,
        ]);

    }

    /**
     * Validate the config from the conf file.
     * @throws Exception
     */
    protected function validateConfig()
    {

        // Check IP
        if (empty($this->config['ip'])) {
            throw new Exception('No IP address in configuration.');
        }

        $ip = $this->config['ip'];
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new Exception($ip . ' is not a valid ip address.');
        }

        // Check auth
        $user          = $this->config['user'] ?? null;
        $auth_token    = $this->config['auth_token'] ?? null;
        $user_and_auth = $user && $auth_token;
        $security_id   = $this->config['security_id'] ?? null;

        if (empty($security_id) && !$user_and_auth) {
            throw new Exception('Config must contain either security id or user and auth token.');
        }

        if (!is_writable('conf.ini') && !$user_and_auth) {
            throw new Exception('Config file is not writable so can\'t add user and auth token.');
        }

    }

    /**
     * Request a list of bulb ids, save to config and return array.
     */
    public function listBulbs()
    {

        $uri = $this->baseUri() . '15001';

        $request = new CoapRequest([
            'method' => 'get',
            'user'   => $this->config['user'],
            'key'    => $this->config['auth_token'],
            'uri'    => $uri,
        ]);

        $response = $request->send();

        $response     = array_pop($response);
        $new_bulb_ids = (array)json_decode($response);

        $data = [];
        foreach($new_bulb_ids as $id) {
            if($bulb_status = $this->status($id)) {
                $data[] = $bulb_status;
            }
            $seconds = 0.5;
            time_nanosleep(0, $seconds * 1000000);
        }

        $this->updateBulbsFile($data);

        return $data;
    }

    public function status(int $id = null)
    {
        $uri = $this->baseUri() . '15001/' . ($id ?? $this->bulb_id);

        $request = new CoapRequest([
            'method' => 'get',
            'user'   => $this->config['user'],
            'key'    => $this->config['auth_token'],
            'uri'    => $uri,
        ]);

        $response = $request->send();

        $response = array_pop($response);
        $data     = (array)json_decode($response);

        // Not a light bulb.
        if(!isset($data[3311])) {
            return [];
        }

        $state    = (array)$data[3311][0];
        $info     = (array)$data[3];

        $response = [
            'id'         => $data[9003],
            'name'       => $data[9001],
            'power'      => $state[5850] === 1 ? 'On' : 'Off',
            'brightness' => $this->brightnessToPercent($state[5851]),
            'type'       => $info[1],
        ];

        return $response;

    }

    /**
     * Update or create the bulbs ini file.
     *
     * @param array $data
     */
    protected function updateBulbsFile(array $data)
    {
        $names_and_ids = [];
        foreach($data as $bulb) {
            $names_and_ids[$bulb['id']] = $bulb['name'];
        }
        $this->writeIni($this->bulb_file, $names_and_ids);
    }

    /**
     * Set the bulb id to use for any operations.
     *
     * @param int $bulb_id
     * @return $this
     */
    public function setBulb(int $bulb_id)
    {
        $this->bulb_id = $bulb_id;
        return $this;
    }

    /**
     * Turn the bulb on or off.
     * @param bool $value
     * @return $this
     */
    public function power(bool $value)
    {
        $value   = $value ? 1 : 0;
        $payload = '{ "' . self::ON_OFF_CODE . '": ' . $value . ' }';
        $this->bulbRequest($payload);
        return $this;
    }

    /**
     * Set the brightness.
     *
     * @param int $percent
     * @return $this
     */
    public function brightness(int $percent)
    {
        $brightness = $this->percentToBrightness($percent);
        $payload    = '{ "' . self::BRIGHTNESS_CODE . '": ' . $brightness . ' }';
        $this->bulbRequest($payload);
        return $this;
    }

    /**
     * @param int $brightness
     * @return int
     */
    protected function brightnessToPercent(int $brightness) {
        return intval(round(($brightness / 254) * 100));
    }

    /**
     * @param int $percent
     * @return int
     */
    protected function percentToBrightness(int $percent) {
        return intval(round(254 * ($percent / 100)));
    }

    /**
     * Set the colour.
     *
     * @param string $value
     * @return $this
     */
    public function colour(string $value)
    {

        if ($value == 'warm') {
            $num1 = '33135';
            $num2 = '27211';
        } elseif ($value == 'normal') {
            $num1 = '30140';
            $num2 = '26909';
        } elseif ($value == 'cold') {
            $num1 = '24930';
            $num2 = '24684';
        } else {
            trigger_error('Unknown colour ' . $value, E_USER_WARNING);
            return $this;
        }

        $payload = '{ "5709": ' . $num1 . ', "5710": ' . $num2 . ' }';

        $this->bulbRequest($payload);

        return $this;
    }

    /**
     * Format a request to perform an operation on a bulb.
     *
     * @param string $payload
     */
    protected function bulbRequest(string $payload)
    {
        $body = '{ "' . self::DIMMER_CODE . '": [' . $payload . '] }';
        $path = self::CONTROL_SINGLE_BULB . '/' . $this->bulb_id;
        $uri  = $this->baseUri() . $path;

        $request = new CoapRequest([
            'method' => 'put',
            'user'   => $this->config['user'],
            'key'    => $this->config['auth_token'],
            'body'   => $body,
            'uri'    => $uri,
        ]);

        $request->send();
    }

    /**
     * Return formatted ip and port.
     *
     * @return string
     */
    protected function baseUri()
    {
        return $this->config['ip'] . ':' . self::PORT . '/';
    }

}
