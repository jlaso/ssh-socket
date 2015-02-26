<?php

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use MyApp\SshServer;

require dirname(__DIR__) . '/vendor/autoload.php';

$parameters = parse_ini_file(__DIR__.'/config/parameters.ini', true);

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new SshServer($parameters['config'])
        )
    ),
    8080
);

$server->run();

