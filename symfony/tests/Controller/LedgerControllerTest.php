<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LedgerControllerTest extends WebTestCase
{
    public function testIndexReturnsLedgers(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/ledgers');

        // Assert that the response is successful (HTTP 200)
        $this->assertResponseIsSuccessful();

        // Decode response and assert it's an array
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data, 'Expected ledger list to be an array');
    }

    public function testCreateLedgerWithInvalidDataReturns400(): void
    {
        $client = static::createClient();

        // Send a POST request with missing required field(s)
        $client->request(
            'POST',
            '/api/ledgers',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                // For example, omit "amount" and "walletId"
                'description'     => 'Test ledger entry',
                'transactionDate' => '2023-03-01T12:00:00Z',
                'transactionType' => 'credit',
                'transactionId'   => 1
            ])
        );

        // Expect a 400 Bad Request response
        $this->assertResponseStatusCodeSame(400);

        // Optionally, check that errors are returned
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
    }

    public function testCreateLedgerWithValidDataReturns201(): void
    {
        $client = static::createClient();

        // Create a Transaction first.
        $uniqueReference = 'TXTEST' . uniqid();
        $client->request(
            'POST',
            '/api/transactions',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'reference'       => $uniqueReference,
                'description'     => 'Test transaction for ledger',
                'transactionDate' => '2023-03-01T12:00:00Z'
            ])
        );
        $this->assertResponseStatusCodeSame(201);
        $transactionData = json_decode($client->getResponse()->getContent(), true);
        $transactionId = $transactionData['id'] ?? null;
        $this->assertNotNull($transactionId, 'Transaction id should be returned.');

        // Create a Wallet if one does not already exist.
        $client->request(
            'POST',
            '/api/wallets',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name'    => 'Test Wallet',
                'balance' => 100.50
            ])
        );
        $this->assertResponseStatusCodeSame(201);
        $walletData = json_decode($client->getResponse()->getContent(), true);
        $walletId = $walletData['id'] ?? null;
        $this->assertNotNull($walletId, 'Wallet id should be returned.');

        // Now create a ledger entry using the created walletId and transactionId.
        $client->request(
            'POST',
            '/api/ledgers',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'amount'          => 75.50,
                'description'     => 'Payment for service',
                'transactionDate' => '2023-03-01T12:00:00Z',
                'transactionType' => 'debit',
                'walletId'        => $walletId,
                'transactionId'   => $transactionId
            ])
        );

        // Expect a 201 Created response for ledger creation.
        $this->assertResponseStatusCodeSame(201);

        // Decode the response and verify required fields exist.
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data, 'Ledger should have an id');
        $this->assertArrayHasKey('amount', $data, 'Ledger should have an amount');
        $this->assertArrayHasKey('description', $data, 'Ledger should have a description');
        $this->assertArrayHasKey('transactionType', $data, 'Ledger should have a transaction type');
    }

}
