<?php

namespace App\Controller;

use App\Entity\Ledger;
use App\Entity\Wallet;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/ledgers')]
class LedgerController extends AbstractController
{
    #[Route('', name: 'ledger_index', methods: ['GET'])]
    #[OA\Get(
        summary: 'List all ledger entries',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Ledger list',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Ledger')
                )
            )
        ]
    )]
    public function index(EntityManagerInterface $em): JsonResponse
    {
        $ledgers = $em->getRepository(Ledger::class)->findAll();
        return $this->json($ledgers, 200);
    }

    #[Route('', name: 'ledger_create', methods: ['POST'])]
    #[OA\Post(
        summary: 'Create a new ledger entry',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['amount', 'description', 'transactionDate', 'transactionType', 'walletId', 'transactionId'],
                properties: [
                    new OA\Property(property: 'amount', type: 'number', format: 'float', example: 50.00),
                    new OA\Property(property: 'description', type: 'string', example: 'Payment received'),
                    new OA\Property(property: 'transactionDate', type: 'string', format: 'date-time', example: '2023-03-01T12:00:00Z'),
                    new OA\Property(property: 'transactionType', type: 'string', example: 'credit', enum: ['credit', 'debit']),
                    new OA\Property(property: 'walletId', type: 'integer', example: 1),
                    new OA\Property(property: 'transactionId', type: 'integer', example: 1)
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Ledger entry created',
                content: new OA\JsonContent(ref: '#/components/schemas/Ledger')
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
        // Check required fields explicitly
        foreach (['amount', 'description', 'transactionDate', 'transactionType', 'walletId', 'transactionId'] as $field) {
            if (!array_key_exists($field, $data)) {
                return $this->json(['errors' => ["Field '$field' is required."]], 400);
            }
        }

        $ledger = new Ledger();
        $ledger->setAmount((string)$data['amount']);
        $ledger->setDescription($data['description']);
        try {
            $ledger->setTransactionDate(new \DateTime($data['transactionDate']));
        } catch (\Exception $e) {
            return $this->json(['errors' => ['Invalid transactionDate format.']], 400);
        }
        $ledger->setTransactionType($data['transactionType']);

        // Fetch related Wallet entity
        $wallet = $em->getRepository(Wallet::class)->find($data['walletId']);
        if (!$wallet) {
            return $this->json(['errors' => ['Wallet not found.']], 400);
        }
        $ledger->setWallet($wallet);

        // Fetch related Transaction entity
        $transaction = $em->getRepository(Transaction::class)->find($data['transactionId']);
        if (!$transaction) {
            return $this->json(['errors' => ['Transaction not found.']], 400);
        }
        $ledger->setTransaction($transaction);

        $errors = $validator->validate($ledger);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }
        $em->persist($ledger);
        $em->flush();

        $ledgerData = [
            'id' => $ledger->getId(),
            'amount' => $ledger->getAmount(),
            'description' => $ledger->getDescription(),
            'transactionDate' => $ledger->getTransactionDate()
                ? $ledger->getTransactionDate()->format(\DateTime::ATOM)
                : null,
            'transactionType' => $ledger->getTransactionType(),
        ];

        return $this->json($ledgerData, 201);
    }

    #[Route('/{id}', name: 'ledger_show', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get ledger entry details',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Ledger ID',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Ledger details',
                content: new OA\JsonContent(ref: '#/components/schemas/Ledger')
            ),
            new OA\Response(response: 404, description: 'Ledger not found')
        ]
    )]
    public function show(Ledger $ledger): JsonResponse
    {
        $ledgerData = [
            'id' => $ledger->getId(),
            'amount' => $ledger->getAmount(),
            'description' => $ledger->getDescription(),
            'transactionDate' => $ledger->getTransactionDate()
                ? $ledger->getTransactionDate()->format(\DateTime::ATOM)
                : null,
            'transactionType' => $ledger->getTransactionType(),
            'wallet' => $ledger->getWallet() ? [
                'id'      => $ledger->getWallet()->getId(),
                'name'    => $ledger->getWallet()->getName(),
                'balance' => $ledger->getWallet()->getBalance(),
            ] : null,
            'transaction' => $ledger->getTransaction() ? [
                'id'              => $ledger->getTransaction()->getId(),
                'reference'       => $ledger->getTransaction()->getReference(),
                'description'     => $ledger->getTransaction()->getDescription(),
                'transactionDate' => $ledger->getTransaction()->getTransactionDate()
                    ? $ledger->getTransaction()->getTransactionDate()->format(\DateTime::ATOM)
                    : null,
            ] : null,
        ];

        return $this->json($ledgerData, 200);
    }

    #[Route('/{id}', name: 'ledger_update', methods: ['PUT'])]
    #[OA\Put(
        summary: 'Update a ledger entry',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Ledger ID',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'amount', type: 'number', format: 'float', example: 75.00),
                    new OA\Property(property: 'description', type: 'string', example: 'Updated description'),
                    new OA\Property(property: 'transactionDate', type: 'string', format: 'date-time', example: '2023-03-01T12:00:00Z'),
                    new OA\Property(property: 'transactionType', type: 'string', example: 'debit', enum: ['credit','debit']),
                    new OA\Property(property: 'walletId', type: 'integer', example: 1),
                    new OA\Property(property: 'transactionId', type: 'integer', example: 1)
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Ledger updated',
                content: new OA\JsonContent(ref: '#/components/schemas/Ledger')
            ),
            new OA\Response(response: 400, description: 'Invalid input'),
            new OA\Response(response: 404, description: 'Ledger not found')
        ]
    )]
    public function update(Request $request, Ledger $ledger, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data || !is_array($data)) {
            return $this->json(['errors' => ['No valid JSON payload provided']], 400);
        }
        if (isset($data['amount'])) {
            $ledger->setAmount((string)$data['amount']);
        }
        if (isset($data['description'])) {
            $ledger->setDescription($data['description']);
        }
        if (isset($data['transactionDate'])) {
            try {
                $ledger->setTransactionDate(new \DateTime($data['transactionDate']));
            } catch (\Exception $e) {
                return $this->json(['errors' => ['Invalid transactionDate format.']], 400);
            }
        }
        if (isset($data['transactionType'])) {
            $ledger->setTransactionType($data['transactionType']);
        }
        if (isset($data['walletId'])) {
            $wallet = $em->getRepository(Wallet::class)->find($data['walletId']);
            if (!$wallet) {
                return $this->json(['errors' => ['Wallet not found.']], 400);
            }
            $ledger->setWallet($wallet);
        }
        if (isset($data['transactionId'])) {
            $transaction = $em->getRepository(Transaction::class)->find($data['transactionId']);
            if (!$transaction) {
                return $this->json(['errors' => ['Transaction not found.']], 400);
            }
            $ledger->setTransaction($transaction);
        }
        $errors = $validator->validate($ledger);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }
        $em->flush();
        $ledgerData = [
            'id' => $ledger->getId(),
            'amount' => $ledger->getAmount(),
            'description' => $ledger->getDescription(),
            'transactionDate' => $ledger->getTransactionDate()
                ? $ledger->getTransactionDate()->format(\DateTime::ATOM)
                : null,
            'transactionType' => $ledger->getTransactionType(),
            'wallet' => $ledger->getWallet() ? [
                'id'      => $ledger->getWallet()->getId(),
                'name'    => $ledger->getWallet()->getName(),
                'balance' => $ledger->getWallet()->getBalance(),
            ] : null,
            'transaction' => $ledger->getTransaction() ? [
                'id'              => $ledger->getTransaction()->getId(),
                'reference'       => $ledger->getTransaction()->getReference(),
                'description'     => $ledger->getTransaction()->getDescription(),
                'transactionDate' => $ledger->getTransaction()->getTransactionDate()
                    ? $ledger->getTransaction()->getTransactionDate()->format(\DateTime::ATOM)
                    : null,
            ] : null,
        ];

        return $this->json($ledgerData, 200);
    }

    #[Route('/{id}', name: 'ledger_delete', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Delete a ledger entry',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Ledger ID',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 204, description: 'Ledger deleted'),
            new OA\Response(response: 404, description: 'Ledger not found')
        ]
    )]
    public function delete(Ledger $ledger, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($ledger);
        $em->flush();
        return $this->json(null, 204);
    }
}
