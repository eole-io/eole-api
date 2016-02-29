<?php

namespace Eole\Silex\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Eole\Silex\Application as SilexApplication;

class InstallGamesCommand extends Command
{
    /**
     * @var SilexApplication
     */
    private $silexApplication;

    /**
     * @param SilexApplication $silexApplication
     */
    public function __construct(SilexApplication $silexApplication)
    {
        parent::__construct();

        $this->silexApplication = $silexApplication;
    }

    /**
     * {@InheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('eole:games:install')
            ->setDescription('Install new games added in environment.')
        ;
    }

    /**
     * {@InheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $gameRepository = $this->silexApplication['orm.em']->getRepository('Eole:Game');

        $registeredGames = $this->silexApplication['environment']['games'];
        $installedGames = $gameRepository->findAll();

        $registeredGamesNames = array_keys($registeredGames);
        $installedGamesNames = array();

        foreach ($installedGames as $game) {
            $installedGamesNames []= $game->getName();
        }

        $newDetectedGames = array_diff($registeredGamesNames, $installedGamesNames);

        if (0 === count($newDetectedGames)) {
            $output->writeln('No new games detected.');

            return null;
        }

        $output->writeln('New games has been detected, installing...');

        foreach ($newDetectedGames as $game) {
            $output->write('    '.$game.'...');
            $gameInterface = $this->silexApplication->createGameInterface($game);
            $this->silexApplication['eole.games']->installGame($gameInterface);
            $output->writeln(' installed.');
        }

        $this->silexApplication['orm.em']->flush();
        $output->writeln('done.');
    }
}
