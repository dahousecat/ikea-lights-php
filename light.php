<?php

require __DIR__ . '/vendor/autoload.php';

use IkeaLightsPhp\ArgumentParser;
use IkeaLightsPhp\Formatter;
use IkeaLightsPhp\LightController;

try {
    $parser     = new ArgumentParser();
    $arguments  = $parser->parse($argv);
    $controller = new LightController();
} catch (Exception $e) {
    die('Error: ' . $e->getMessage() . PHP_EOL);
}

if (isset($arguments['list'])) {
    $data   = $controller->listBulbs();
    $header = array_map('strtoupper', array_keys($data[0]));
    $data   = array_map('array_values', $data);
    $table  = array_merge([$header], $data);
    echo Formatter::table($table);
    exit;
}

$controller->setBulb($arguments['id']);

if (isset($arguments['status'])) {
    $data = $controller->status();
    $table = [];
    foreach($data as $key => $value) {
        $table[] = [strtoupper($key) . ':', $value];
    }
    echo Formatter::table($table);
    exit;
}

if (isset($arguments['power'])) {
    $controller->power($arguments['power']);
}

if (isset($arguments['brightness'])) {
    $controller->brightness($arguments['brightness']);
}

if (isset($arguments['colour'])) {
    $controller->colour($arguments['colour']);
}
