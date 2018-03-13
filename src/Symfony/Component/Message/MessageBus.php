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

/**
 * @author Samuel Roze <samuel.roze@gmail.com>
 * @author Matthias Noback <matthiasnoback@gmail.com>
 */
class MessageBus implements MessageBusInterface
{
    /**
     * @var MiddlewareInterface[]
     */
    private $middlewares;

    /**
     * @param MiddlewareInterface[]|iterable $middlewares
     */
    public function __construct(iterable $middlewares = array())
    {
        $this->middlewares = is_array($middlewares) ? array_values($middlewares) : iterator_to_array($middlewares, false);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch($message)
    {
        return call_user_func($this->callableForNextMiddleware(0), $message);
    }

    private function callableForNextMiddleware(int $index): callable
    {
        if (!isset($this->middlewares[$index])) {
            return function () {};
        }

        $middleware = $this->middlewares[$index];

        return function ($message) use ($middleware, $index) {
            return $middleware->handle($message, $this->callableForNextMiddleware($index + 1));
        };
    }
}
