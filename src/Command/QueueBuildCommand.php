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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Keith Kirk <kkirk@undergroundelephant.com>
 */
class QueueBuildCommand extends Command implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     *
     * @api
     */
    protected $container;

    /**
     * Sets the Container associated with this Controller.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    protected $output;

    protected function configure()
    {
        $this
            ->setName('uecode:qpush:build')
            ->setDescription('Builds the configured Queues')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'Name of a specific queue to build',
                null
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $registry = $this->container->get('uecode_qpush');

        $name = $input->getArgument('name');

        if (null !== $name) {
            return $this->buildQueue($registry, $name);
        }

        foreach ($registry->all() as $queue) {
            $this->buildQueue($registry, $queue->getName());
        }

        return 0;
    }

    private function buildQueue($registry, $name)
    {
        if (!$registry->has($name)) {
            return $this->output->writeln(
                sprintf("The [%s] queue you have specified does not exists!", $name)
            );
        }

        $registry->get($name)->create();
        $this->output->writeln(sprintf("The %s queue has been built successfully.", $name));

        return 0;
    }
}
