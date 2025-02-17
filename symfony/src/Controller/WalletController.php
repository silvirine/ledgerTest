<?php

namespace App\Controller;

use App\Entity\Wallet;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/wallets')]
class WalletController extends AbstractController
{
    #[Route('', name: 'wallet_index', methods: ['GET'])]
    #[OA\Get(
        summary: 'List all wallets',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Wallet list',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Wallet')
                )
            )
        ]
    )]
    public function index(EntityManagerInterface $em): JsonResponse
    {
        $wallets = $em->getRepository(Wallet::class)->findAll();
        return $this->json($wallets);
    }

    #[Route('', name: 'wallet_create', methods: ['POST'])]
    #[OA\Post(
        summary: 'Create a new wallet',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'balance'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'My Wallet'),
                    new OA\Property(property: 'balance', type: 'number', format: 'float', example: 100.00)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Wallet created',
                content: new OA\JsonContent(ref: '#/components/schemas/Wallet')
            ),
            new OA\Response(response: 400, description: 'Invalid input')
        ]
    )]
    public function create(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        // Check if the JSON payload is provided
        if (!$data) {
            return $this->json(['errors' => ['No JSON payload provided']], 400);
        }

        // Validate the existence of required fields explicitly
        if (!isset($data['name']) || trim($data['name']) === '') {
            return $this->json(['errors' => ['Wallet name cannot be blank.']], 400);
        }
        if (!isset($data['balance'])) {
            return $this->json(['errors' => ['Wallet balance must be provided.']], 400);
        }

        $wallet = new Wallet();
        $wallet->setName($data['name']);
        $wallet->setBalance((string)$data['balance']);

        $errors = $validator->validate($wallet);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }

        $em->persist($wallet);
        $em->flush();
        $walletData = [
            'id' => $wallet->getId(),
            'name' => $wallet->getName(),
            'balance' => $wallet->getBalance(),
        ];
        return $this->json($walletData, 201);
    }

    #[Route('/{id}', name: 'wallet_show', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get wallet details',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Wallet ID',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Wallet details',
                content: new OA\JsonContent(ref: '#/components/schemas/Wallet')
            ),
            new OA\Response(response: 404, description: 'Wallet not found')
        ]
    )]
    public function show(Wallet $wallet): JsonResponse
    {
        $walletData = [
            'id' => $wallet->getId(),
            'name' => $wallet->getName(),
            'balance' => $wallet->getBalance(),
        ];
        return $this->json($walletData, 200);
    }

    #[Route('/{id}', name: 'wallet_update', methods: ['PUT'])]
    #[OA\Put(
        summary: 'Update a wallet',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Wallet ID',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Updated Wallet Name'),
                    new OA\Property(property: 'balance', type: 'number', format: 'float', example: 150.50)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Wallet updated',
                content: new OA\JsonContent(ref: '#/components/schemas/Wallet')
            ),
            new OA\Response(response: 400, description: 'Invalid input'),
            new OA\Response(response: 404, description: 'Wallet not found')
        ]
    )]
    public function update(Request $request, Wallet $wallet, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $wallet->setName($data['name']);
        }
        if (isset($data['balance'])) {
            $wallet->setBalance($data['balance']);
        }

        $errors = $validator->validate($wallet);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }

        $em->flush();
        $walletData = [
            'id' => $wallet->getId(),
            'name' => $wallet->getName(),
            'balance' => $wallet->getBalance(),
        ];
        return $this->json($walletData, 200);
    }

    #[Route('/{id}', name: 'wallet_delete', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Delete a wallet',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Wallet ID',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 204, description: 'Wallet deleted'),
            new OA\Response(response: 404, description: 'Wallet not found')
        ]
    )]
    public function delete(Wallet $wallet, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($wallet);
        $em->flush();
        return $this->json(null, 204);
    }
}
