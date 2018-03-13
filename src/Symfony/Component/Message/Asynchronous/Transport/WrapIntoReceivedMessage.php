<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Message\Asynchronous\Transport;

use Symfony\Component\Message\Transport\ReceiverInterface;

/**
 * @author Samuel Roze <samuel.roze@gmail.com>
 */
class WrapIntoReceivedMessage implements ReceiverInterface
{
    private $decoratedReceiver;

    public function __construct(ReceiverInterface $decoratedConsumer)
    {
        $this->decoratedReceiver = $decoratedConsumer;
    }

    public function receive(): \Generator
    {
        $generator = $this->decoratedReceiver->receive();

        foreach ($generator as $message) {
            try {
                yield new ReceivedMessage($message);
            } catch (\Throwable $e) {
                $generator->throw($e);
            }
        }
    }
}
