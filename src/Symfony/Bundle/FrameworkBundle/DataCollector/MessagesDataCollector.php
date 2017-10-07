<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\FrameworkBundle\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\Message\MessageBusMiddlewareInterface;

/**
 * @author Samuel Roze <samuel.roze@gmail.com>
 */
class MessagesDataCollector extends DataCollector implements MessageBusMiddlewareInterface
{
    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'messages';
    }

    /**
     * {@inheritdoc}
     */
    public function handle($message, callable $next)
    {
        $debugRepresentation = [
            'message' => [
                'type' => get_class($message),
            ],
        ];

        try {
            $result = $next($message);

            if (is_object($result)) {
                $debugRepresentation['result'] = [
                    'type' => get_class($result),
                ];
            } else {
                $debugRepresentation['result'] = [
                    'type' => gettype($result),
                    'value' => $result,
                ];
            }
        } catch (\Throwable $exception) {
            $debugRepresentation['exception'] = [
                'type' => get_class($exception),
                'message' => $exception->getMessage(),
            ];
        }

        $this->data[] = $debugRepresentation;

        if (isset($exception)) {
            throw $exception;
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->data;
    }
}
