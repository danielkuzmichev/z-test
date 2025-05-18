<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Record;
use Doctrine\ORM\EntityManagerInterface;

class RecordTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->em->beginTransaction(); 
    }

    public function testCreateRecord(): void
    {
        $payload = [
            'code' => 123456,
            'number' => 'ABC-001',
            'status' => 'new',
            'title' => 'Test Record',
            'changeAt' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];

        $this->client->request('POST', '/api/record', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame('ABC-001', $data['number']);
    }

    public function testViewRecord(): void
    {
        $record = $this->em->getRepository(Record::class)->findOneBy([]);

        $this->assertNotNull($record, 'Тест требует хотя бы одну запись в базе.');

        $this->client->request('GET', '/api/record/' . $record->getId());

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame($record->getId(), $data['id']);
    }

    public function testRecordNotFound(): void
    {
        $this->client->request('GET', '/api/record/99999999');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testDuplicateCreate(): void
    {
        $existing = $this->em->getRepository(Record::class)->findOneBy([]);
        $this->assertNotNull($existing);

        $payload = [
            'code' => $existing->getCode(),
            'number' => $existing->getNumber(),
            'status' => 'duplicate',
            'title' => 'Should Fail',
            'changeAt' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];

        $this->client->request('POST', '/api/record', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testListWithFilters(): void
    {
        $record = $this->em->getRepository(Record::class)->findOneBy([]);
        $this->assertNotNull($record);

        $changeAt = '14.08.2022';

        $this->client->request('GET', '/api/records?date=' . $changeAt . '&page=1&limit=5');

        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($data);

        $found = false;
        foreach ($data as $item) {
            if ($item['number'] === '17662-2') {
                $found = true;
                break;
            }
        }

         $this->assertTrue($found);
    }

    public function testInvalidDatetimeFormat(): void
    {
        $record = $this->em->getRepository(Record::class)->findOneBy([]);
        $this->assertNotNull($record);

        $changeAt = $record->getChangeAt()->format('Y-m-d');

        $this->client->request('GET', '/api/records?date=' . $changeAt . '&page=1&limit=5');

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    protected function tearDown(): void
    {
        if ($this->em->getConnection()->isTransactionActive()) {
            $this->em->rollback();
        }
        parent::tearDown();
    }
}
