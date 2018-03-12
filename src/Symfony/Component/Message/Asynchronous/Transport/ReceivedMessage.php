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

use Symfony\Component\Message\Asynchronous\Middleware\SendMessageMiddleware;

/**
 * Wraps a received message. This is mainly used by the `SendMessageMiddleware` middleware to identify
 * a message should not be sent if it was just received.
 *
 * @see SendMessageMiddleware
 *
 * @author Samuel Roze <samuel.roze@gmail.com>
 */
final class ReceivedMessage
{
    private $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }
}
