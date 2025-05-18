<?php

namespace App\Command;

use App\Entity\Record;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:import')]
class ImportCommand extends Command
{
    private const BATCH_SIZE = 50;
    private string $csvPath = __DIR__.'/../../migrations/data/test_task_data.csv';

    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!file_exists($this->csvPath)) {
            $output->writeln("<error>CSV file not found: {$this->csvPath}</error>");

            return Command::FAILURE;
        }

        if (($handle = fopen($this->csvPath, 'r')) === false) {
            $output->writeln("<error>Failed to open file: {$this->csvPath}</error>");

            return Command::FAILURE;
        }

        $output->writeln('<info>Starting CSV import...</info>');

        fgetcsv($handle);
        $i = 0;

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            if (count($row) < 5) {
                $output->writeln('<comment>Skipping invalid row</comment>');
                continue;
            }

            [$externalCode, $number, $status, $title, $changedAt] = $row;

            $record = new Record();
            $record->setCode((int) $externalCode);
            $record->setNumber(trim($number));
            $record->setStatus(trim($status));
            $record->setTitle(trim($title));
            $date = \DateTime::createFromFormat('d.m.Y H:i:s', trim($changedAt));
            if (false === $date) {
                $output->writeln("<comment>Invalid date format: $changedAt</comment>");
                continue;
            }
            $record->setChangeAt($date);

            $this->em->persist($record);

            if ((++$i % self::BATCH_SIZE) === 0) {
                $this->em->flush();
                $this->em->clear();
            }
        }

        fclose($handle);
        $this->em->flush();
        $this->em->clear();

        $output->writeln("<info>CSV import completed! Total records: $i</info>");

        return Command::SUCCESS;
    }
}
