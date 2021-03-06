<?php

namespace IkeaLightsPhp;

use Exception;
use IkeaLightsPhp\Traits\IniTrait;

class ArgumentParser
{
    use IniTrait;

    /**
     * @var string Example of how to use command.
     */
    protected string $example = 'Example: php light.php lounge on 50% warm' . PHP_EOL;

    /**
     * @var string[] Valid colours.
     */
    protected array $colours = ['cold', 'normal', 'warm'];

    /**
     * @var string[] Valid power states.
     */
    protected array $power_states = ['on', 'off'];

    /**
     * @var array Array of light names and their ids.
     */
    protected array $bulbs;

    /**
     * @var string
     */
    protected string $bulb_file = 'bulbs.ini';

    /**
     * ArgumentParser constructor.
     */
    public function __construct() {

        // Try and load the bulb configuration file.
        $this->bulbs = $this->loadIni($this->bulb_file);
    }

    /**
     * Parse the command line arguments.
     *
     * @param $argv
     * @return array
     * @throws Exception
     */
    public function parse($argv) {

        if($argv[1] == 'list') {
            return ['list' => true];
        }

        $this->basicValidation($argv);

        $output = [];

        $this->parseNameOrId($argv[1], $output);

        $this->parseCommand($argv[2], $output);
        $this->parseCommand($argv[3] ?? null, $output);
        $this->parseCommand($argv[4] ?? null, $output);

        return $output;
    }

    /**
     * Parse first argument - must be either name or id of bulb.
     *
     * @param string $name_or_id
     * @param array $output
     * @throws Exception
     */
    function parseNameOrId(string $name_or_id, array &$output) {
        if($id = array_search($name_or_id, $this->bulbs)) {
            $output['id'] = intval($id);
        } else {
            $output['id'] = intval($name_or_id);
        }
        $this->validateId($output['id']);
    }

    /**
     * Parse a command. May be power state, brightness, colour or status.
     *
     * @param string|null $command
     * @param array $output
     * @throws Exception
     */
    function parseCommand(?string $command, array &$output) {
        if(is_null($command)) {
            return;
        }
        $command = str_replace('%', '', $command);
        if($command === 'status') {
            $output['status'] = 'true';
        }
        elseif(in_array($command, $this->power_states)) {
            $output['power'] = $command == 'on';
        }
        elseif(in_array($command, $this->colours)) {
            $output['colour'] = $command;
        }
        elseif(is_numeric($command) && $command >= 0 && $command <= 100) {
            $output['brightness'] = (int) $command;
        }
        elseif(substr($command, 0, 6) === 'delay=') {
            $delay = str_replace('delay=', '', $command);
            $output['delay'] = $this->delayToSeconds($delay);
        }
        else {
            throw new Exception($command . ' is not a valid command.');
        }
    }

    /**
     * Ensure bulb name or id at at least one command are present.
     *
     * @param $argv
     * @throws Exception
     */
    protected function basicValidation($argv) {
        if (empty($argv[1])) {
            $error = 'Must supply light name. ' . PHP_EOL . $this->example;
            throw new Exception($error);
        }

        if (empty($argv[2])) {
            $error = 'Must supply at least one command. ' . PHP_EOL . $this->example;
            throw new Exception($error);
        }
    }

    /**
     * Validate bulb id.
     *
     * @param string $id
     * @throws Exception
     */
    protected function validateId(string $id) {
        if(substr($id, 0, 3) !== '655') {
            throw new Exception('All bulb ids must start with 655');
        }

        if(strlen($id) !== 5) {
            throw new Exception('All bulb ids must be 5 digits long.');
        }
    }

    /**
     * @param string $delay
     * @return int
     */
    protected function delayToSeconds(string $delay): int {
        $units_multipiers = [
            's' => 1,
            'm' => 60,
            'h' => 60 * 60,
        ];
        $unit = substr($delay, strlen($delay) - 1);

        if(!array_key_exists($unit, $units_multipiers)) {
            return 0;
        }

        $value = substr($delay, 0, strlen($delay) - 1);

        if(!is_numeric($value)) {
            return 0;
        }

        return intval($value * $units_multipiers[$unit]);
    }

}