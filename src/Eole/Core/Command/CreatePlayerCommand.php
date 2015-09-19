<?php

namespace Eole\Core\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Alcalyn\UserApi\Api\ApiInterface;
use Alcalyn\UserApi\Command\CreateUserCommand;

class CreatePlayerCommand extends CreateUserCommand
{
    /**
     * @param ApiInterface $api
     */
    public function __construct(ApiInterface $api)
    {
        parent::__construct($api);

        $this->api = $api;
    }

    /**
     * {@InheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('eole:create:player')
            ->setDescription('Create a new player.')
        ;
    }
}
