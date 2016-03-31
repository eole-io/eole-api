<?php

namespace Eole\WebSocket;

use ZMQ;
use React\ZMQ\Context;
use React\EventLoop\LoopInterface;
use React\EventLoop\Factory;
use React\Socket\Server as ReactSocketServer;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Wamp\ServerProtocol;
use Eole\Silex\Application as SilexApplication;
use Eole\WebSocket\Application as WebSocketApplication;

class Server
{
    /**
     * @var SilexApplication
     */
    private $silexApplication;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @param SilexApplication $silexApplication
     */
    public function __construct(SilexApplication $silexApplication)
    {
        $this->silexApplication = $silexApplication;

        $this->init();
    }

    /**
     * Init websocket server and push server if enabled.
     */
    private function init()
    {
        echo 'Initialization...'.PHP_EOL;

        $this->loop = Factory::create();

        $webSocketHost = $this->silexApplication['environment']['websocket']['server']['host'];
        $webSocketPort = $this->silexApplication['environment']['websocket']['server']['port'];

        $socket = new ReactSocketServer($this->loop);
        $socket->listen($webSocketPort, $webSocketHost);

        new IoServer(
            new HttpServer(
                new WsServer(
                    new ServerProtocol(
                        new WebSocketApplication(
                            $this->silexApplication
                        )
                    )
                )
            ),
            $socket
        );

        if ($this->silexApplication['environment']['push_server']['enabled']) {
            $this->enablePushServer();
        }
    }

    /**
     * Init push server and redispatch events from push server to application stack.
     */
    public function enablePushServer()
    {
        $pushHost = $this->silexApplication['environment']['push_server']['server']['host'];
        $pushPort = $this->silexApplication['environment']['push_server']['server']['port'];

        $context = new Context($this->loop);
        $pushServer = $context->getSocket(ZMQ::SOCKET_PULL);

        $pushServer->bind("tcp://$pushHost:$pushPort");

        $pushServer->on('message', function ($message) {
            $data = $this->silexApplication['eole.event_serializer']->deserializeEvent($message);

            echo 'PushServer message event: '.$data['name'].PHP_EOL;

            $this->silexApplication['dispatcher']->dispatch($data['name'], $data['event']);
        });
    }

    /**
     * Run websocket server.
     */
    public function run()
    {
        $webSocketHost = $this->silexApplication['environment']['websocket']['server']['host'];
        $webSocketPort = $this->silexApplication['environment']['websocket']['server']['port'];

        echo "Bind websocket server to $webSocketHost:$webSocketPort".PHP_EOL;

        if ($this->silexApplication['environment']['push_server']['enabled']) {
            $pushHost = $this->silexApplication['environment']['push_server']['server']['host'];
            $pushPort = $this->silexApplication['environment']['push_server']['server']['port'];

            echo "Bind push server to $pushHost:$pushPort".PHP_EOL;
        }

        $this->loop->run();
    }
}
