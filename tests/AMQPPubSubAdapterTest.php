<?php

namespace Tests;

use Mockery;
use PHPUnit\Framework\TestCase;
use PhpAmqpLib\Connection\AMQPStreamConnection as Client;
use LiFeAiR\PubSub\AMQP\AMQPPubSubAdapter;

class AMQPPubSubAdapterTest extends TestCase
{
    protected function getClient()
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('close')->once();
        return $client;
    }

    protected function getChannel()
    {
        $client = Mockery::mock(\stdClass::class);
        $client->callbacks = [];

        $client->shouldReceive('queue_declare')->once();
        $client->shouldReceive('queue_bind')->once();
        $client->shouldReceive('basic_consume')->once();
        $client->shouldReceive('wait')->times();
        $client->shouldReceive('basic_publish')->times();
        $client->shouldReceive('close')->once();
        return $client;
    }

    public function testGetClient()
    {
        $client = $this->getClient();
        $adapter = new AMQPPubSubAdapter($client);
        $this->assertSame($client, $adapter->getClient());
    }

    public function testSubscribe()
    {
        $loop = Mockery::mock('\Tests\Mocks\MockRedisPubSubLoop[subscribe]');
        $loop->shouldReceive('subscribe')
            ->with('channel_name')
            ->once();

        $client = $this->getClient();
        $client->shouldReceive('channel')
            ->once()
            ->andReturn($this->getChannel());
        $client->shouldReceive('pubSubLoop')
            ->once()
            ->andReturn($loop);

        $adapter = new AMQPPubSubAdapter($client);

        $handler1 = Mockery::mock(\stdClass::class);
        $handler1->shouldReceive('handle')
            ->with(['hello' => 'world'])
            ->once();
        $adapter->subscribe('channel_name', [$handler1, 'handle']);
    }

    public function testPublish()
    {
        $client = $this->getClient();
        $client->shouldReceive('channel')
            ->once()
            ->andReturn($this->getChannel());
        $client->shouldReceive('publish')
            ->withArgs([
                'channel_name',
                '{"hello":"world"}',
            ])
            ->once();

        $adapter = new AMQPPubSubAdapter($client);
        $adapter->publish('channel_name', ['hello' => 'world']);
    }

    public function testPublishBatch()
    {
        $client = $this->getClient();
        $client->shouldReceive('channel')
            ->once()
            ->andReturn($this->getChannel());
        $client->shouldReceive('publish')
            ->withArgs([
                'channel_name',
                '"message1"',
            ])
            ->once();
        $client->shouldReceive('publish')
            ->withArgs([
                'channel_name',
                '"message2"',
            ])
            ->once();

        $adapter = new AMQPPubSubAdapter($client);
        $messages = [
            'message1',
            'message2',
        ];
        $adapter->publishBatch('channel_name', $messages);
    }
}
