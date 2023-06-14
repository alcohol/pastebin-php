<?php

declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Console\Command;

use Paste\Repository\PasteRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'paste:read', description: 'Look up a paste.')]
final class ReadCommand extends Command
{
    protected PasteRepository $repository;

    public function __construct(PasteRepository $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id', InputArgument::REQUIRED, 'Identifier of paste to lookup.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($output instanceof ConsoleOutputInterface) {
            $output = $output->getErrorOutput();
        }

        /** @var string $id */
        $id = $input->getArgument('id');
        $paste = $this->repository->find($id);

        $output
            ->getFormatter()
            ->setStyle('bold', new OutputFormatterStyle(null, null, ['bold']))
        ;

        $output->writeln(sprintf('<bold>Code:</bold> %s', $paste->code));
        $output->writeln(sprintf('<bold>Body:</bold> %s', $paste->body));

        return Command::SUCCESS;
    }
}
