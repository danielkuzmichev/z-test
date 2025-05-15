<?php

namespace App\Service\Record;

use App\ApiResource\DTO\Record\RecordCreateDTO;
use App\DTO\RecordResponseDTO;
use App\Entity\Record;
use App\Exception\DuplicateException;
use App\Repository\Record\RecordRepositoryInterface;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class RecordService
{
    public function __construct(
        private EntityManagerInterface $em,
        private RecordRepositoryInterface $recordRepository
    ) {}

    public function create(RecordCreateDTO $dto): RecordResponseDTO
    {
        if(!empty($this->recordRepository->findByCode($dto->code))) {
            throw new DuplicateException('Code already exist ' . $dto->code);
        }

        if(!empty($this->recordRepository->findByNumber($dto->number))) {
            throw new DuplicateException('Number already exist ' . $dto->number);
        }

        $record = new Record();
        $record->setCode($dto->code);
        $record->setNumber($dto->number);
        $record->setStatus($dto->status);
        $record->setTitle($dto->title);
        $record->setChangeAt(new DateTime());

        $this->em->persist($record);
        $this->em->flush();

        return new RecordResponseDTO(
            $record->getId(),
            $record->getCode(),
            $record->getNumber(),
            $record->getStatus(),
            $record->getTitle(),
            $record->getChangeAt()
        );
    }

    public function get(int $id): ?RecordResponseDTO
    {
        $record = $this->recordRepository->getById($id);

        return new RecordResponseDTO(
            $record->getId(),
            $record->getCode(),
            $record->getNumber(),
            $record->getStatus(),
            $record->getTitle(),
            $record->getChangeAt()
        );
    }

    public function getAll(array $criteria = [], int $page = 1, int $limit = 10): array
    {
        
        $records = $this->recordRepository->getAll($criteria, $page, $limit);

        return array_map(fn($record) => new RecordResponseDTO(
            $record->getId(),
            $record->getCode(),
            $record->getNumber(),
            $record->getStatus(),
            $record->getTitle(),
            $record->getChangeAt()
        ), $records);
    }
}
