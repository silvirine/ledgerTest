<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TransactionControllerTest extends WebTestCase
{
    public function testIndexReturnsTransactions(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/transactions');

        // Assert that the response is successful
        $this->assertResponseIsSuccessful();

        // Decode the response and assert it's an array
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data, 'Expected transaction list to be an array');
    }

    public function testCreateTransactionWithInvalidDataReturns400(): void
    {
        $client = static::createClient();

        // Send a POST request with missing required fields (for example, omit 'reference')
        $client->request(
            'POST',
            '/api/transactions',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'description'     => 'Test transaction',
                'transactionDate' => '2023-03-01T12:00:00Z'
            ])
        );

        // Expect a 400 Bad Request response
        $this->assertResponseStatusCodeSame(400);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
    }

    public function testCreateTransactionWithValidDataReturns201(): void
    {
        $client = static::createClient();

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


        // Expect a 201 Created response
        $this->assertResponseStatusCodeSame(201);

        // Decode the response and verify required fields
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data, 'Transaction should have an id');
        $this->assertArrayHasKey('reference', $data, 'Transaction should have a reference');
        $this->assertArrayHasKey('description', $data, 'Transaction should have a description');
        $this->assertArrayHasKey('transactionDate', $data, 'Transaction should have a transactionDate');
    }
}
