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

use Symfony\Component\Messenger\Transport\SenderInterface;
use Symfony\Component\Messenger\Transport\Serialization\EncoderInterface;

/**
 * Symfony Message sender to send messages to AMQP brokers using PHP's AMQP extension.
 *
 * @author Samuel Roze <samuel.roze@gmail.com>
 */
class AmqpSender implements SenderInterface
{
    private $messageEncoder;
    private $connection;

    public function __construct(EncoderInterface $messageEncoder, Connection $connection)
    {
        $this->messageEncoder = $messageEncoder;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function send($message)
    {
        $encodedMessage = $this->messageEncoder->encode($message);

        $this->connection->publish(
            $encodedMessage['body'],
            $encodedMessage['headers']
        );
    }
}
