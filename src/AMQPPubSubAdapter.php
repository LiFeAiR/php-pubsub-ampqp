<?php


namespace LiFeAiR\PubSub\AMQP;

use PhpAmqpLib\Connection\AMQPStreamConnection as Client;
use PhpAmqpLib\Message\AMQPMessage;
use Superbalist\PubSub\PubSubAdapterInterface;
use Superbalist\PubSub\Utils;

class AMQPPubSubAdapter implements PubSubAdapterInterface
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Return the Redis client.
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Subscribe a handler to a channel.
     *
     * @param string $channel
     * @param callable $handler
     */
    public function subscribe($channel, callable $handler)
    {
        $channelObject = $this->client->channel();

        list($queue_name, ,) = $channelObject->queue_declare('', false, false, true, false);
        $channelObject->queue_bind($queue_name, $channel);
        $channelObject->basic_consume($queue_name, '', false, true, false, false, $handler);

        while(count($channelObject->callbacks)) {
            $channelObject->wait();
        }

        $channelObject->close();
    }

    /**
     * Publish a message to a channel.
     *
     * @param string $channel
     * @param mixed $message
     */
    public function publish($channel, $message)
    {
        $channelObject = $this->client->channel();

        $msg = new AMQPMessage(Utils::serializeMessage($message));
        $channelObject->basic_publish($msg, $channel);

        $channelObject->close();
    }

    /**
     * Publish multiple messages to a channel.
     *
     * @param string $channel
     * @param array $messages
     */
    public function publishBatch($channel, array $messages)
    {
        foreach ($messages as $message) {
            $this->publish($channel, $message);
        }
    }

    public function __destruct()
    {
        $this->client->close();
    }

}