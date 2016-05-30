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

        $gameProviders = $this->silexApplication->getGameProviders();
        $installedGames = $gameRepository->findAll();

        $installedGamesNames = array();

        foreach ($installedGames as $game) {
            $installedGamesNames []= $game->getName();
        }

        $newDetectedGames = array_diff(array_keys($gameProviders), $installedGamesNames);

        if (0 === count($newDetectedGames)) {
            $output->writeln('No new games detected.');

            return null;
        }

        $output->writeln('New games has been detected, installing...');
        $om = $this->silexApplication['orm.em'];

        foreach ($newDetectedGames as $gameName) {
            $gameProvider = $gameProviders[$gameName];

            $output->write('    '.$gameName.'...');
            $om->persist($gameProvider->createGame());
            $gameProvider->createFixtures($this->silexApplication, $om);

            $output->writeln(' installed.');
        }

        $om->flush();
        $output->writeln('done.');
    }
}
