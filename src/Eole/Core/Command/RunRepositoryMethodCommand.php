<?php

namespace Eole\Core\Command;

use Doctrine\Common\Util\Debug;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class RunRepositoryMethodCommand extends Command
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        parent::__construct();

        $this->om = $om;
    }

    /**
     * {@InheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('orm:run-method')
            ->setDescription('Run a repository method.')
            ->addArgument('entity', InputArgument::REQUIRED)
            ->addArgument('method', InputArgument::REQUIRED)
            ->addArgument('arguments', InputArgument::IS_ARRAY)
            ->addOption('depth', 'd', InputOption::VALUE_OPTIONAL, 'Max depth of the output.', 4)
        ;
    }

    /**
     * {@InheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entity = $input->getArgument('entity');
        $method = $input->getArgument('method');
        $arguments = $input->getArgument('arguments');
        $depth = $input->getOption('depth');

        $repository = $this->om->getRepository($entity);

        $output->writeln(sprintf(
            '%s::%s(%s);',
            get_class($repository),
            $method,
            implode(', ', $arguments)
        ));

        $result = call_user_func_array(array($repository, $method), $arguments);

        $output->writeln('Result:');
        $output->writeln(Debug::dump($result, $depth, true, false));
    }
}
