<?php

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Alcohol\PasteBundle\Command;

use Alcohol\PasteBundle\Entity\PasteManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends Command
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
            ->setName('paste:list')
            ->setDescription('Returns a list of currently stored pasties.')
            ->addOption(
                '--truncate',
                '-t',
                InputOption::VALUE_REQUIRED,
                '',
                80
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($output instanceof ConsoleOutputInterface) {
            $output = $output->getErrorOutput();
        }

        $pasties = $this->manager->getList();

        $length_max = $input->getOption('truncate');
        $length_sub = (int) floor(($length_max - 4) / 2);

        if (!count($pasties)) {
            $output->writeln('Database is empty.');

            return 0;
        }

        $table = new Table($output);
        $table->setHeaders(['code', 'token', 'size', 'body']);

        /** @var \Alcohol\PasteBundle\Entity\Paste $paste */
        foreach ($pasties as $paste) {
            $body = trim(preg_replace(['/\n\r?/', '/\s\s+/'], ' ', $paste->getBody()));
            $size = mb_strlen($body);

            if ($size > $length_max) {
                $body = sprintf(
                    '%s<comment> .. </comment>%s',
                    trim(substr($body, 0, $length_sub)),
                    trim(substr($body, -$length_sub))
                );
            }

            $table->addRow([$paste->getCode(), $paste->getToken(), $size, $body]);
        }

        $table->render();

        return 0;
    }
}
