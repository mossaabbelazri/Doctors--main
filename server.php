<?php
require 'vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\Socket\Server;

class Chat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo "Message received: {$msg}\n";
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                $client->send($msg);
                echo "Message sent to client ({$client->resourceId}): {$msg}\n";
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

$loop = React\EventLoop\Factory::create();
$socket = new Server('0.0.0.0:8080', $loop);

$server = new IoServer(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    $socket,
    $loop
);

$server->run();
