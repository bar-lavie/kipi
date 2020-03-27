<?php
namespace DataSocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

use app\CRUD;
use app\DB;

require_once('CRUD.php');
require_once('DB.php');

class DataSocket implements MessageComponentInterface
{
    protected $data;
    protected $client;

    public function __construct()
    {
        echo 'connected!';
        // $this->clients = new \SplObjectStorage;
        $this->data = [];
    }

    public function onOpen(ConnectionInterface $conn)
    {
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $this->data = json_decode($msg);
        CRUD::store(serialize($this->data[0]), $this->data[1]);
    }

    public function onClose(ConnectionInterface $conn)
    {
       // print_r($this->data);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}