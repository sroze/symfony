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

use Symfony\Bridge\PhpAmqp\Exception\RejectMessageException;
use Symfony\Component\Message\Transport\ReceiverInterface;
use Symfony\Component\Message\Transport\Serialization\DecoderInterface;

/**
 * Symfony Message receiver to get messages from AMQP brokers using PHP's AMQP extension.
 *
 * @author Samuel Roze <samuel.roze@gmail.com>
 */
class AmqpReceiver implements ReceiverInterface
{
    private $messageDecoder;
    private $connection;

    public function __construct(DecoderInterface $messageDecoder, Connection $connection)
    {
        $this->messageDecoder = $messageDecoder;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function receive(): iterable
    {
        while (true) {
            if (null === ($message = $this->connection->waitAndGet())) {
                continue;
            }

            try {
                yield $this->messageDecoder->decode([
                    'body' => $message->getBody(),
                    'headers' => $message->getHeaders(),
                ]);

                $this->connection->ack($message);
            } catch (RejectMessageException $e) {
                $this->connection->reject($message);
            } catch (\Throwable $e) {
                $this->connection->nack($message);
            }
        }
    }
}
