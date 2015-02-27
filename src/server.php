<?php

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use MyApp\SshServer;

require dirname(__DIR__) . '/vendor/autoload.php';

$parameters = parse_ini_file(__DIR__.'/config/parameters.ini', true);
$config = $parameters['config'];
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new SshServer($config)
        )
    ),
    $config['port']
);

$server->run();

