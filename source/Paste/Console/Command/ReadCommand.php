<?php

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Alcohol\Paste\Console\Command;

use Alcohol\Paste\Entity\PasteManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReadCommand extends Command
{
    /** @var PasteManager */
    protected $manager;

    public function __construct(PasteManager $manager)
    {
        parent::__construct();

        $this->manager = $manager;
    }

    protected function configure()
    {
        $this
            ->setName('paste:read')
            ->setDescription('Show details of a paste.')
            ->addArgument('id', InputArgument::REQUIRED, 'Identifier of paste to lookup.')
            ->setHelp(
                'In verbose mode (<comment>-v</comment>) <info>%command.name%</info> will include the paste body.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($output instanceof ConsoleOutputInterface) {
            $output = $output->getErrorOutput();
        }

        $paste = $this->manager->read($input->getArgument('id'));

        $output
            ->getFormatter()
            ->setStyle('bold', new OutputFormatterStyle(null, null, ['bold']))
        ;

        $output->writeln(sprintf('<bold>Code:</bold> %s', $paste->getCode()));
        $output->writeln(sprintf('<bold>Token:</bold> %s', $paste->getToken()));

        if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
            $output->writeln(sprintf('<bold>Body:</bold> %s', $paste->getBody()));
        }

        return 0;
    }
}
