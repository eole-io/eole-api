<?php

namespace Eole\Core\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Alcalyn\UserApi\Api\ApiInterface;

class CreateGuestCommand extends Command
{
    /**
     * @var ApiInterface
     */
    private $api;

    /**
     * @param ApiInterface $api
     */
    public function __construct(ApiInterface $api)
    {
        parent::__construct('eole:create:guest');

        $this->api = $api;
    }

    /**
     * {@InheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Create a new guest.')
        ;
    }

    /**
     * {@InheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $createdGuest = $this->api->createGuest();

        $output->writeln('Guest '.$createdGuest->getUsername().' has been created.');
        $output->writeln('Full guest data:');
        $output->writeln(print_r($createdGuest, true));
    }
}
