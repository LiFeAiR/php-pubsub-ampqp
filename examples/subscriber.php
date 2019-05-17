<?php
require_once __DIR__ . '/../vendor/autoload.php';

use LiFeAiR\PubSub\AMQP\AMQPPubSubAdapter;
use PhpAmqpLib\Connection\AMQPStreamConnection;

$client = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$adapter = new LiFeAiR\PubSub\AMQP\AMQPPubSubAdapter($client);


$callback = function($msg){
    echo ' [x] ', $msg->body, "\n";
};

$adapter->subscribe('my_channel', $callback);