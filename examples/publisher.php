<?php
require_once __DIR__ . '/../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

$client = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$adapter = new LiFeAiR\PubSub\AMQP\AMQPPubSubAdapter($client);

// publish messages
$adapter->publish('my_channel', 'HELLO WORLD');
$adapter->publish('my_channel', ['hello' => 'world']);
$adapter->publish('my_channel', 1);
$adapter->publish('my_channel', false);

// publish multiple messages
$messages = [
    'message 1',
    'message 2',
];
$adapter->publishBatch('my_channel', $messages);