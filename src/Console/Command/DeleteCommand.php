<?php declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Console\Command;

use Paste\Repository\PasteRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class DeleteCommand extends Command
{
    /** @var ?string */
    protected static $defaultName = 'paste:delete';
    protected PasteRepository $repository;

    public function __construct(PasteRepository $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Deletes a paste.')
            ->addArgument('id', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'Identifier of paste to delete.', [])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($output instanceof ConsoleOutputInterface) {
            $output = $output->getErrorOutput();
        }

        $identifiers = $input->getArgument('id');

        foreach ($identifiers as $id) {
            $paste = $this->repository->find($id);
            $this->repository->delete($paste);

            if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
                $output->writeln(sprintf('Paste "<info>%s</info>" has been deleted.', $id));
            }
        }

        return 0;
    }
}
