<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WalletControllerTest extends WebTestCase
{
    public function testIndexReturnsWallets(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/wallets');

        // Assert that the response status code is 200 OK
        $this->assertResponseIsSuccessful();

        // Decode the response and assert that it's an array
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data, 'Expected response to be an array');
    }

    public function testCreateWalletWithInvalidDataReturns400(): void
    {
        $client = static::createClient();

        // Send a POST request with invalid data (wrong key: 'babne' instead of 'balance')
        $client->request(
            'POST',
            '/api/wallets',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Test Wallet'
            ])
        );

        // Expect a 400 Bad Request response since required field "balance" is missing
        $this->assertResponseStatusCodeSame(400);

        // Optionally, check the error message structure
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
    }

    public function testCreateWalletWithValidDataReturns201(): void
    {
        $client = static::createClient();

        // Send a valid JSON payload to create a wallet
        $client->request(
            'POST',
            '/api/wallets',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Test Wallet',
                'balance' => 100.50,
                'currency' => 'EUR'
            ])
        );

        // Assert that the response status code is 201 Created
        $this->assertResponseStatusCodeSame(201);

        // Decode the response JSON
        $data = json_decode($client->getResponse()->getContent(), true);

        // Assert that the returned data contains the expected fields
        $this->assertArrayHasKey('id', $data, 'Response should include an id');
        $this->assertArrayHasKey('name', $data, 'Response should include a name');
        $this->assertArrayHasKey('balance', $data, 'Response should include a balance');
        $this->assertArrayHasKey('currency', $data, 'Response should include a currency');
        $this->assertEquals('Test Wallet', $data['name']);
        $this->assertEquals('EUR', $data['currency']);
    }
}
