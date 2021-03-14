<?php

require_once('Traits/IniTrait.php');
require_once('CoapRequest.php');

class LightController
{
    use IniTrait;

    const PORT = '5684';
    const DIMMER_CODE = '3311';
    const ON_OFF_CODE = '5850';
    const BRIGHTNESS_CODE = '5851';
    const CONTROL_SINGLE_BULB = '15001';
    const CONTROL_GROUP = '15004';

    protected $conf_file = 'conf.ini';

    private array $config;

    private int $bulb_id;

    /**
     * LightController constructor.
     * @throws Exception
     */
    public function __construct() {
        $this->config = $this->loadIni($this->conf_file);
        $this->validateConfig();

        if(empty($this->config['user']) || empty($this->config['auth_token'])) {
            $this->getAuthToken();
        }
    }

    /**
     * @throws Exception
     */
    protected function getAuthToken() {

        $new_user = uniqid();

        $body = '{"9090":"' . $new_user . '"}';

        $uri  = $this->config['ip'] . ':' . self::PORT . '/15011/9063';

        $request = new CoapRequest([
            'method' => 'post',
            'user' => 'Client_identity',
            'key' => $this->config['security_id'],
            'body' => $body,
            'uri' => $uri,
        ]);

        $response = $request->send();
        $response = array_pop($response);
        $data = (array) json_decode($response);

        $auth_token = trim($data['9091']);

        if(strlen($auth_token) !== 16) {
            throw new Exception('Malformed auth token returned: ' . $this->config['auth_token']);
        }

        // Update local and stored conf values.
        $this->config['user'] = $new_user;
        $this->config['auth_token'] = $auth_token;

        $this->writeIni($this->conf_file, [
            'user' => $new_user,
            'auth_token' => $auth_token,
        ]);

    }

    /**
     * @throws Exception
     */
    private function validateConfig() {

        // Check IP
        if(empty($this->config['ip'])) {
            throw new Exception('No IP address in configuration.');
        }

        $ip = $this->config['ip'];
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new Exception($ip . ' is not a valid ip address.');
        }

        // Check auth
        $user = $this->config['user'] ?? null;
        $auth_token = $this->config['auth_token'] ?? null;
        $user_and_auth = $user && $auth_token;
        $security_id = $this->config['security_id'] ?? null;

        if(empty($security_id) && !$user_and_auth) {
            throw new Exception('Config must contain either security id or user and auth token.');
        }

        if(!is_writable('conf.ini') && !$user_and_auth) {
            throw new Exception('Config file is not writable so can\'t add user and auth token.');
        }

    }

    public function setBulb(int $bulb_id) {
        $this->bulb_id = $bulb_id;
    }

    public function power(bool $value) {
        $value = $value ? 1 : 0;
        $payload = '{ "' . self::ON_OFF_CODE . '": ' . $value . ' }';
        $this->bulbRequest($payload);
    }

    public function brightness(int $value) {
        $brightness = round(254 * ($value / 100));
        $payload = '{ "' . self::BRIGHTNESS_CODE . '": ' . $brightness . ' }';
        $this->bulbRequest($payload);
    }

    public function colour(string $value) {

        if($value == 'warm') {
            $num1 = '33135';
            $num2 = '27211';
        } elseif($value == 'normal') {
            $num1 = '30140';
            $num2 = '26909';
        } elseif($value == 'cold') {
            $num1 = '24930';
            $num2 = '24684';
        } else {
            trigger_error('Unknown colour ' . $value, E_USER_WARNING);
            return;
        }

        $payload = '{ "5709": ' . $num1 . ', "5710": ' . $num2 . ' }';

        $this->bulbRequest($payload);

    }

    /**
     * @param string $payload
     */
    protected function bulbRequest(string $payload) {
        $body = '{ "' . self::DIMMER_CODE . '": [' . $payload . '] }';
        $path  = self::CONTROL_SINGLE_BULB . '/' . $this->bulb_id;
        $ip = $this->config['ip'];
        $uri  = "$ip:" . self::PORT . '/' . $path;

        $request = new CoapRequest([
            'method' => 'put',
            'user' => $this->config['user'],
            'key' => $this->config['auth_token'],
            'body' => $body,
            'uri' => $uri,
        ]);

        $request->send();
    }

    /**
     * @param string $body
     * @param string $path
     * @param string $user
     * @param string $key
     * @return array|null
     */
//    private function request(string $body, string $path, string $user, string $key): ?array {
//
//        $ip = $this->config['ip'];
//
//        $uri  = "coaps://$ip:" . self::PORT . '/' . $path;
//
//        $options = $this->formatOptions([
//            'm' => 'put',
//            'u' => $user,
//            'k' => $key,
//            'e' => $body,
//        ]);
//
//        $command = "coap-client $options '$uri'";
//
//        echo $command . PHP_EOL;
//
//        exec($command, $output);
//
//        return $output;
//    }

    /**
     * @param array $options
     * @return string
     */
//    private function formatOptions(array $options): string {
//        $formatted_options = '';
//        foreach ($options as $option => $value) {
//            $formatted_options .= "-$option '$value' ";
//        }
//        return $formatted_options;
//    }

}
