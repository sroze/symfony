<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\PhpAmqp;

/**
 * An AMQP connection.
 *
 * @author Samuel Roze <samuel.roze@gmail.com>
 */
class Connection
{
    private $amqpConnectionCredentials;
    private $exchangeName;
    private $queueName;
    private $debug;

    /**
     * @var \AMQPChannel|null
     */
    private $amqpChannel;

    /**
     * @var \AMQPExchange|null
     */
    private $amqpExchange;

    /**
     * @var \AMQPQueue|null
     */
    private $amqpQueue;

    public function __construct(array $amqpConnectionCredentials, string $exchangeName, string $queueName, bool $debug = false)
    {
        $this->amqpConnectionCredentials = $amqpConnectionCredentials;
        $this->exchangeName = $exchangeName;
        $this->queueName = $queueName;
        $this->debug = $debug;
    }

    public static function fromDsn(string $dsn, bool $debug = false)
    {
        if (false === ($parsedUrl = parse_url($dsn))) {
            throw new \InvalidArgumentException(sprintf('The given AMQP DSN "%s" is invalid.', $dsn));
        }

        $pathParts = explode(trim($parsedUrl['path'] ?? '', '/'), '/');

        $amqpOptions = [
            'host' => $parsedUrl['host'] ?? 'localhost',
            'port' => $parsedUrl['port'] ?? 5672,
            'vhost' => $pathParts[0] ?? '/',
            'queue_name' => $queueName = $pathParts[1] ?? 'messages',
            'exchange_name' => $queueName
        ];

        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $parsedQuery);

            $amqpOptions = array_merge($amqpOptions, $parsedQuery);
        }

        return new self($amqpOptions, $amqpOptions['exchange_name'], $amqpOptions['queue_name'], $debug);
    }

    /**
     * @throws \AMQPException
     */
    public function publish(string $body, array $headers = [])
    {
        if ($this->debug) {
            $this->queue();
        }

        $this->exchange()->publish($body, null, AMQP_NOPARAM, [
            'headers' => $headers
        ]);
    }

    /**
     * Waits and gets a message from the configured queue.
     *
     * @throws \AMQPException
     */
    public function waitAndGet() : ?\AMQPEnvelope
    {
        $message = null;
        $this->queue()->consume(function (\AMQPEnvelope $envelope) use (&$message) {
            $message = $envelope;

            return false;
        });

        return $message;
    }

    private function channel(): \AMQPChannel
    {
        if (null === $this->amqpChannel) {
            $connection = new \AMQPConnection($this->amqpConnectionCredentials);

            if (false === $connection->connect()) {
                throw new \AMQPException('Could not connect to the AMQP server. Please verify the provided DSN.');
            }

            $this->amqpChannel = new \AMQPChannel($connection);
        }

        return $this->amqpChannel;
    }

    private function queue() : \AMQPQueue
    {
        if (null === $this->amqpQueue) {
            $this->amqpQueue = new \AMQPQueue($this->channel());
            $this->amqpQueue->setName($this->queueName);
            $this->amqpQueue->setFlags(AMQP_DURABLE);
            $this->amqpQueue->declareQueue();
            $this->amqpQueue->bind($this->exchange()->getName());
        }

        return $this->amqpQueue;
    }

    private function exchange() : \AMQPExchange
    {
        if (null === $this->amqpExchange) {
            $this->amqpExchange = new \AMQPExchange($this->channel());
            $this->amqpExchange->setName($this->exchangeName);
            $this->amqpExchange->setType(AMQP_EX_TYPE_FANOUT);
            $this->amqpExchange->setFlags(AMQP_DURABLE);
            $this->amqpExchange->declareExchange();
        }

        return $this->amqpExchange;
    }

    public function ack(\AMQPEnvelope $message)
    {
        return $this->queue()->ack($message->getDeliveryTag());
    }

    public function reject(\AMQPEnvelope $message)
    {
        return $this->queue()->reject($message->getDeliveryTag());
    }

    public function nack(\AMQPEnvelope $message)
    {
        return $this->queue()->nack($message->getDeliveryTag());
    }
}
