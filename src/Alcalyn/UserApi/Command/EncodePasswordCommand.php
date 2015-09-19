<?php

namespace Alcalyn\UserApi\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Alcalyn\UserApi\Service\UserManager;

class EncodePasswordCommand extends Command
{
    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * @param UserManager $userManager
     */
    public function __construct(UserManager $userManager)
    {
        parent::__construct();

        $this->userManager = $userManager;
    }

    /**
     * {@InheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('userapi:encode:password')
            ->setDescription('Generate salt and hash for a clear password.')
            ->addArgument('password', InputArgument::REQUIRED, 'Clear password.')
            ->addOption('salt', null, InputOption::VALUE_REQUIRED, 'Salt to use. Generate a salt if not set.')
            ->addOption('userclass', null, InputOption::VALUE_REQUIRED, 'User class, defaults to UserApi user class.')
        ;
    }

    /**
     * {@InheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $password = $input->getArgument('password');
        $salt = $input->getOption('salt');
        $userClass = $input->getOption('userclass');
        $user = null;

        if (null !== $userClass) {
            if (!class_exists($userClass)) {
                $output->writeln('Please provide a valid user class with namespace.');
                $output->writeln('Class "'.$userClass.'" does not exists.');

                return false;
            }

            $user = new $userClass();
        }

        $encoded = $this->userManager->encodePassword($password, $user, $salt);

        $output->writeln('Clear password: '.$password);
        $output->writeln('User class:     '.$encoded['user']);
        $output->writeln('Salt used:      '.$encoded['salt']);
        $output->writeln('Generated hash: '.$encoded['hash']);
    }
}
