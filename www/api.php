<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Eole\RestApi\Application(array(
    'project.root' => dirname(__DIR__),
    'env' => 'prod',
    'debug' => false,
));

$app->run();
