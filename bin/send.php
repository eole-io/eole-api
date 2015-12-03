<?php

$context = new ZMQContext();
$socket = $context->getSocket(ZMQ::SOCKET_PUSH);
$socket->connect('tcp://127.0.0.1:5555');

$socket->send('yo');
