<?php

require_once __DIR__.'/../vendor/autoload.php';

\Symfony\Component\Debug\Debug::enable();

$app = new Eole\RestApi\Application(array(
    'project.root' => dirname(__DIR__),
    'env' => 'docker',
    'debug' => true,
));

$app->run();
