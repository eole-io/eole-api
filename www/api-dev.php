<?php

require_once __DIR__.'/../vendor/autoload.php';

\Symfony\Component\Debug\Debug::enable();

$app = new Eole\Silex\Application(array(
    'project.root' => dirname(__DIR__),
    'env' => 'dev',
    'debug' => true,
));

$app->run();
