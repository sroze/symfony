<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Messenger\Asynchronous\Routing;

use Symfony\Component\Messenger\Transport\SenderInterface;

/**
 * @author Samuel Roze <samuel.roze@gmail.com>
 */
interface SenderLocatorInterface
{
    /**
     * Gets the producer (if applicable) for the given message object.
     *
     * @param object $message
     *
     * @return SenderInterface[]
     */
    public function getSendersForMessage($message): array;
}
