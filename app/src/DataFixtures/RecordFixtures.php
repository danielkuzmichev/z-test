<?php

namespace App\DataFixtures;

use App\Entity\Record;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RecordFixtures extends Fixture
{
    private const BATCH_SIZE = 25;
    private string $csvPath = __DIR__.'/data/test_task_data.csv';

    public function load(ObjectManager $em): void
    {
        $handle = fopen($this->csvPath, 'r');
        fgetcsv($handle);
        $i = 0;

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            [$externalCode, $number, $status, $title, $changedAt] = $row;

            $record = new Record();
            $record->setCode((int) $externalCode);
            $record->setNumber(trim($number));
            $record->setStatus(trim($status));
            $record->setTitle(trim($title));
            $date = \DateTime::createFromFormat('d.m.Y H:i:s', trim($changedAt));
            if (false === $date) {
                continue;
            }
            $record->setChangeAt($date);

            $em->persist($record);

            if ((++$i % self::BATCH_SIZE) === 0) {
                $em->flush();
                $em->clear();
            }
        }

        fclose($handle);
        $em->flush();
        $em->clear();
    }
}
