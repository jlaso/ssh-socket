<?php

namespace MyApp;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface 
{

    protected $clients;
    protected $sessions;
    protected $options;

    public function __construct($options) {
        $this->clients = new \SplObjectStorage;
        $this->options = $options;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);
        $session = $this->sessions[$conn->resourceId] = ssh2_connect('localhost');
        ssh2_auth_password($session, $this->options['user'], $this->options['password']);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        foreach ($this->clients as $client) {
            if ($from == $client) {
                // The sender is not the receiver, send to each client connected
                $client->send($msg);
                $session = $this->sessions[$client->resourceId];
                $result = $this->exec($session, $msg);
                $client->send($result);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    /**
     * @param $command
     * @return string
     * @throws \Exception
     */
    protected function exec($session, $command)
    {
        if(!$session){
            throw new \Exception('SSH session not started');
        }
        if (!($stream = ssh2_exec($session, $command))) {
            throw new \Exception('SSH command failed');
        }
        stream_set_blocking($stream, true);
        $result = "";
        while ($buf = fread($stream, 4096)) {
            $result .= $buf;
        }
        fclose($stream);

        return $result;
    }

}
