<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\FrameworkBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Message\MessageBusInterface;
use Symfony\Component\Message\Transport\Enhancers\MaximumCountReceiver;
use Symfony\Component\Message\Transport\ReceiverInterface;
use Symfony\Component\Message\Worker;

/**
 * @author Samuel Roze <samuel.roze@gmail.com>
 */
class MessageConsumeCommand extends Command
{
    protected static $defaultName = 'message:consume';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputArgument('receiver', InputArgument::REQUIRED, 'Name of the receiver'),
                new InputOption('bus', 'b', InputOption::VALUE_REQUIRED, 'Name of the bus to dispatch the messages to', 'message_bus'),
                new InputOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Limit the number of received messages'),
            ))
            ->setDescription('Consumes a message')
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> command consumes a message and dispatches it to the message bus.

    <info>php %command.full_name% <consumer-service-name></info>
EOF
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ContainerInterface $container */
        $container = $this->getApplication()->getKernel()->getContainer();

        if (!$container->has($receiverName = $input->getArgument('receiver'))) {
            throw new \RuntimeException(sprintf('Receiver "%s" does not exist.', $receiverName));
        }

        if (!($receiver = $container->get($receiverName)) instanceof ReceiverInterface) {
            throw new \RuntimeException(sprintf('Receiver "%s" is not a valid message consumer. It must implement the "%s" interface.', $receiverName, ReceiverInterface::class));
        }

        if (!$container->has($busName = $input->getOption('bus'))) {
            throw new \RuntimeException(sprintf('Bus "%s" does not exist.', $busName));
        }

        if (!($messageBus = $container->get($busName)) instanceof MessageBusInterface) {
            throw new \RuntimeException(sprintf('Bus "%s" is not a valid message bus. It must implement the "%s" interface.', $busName, MessageBusInterface::class));
        }

        if ($limit = $input->getOption('limit')) {
            $receiver = new MaximumCountReceiver($receiver, $limit);
        }

        $worker = new Worker($receiver, $messageBus);
        $worker->run();
    }
}
