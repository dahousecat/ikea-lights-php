<?php

require_once 'src/LightController.php';
require_once 'src/ArgumentParser.php';

try {
    $parser = new ArgumentParser();
    $arguments = $parser->parse($argv);
    $controller = new LightController();
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
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
