<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Message;

use Symfony\Component\Message\Asynchronous\Transport\ReceivedMessage;
use Symfony\Component\Message\Transport\ReceiverInterface;

/**
 * @author Samuel Roze <samuel.roze@gmail.com>
 */
class Worker
{
    private $receiver;
    private $bus;

    public function __construct(ReceiverInterface $receiver, MessageBusInterface $bus)
    {
        $this->receiver = $receiver;
        $this->bus = $bus;
    }

    /**
     * Receive the messages and dispatch them to the bus.
     */
    public function run()
    {
        $messageGenerator = $this->receiver->receive();

        foreach ($messageGenerator as $message) {
            if (!$message instanceof ReceivedMessage) {
                $message = new ReceivedMessage($message);
            }

            try {
                $this->bus->dispatch($message);
            } catch (\Throwable $e) {
                // Return the exception to the message generator so it can properly handle it if needed.
                $messageGenerator->throw($e);
            }
        }
    }
}
