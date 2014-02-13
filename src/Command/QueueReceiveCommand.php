<?php

/**
 * Copyright 2014 Underground Elephant
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package     qpush-bundle
 * @copyright   Underground Elephant 2014
 * @license     Apache License, Version 2.0
 */

namespace Uecode\Bundle\QPushBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Uecode\Bundle\QPushBundle\Event\Events;
use Uecode\Bundle\QPushBundle\Event\MessageEvent;

/**
 * QueueReceiveCommand
 *
 * @author Keith Kirk <kkirk@undergroundelephant.com>
 */
class QueueReceiveCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('uecode:qpush:receive')
            ->setDescription('Polls the configured Queues')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'Name of a specific queue to poll',
                null
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $registry = $this->getContainer()->get('uecode_qpush');

        $name = $input->getArgument('name');

        if (null !== $name) {
            return $this->pollQueue($registry, $name);
        }

        foreach ($registry->all() as $queue) {
            $this->pollQueue($registry, $queue->getName());
        }

    }

    private function pollQueue($registry, $name)
    {
        if (!$registry->has($name)) {
            return $this->output->writeln(
                sprintf("The [%s] queue you have specified does not exists!", $name)
            );
        }

        $provider   = $registry->get($name);
        $messages   = $provider->receive();
        $count      = sizeof($messages);
        foreach ($messages as $message) {

            $messageEvent   = new MessageEvent($name, $message);
            $dispatcher     = $this->getContainer()->get('event_dispatcher');

            $dispatcher->dispatch(Events::Message($name), $messageEvent);
        }

        $msg = "<info>Finished polling %s Queue, %d messages fetched.</info>";
        $this->output->writeln(sprintf($msg, $name, $count));

        return 0;
    }
}
