<?php

namespace Alcalyn\UserApi\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Alcalyn\UserApi\Api\ApiInterface;

class CreateUserCommand extends Command
{
    /**
     * @var ApiInterface
     */
    protected $api;

    /**
     * @param ApiInterface $api
     */
    public function __construct(ApiInterface $api)
    {
        parent::__construct();

        $this->api = $api;
    }

    /**
     * {@InheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('userapi:create:user')
            ->setDescription('Create a new user.')
            ->addArgument('username', InputArgument::REQUIRED)
            ->addArgument('password', InputArgument::REQUIRED)
        ;
    }

    /**
     * {@InheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');

        try {
            $createdUser = $this->api->createUser($username, $password);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            $output->writeln('Oops, an user '.$username.' already exists.');
            return;
        }

        $output->writeln('User '.$username.' has been created.');
        $output->writeln('Full player data:');
        $output->writeln(print_r($createdUser, true));
    }
}
