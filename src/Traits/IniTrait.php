<?php

namespace IkeaLightsPhp\Traits;

trait IniTrait {

    /**
     * @param string $file
     * @return array
     */
    function loadIni(string $file): array {
        if(!file_exists($file)) {
            return [];
        }

        if($config = parse_ini_file($file)) {
            return $config;
        }

        return [];
    }

    /**
     * @param string $file
     * @param array $params
     */
    function writeIni(string $file, array $params) {
        $data = $this->loadIni($file);
        foreach($params as $key => $value) {
            $data[$key] = $value;
        }
        $output = '';
        foreach($data as $key => $value) {
            $output .= $key . ' = ' . $value . PHP_EOL;
        }

        $res = file_put_contents($file, $output, LOCK_EX);

        if($res === false) {
            trigger_error('Could not write ini file ' . $file, E_USER_WARNING);
        }
    }
}
