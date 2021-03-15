<?php

require_once 'src/LightController.php';
require_once 'src/ArgumentParser.php';
require_once 'src/Formatter.php';

try {
    $parser = new ArgumentParser();
    $arguments = $parser->parse($argv);
    $controller = new LightController();
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}

if(isset($arguments['list'])) {
    $bulbs = $controller->listBulbs();
    $table = [];
    foreach($bulbs as $name => $id) {
        $table[] = [$name, $id];
    }
    echo Formatter::table($table);
    exit;
}

$controller->setBulb($arguments['id']);

if(isset($arguments['power'])) {
    $controller->power($arguments['power']);
}

if(isset($arguments['brightness'])) {
    $controller->brightness($arguments['brightness']);
}

if(isset($arguments['colour'])) {
    $controller->colour($arguments['colour']);
}
