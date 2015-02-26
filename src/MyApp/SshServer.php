<?php

namespace MyApp;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;
use SensioLabs\AnsiConverter\Theme\SolarizedXTermTheme;


class Session
{
    protected $session;
    protected $shell;
    protected $uniqid;

    function __construct($session, $shell)
    {
        $this->session = $session;
        $this->shell   = $shell;
        $this->uniqid  = uniqid(uniqid());
    }

    /**
     * @return mixed
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param mixed $session
     */
    public function setSession($session)
    {
        $this->session = $session;
    }

    /**
     * @return mixed
     */
    public function getShell()
    {
        return $this->shell;
    }

    /**
     * @param mixed $shell
     */
    public function setShell($shell)
    {
        $this->shell = $shell;
    }

    /**
     * @return string
     */
    public function getUniqid()
    {
        return $this->uniqid;
    }



}

class SshServer implements MessageComponentInterface
{
    const TERM_TYPE = 'xterm';

    protected $clients;
    /** @var Session[] */
    protected $sessions;
    protected $options;
    protected $theme;
    protected $converter;

    public function __construct($options) {
        $this->clients = new \SplObjectStorage;
        $this->options = $options;
        $this->sessions = array();
        $this->converter = new AnsiToHtmlConverter(new SolarizedXTermTheme());
    }

    public function onOpen(ConnectionInterface $conn)
    {
        echo "New connection! ({$conn->resourceId})\n";
        $this->clients->attach($conn);
        // open ssh2 session
        $ssh2Session = ssh2_connect('localhost');
        ssh2_auth_password($ssh2Session, $this->options['user'], $this->options['password']);
        $shell = ssh2_shell($ssh2Session, self::TERM_TYPE);
        $session = new Session($ssh2Session, $shell);
        $this->sessions[$conn->resourceId] = $session;
        // this is a mark to enable sendCommand to distinguish the end of the output
        $this->sendCommand($session, sprintf('export PS1="%s"',$session->getUniqid()));
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        echo sprintf("received %s from connection %d \n", $msg, $from->resourceId);
        $session = $this->sessions[$from->resourceId];
        $result = $this->sendCommand($session, $msg);
        $from->send($this->converter->convert($result));
    }

    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);
        $session = $this->sessions[$conn->resourceId];
        fclose($session->getShell());
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    protected function sendCommand(Session $session, $command)
    {
        $uid = $session->getUniqid();
        $sshStream = $session->getShell();
        fwrite( $sshStream, $command.PHP_EOL );
        sleep(1);
        stream_set_blocking($sshStream, false);
        $result = '';
        while ($buf = fgets($sshStream,4096)) {
            flush();
            if(strpos($buf, $uid) === 0){
                break;
            }
            $result .= $buf;
        }
        return rtrim($result, $uid);
    }

}
