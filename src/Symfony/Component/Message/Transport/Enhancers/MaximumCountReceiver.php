<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Message\Transport\Enhancers;

use Symfony\Component\Message\Transport\ReceiverInterface;

class MaximumCountReceiver implements ReceiverInterface
{
    private $decoratedReceiver;
    private $maximumNumberOfMessages;

    public function __construct(ReceiverInterface $decoratedReceiver, int $maximumNumberOfMessages)
    {
        $this->decoratedReceiver = $decoratedReceiver;
        $this->maximumNumberOfMessages = $maximumNumberOfMessages;
    }

    public function receive(): \Generator
    {
        $generator = $this->decoratedReceiver->receive();
        $receivedMessages = 0;

        foreach ($generator as $message) {
            try {
                yield $message;
            } catch (\Throwable $e) {
                $generator->throw($e);
            }

            if (++$receivedMessages > $this->maximumNumberOfMessages) {
                break;
            }
        }
    }
}
