<?php

namespace App\Controller;

use App\ApiResource\DTO\Record\RecordCreateDTO;
use App\ApiResource\DTO\Record\RecordResponseDTO;
use App\Service\Record\RecordService;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use OpenApi\Attributes as OA;

class RecordController extends AbstractController
{
    public function __construct(private RecordService $service) {}

    #[Route('/api/record', name: 'create_record', methods: ['POST'])]
    #[OA\Post(
        path: '/api/record',
        summary: 'Создать запись',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: RecordCreateDTO::class))
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешное создание',
                content: new OA\JsonContent(ref: new Model(type: RecordResponseDTO::class))
            ),
            new OA\Response(
                response: 400,
                description: 'Ошибка валидации'
            )
        ]
    )]
    public function create(#[MapRequestPayload()] RecordCreateDTO $dto): JsonResponse
    {
        $record = $this->service->create($dto);
        return $this->json($record);
    }

    #[Route('/api/record/{id}', name: 'get_record', methods: ['GET'])]
    #[OA\Get(
        path: '/api/record/{id}',
        summary: 'Получить запись по ID',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешный ответ',
                content: new OA\JsonContent(ref: new Model(type: RecordResponseDTO::class))
            ),
            new OA\Response(
                response: 404,
                description: 'Запись не найдена'
            )
        ]
    )]
    public function get(int $id): JsonResponse
    {
        $record = $this->service->get($id);
        return $this->json($record);
    }

    #[Route('/api/records', name: 'list_records', methods: ['GET'])]
    #[OA\Get(
        path: '/api/records',
        summary: 'Список записей',
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'date', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список записей',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(type: 'object', ref: new Model(type: RecordResponseDTO::class)))
            )
        ]
    )]
    public function list(Request $request): JsonResponse
    {
        $criteria = $request->query->all();
        $page = isset($criteria['page']) && (int)$criteria['page'] > 0 ? (int)$criteria['page'] : 1;
        $limit = isset($criteria['limit']) && (int)$criteria['limit'] > 0 ? (int)$criteria['limit'] : 10;
        $records = $this->service->getAll($criteria, $page, $limit);
        return $this->json($records);
    }
}
