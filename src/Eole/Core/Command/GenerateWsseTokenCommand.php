<?php

namespace Eole\Core\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Alcalyn\UserApi\Api\ApiInterface;
use Eole\Core\Service\PlayerManager;

class GenerateWsseTokenCommand extends Command
{
    /**
     * @var ApiInterface
     */
    private $api;

    /**
     * @var PlayerManager
     */
    private $playerManager;

    /**
     * @param ApiInterface $api
     * @param PlayerManager $playerManager
     */
    public function __construct(ApiInterface $api, PlayerManager $playerManager)
    {
        parent::__construct();

        $this->api = $api;
        $this->playerManager = $playerManager;
    }

    /**
     * {@InheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('eole:generate:wsse-token')
            ->setDescription('Calculate a Wsse token.')
            ->addArgument('player', InputArgument::REQUIRED, 'Player pseudo which needs the Wsse token.')
        ;
    }

    /**
     * {@InheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $player = $this->api->getUser($input->getArgument('player'));
        $token = $this->playerManager->generateWsseToken($player);
        $output->writeln($token);
    }
}
