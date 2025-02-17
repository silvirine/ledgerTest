<?php

namespace App\Controller;

use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/transactions')]
class TransactionController extends AbstractController
{
    #[Route('', name: 'transaction_index', methods: ['GET'])]
    #[OA\Get(
        summary: 'List all transactions',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Transaction list',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Transaction')
                )
            )
        ]
    )]
    public function index(EntityManagerInterface $em): JsonResponse
    {
        $transactions = $em->getRepository(Transaction::class)->findAll();
        return $this->json($transactions, 200);
    }

    #[Route('', name: 'transaction_create', methods: ['POST'])]
    #[OA\Post(
        summary: 'Create a new transaction',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['reference', 'description', 'transactionDate'],
                properties: [
                    new OA\Property(property: 'reference', type: 'string', example: 'TX123456'),
                    new OA\Property(property: 'description', type: 'string', example: 'Transaction description'),
                    new OA\Property(property: 'transactionDate', type: 'string', format: 'date-time', example: '2023-03-01T12:00:00Z')
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Transaction created',
                content: new OA\JsonContent(ref: '#/components/schemas/Transaction')
            ),
            new OA\Response(response: 400, description: 'Invalid input')
        ]
    )]
    public function create(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data || !is_array($data)) {
            return $this->json(['errors' => ['No valid JSON payload provided']], 400);
        }
        foreach (['reference', 'description', 'transactionDate'] as $field) {
            if (!array_key_exists($field, $data)) {
                return $this->json(['errors' => ["Field '$field' is required."]], 400);
            }
        }
        $transaction = new Transaction();
        $transaction->setReference($data['reference']);
        $transaction->setDescription($data['description']);
        try {
            $transaction->setTransactionDate(new \DateTime($data['transactionDate']));
        } catch (\Exception $e) {
            return $this->json(['errors' => ['Invalid transactionDate format.']], 400);
        }
        $errors = $validator->validate($transaction);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }
        $em->persist($transaction);
        $em->flush();$transactionData = [
            'id' => $transaction->getId(),
            'reference' => $transaction->getReference(),
            'description' => $transaction->getDescription(),
            'transactionDate' => $transaction->getTransactionDate() ? $transaction->getTransactionDate()->format(\DateTime::ATOM) : null,
        ];

        return $this->json($transactionData, 201);
    }

    #[Route('/{id}', name: 'transaction_show', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get transaction details',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Transaction ID',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Transaction details',
                content: new OA\JsonContent(ref: '#/components/schemas/Transaction')
            ),
            new OA\Response(response: 404, description: 'Transaction not found')
        ]
    )]
    public function show(Transaction $transaction): JsonResponse
    {
        $transactionData = [
            'id' => $transaction->getId(),
            'reference' => $transaction->getReference(),
            'description' => $transaction->getDescription(),
            'transactionDate' => $transaction->getTransactionDate() ? $transaction->getTransactionDate()->format(\DateTime::ATOM) : null,
        ];

        return $this->json($transactionData, 200);
    }

    #[Route('/{id}', name: 'transaction_update', methods: ['PUT'])]
    #[OA\Put(
        summary: 'Update a transaction',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Transaction ID',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'reference', type: 'string', example: 'TX654321'),
                    new OA\Property(property: 'description', type: 'string', example: 'Updated description'),
                    new OA\Property(property: 'transactionDate', type: 'string', format: 'date-time', example: '2023-03-01T12:00:00Z')
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Transaction updated',
                content: new OA\JsonContent(ref: '#/components/schemas/Transaction')
            ),
            new OA\Response(response: 400, description: 'Invalid input'),
            new OA\Response(response: 404, description: 'Transaction not found')
        ]
    )]
    public function update(Request $request, Transaction $transaction, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data || !is_array($data)) {
            return $this->json(['errors' => ['No valid JSON payload provided']], 400);
        }
        if (isset($data['reference'])) {
            $transaction->setReference($data['reference']);
        }
        if (isset($data['description'])) {
            $transaction->setDescription($data['description']);
        }
        if (isset($data['transactionDate'])) {
            try {
                $transaction->setTransactionDate(new \DateTime($data['transactionDate']));
            } catch (\Exception $e) {
                return $this->json(['errors' => ['Invalid transactionDate format.']], 400);
            }
        }
        $errors = $validator->validate($transaction);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }
        $em->flush();
        $transactionData = [
            'id' => $transaction->getId(),
            'reference' => $transaction->getReference(),
            'description' => $transaction->getDescription(),
            'transactionDate' => $transaction->getTransactionDate() ? $transaction->getTransactionDate()->format(\DateTime::ATOM) : null,
        ];

        return $this->json($transactionData, 200);

    }

    #[Route('/{id}', name: 'transaction_delete', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Delete a transaction',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Transaction ID',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 204, description: 'Transaction deleted'),
            new OA\Response(response: 404, description: 'Transaction not found')
        ]
    )]
    public function delete(Transaction $transaction, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($transaction);
        $em->flush();
        return $this->json(null, 204);
    }
}
