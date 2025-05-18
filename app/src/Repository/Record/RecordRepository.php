<?php

namespace App\Repository\Record;

use App\Entity\Record;
use App\Exception\NotFoundException;
use App\Exception\WrongDateTimeFormatException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Record>
 */
class RecordRepository extends ServiceEntityRepository implements RecordRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Record::class);
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('r');
    }

    /**
     * @return Record Returns an Record objects
     */
    public function getById(string $id): Record
    {
        $record = $this->find($id);

        if (null === $record) {
            throw new NotFoundException("Not found record with id=$id");
        }

        return $record;
    }

    /**
     * @return Record[] Returns an array of Record objects
     */
    public function getAll(array $criteria, int $page = 1, int $limit = 10): array
    {
        $qb = $this->getQueryBuilder();
        if (isset($criteria['name']) && null !== $criteria['name']) {
            $this->mixinName($qb, $criteria['name']);
        }

        if (isset($criteria['date']) && null !== $criteria['date']) {
            $dateTime = \DateTime::createFromFormat('d.m.Y', $criteria['date']);
            if (!$dateTime) {
                throw new WrongDateTimeFormatException("Required format is 'd.m.Y'");
            }
            $this->mixinDate($qb, $dateTime);
        }

        $qb
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    private function mixinName(QueryBuilder $qb, ?string $name): void
    {
        $qb
            ->andWhere('r.title LIKE :name')
            ->setParameter('name', '%'.$name.'%');
    }

    private function mixinDate(QueryBuilder $qb, \DateTimeInterface $date): void
    {
        $startOfDay = (clone $date)->setTime(0, 0, 0);
        $endOfDay = (clone $date)->setTime(23, 59, 59);

        $qb
            ->andWhere('r.changeAt BETWEEN :start AND :end')
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay);
    }
}
