<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use App\Controller\ProfileController;
use App\Tests\Api\RefreshDatabaseTrait;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * @see ProfileController
 */
final class ProfileControllerTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    private Client $client;

    protected function setup(): void
    {
        $this->client = self::createClient();
    }

    /**
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @see ProfileController::__invoke()
     */
    public function testProfile(): void
    {
        $response = $this->client->request('POST', '/authentication_token', [
            'json' => [
                'email' => 'admin@example.com',
                'password' => 'admin',
            ],
        ]);
        $this->client->request('GET', '/profile', [
            'headers' => [
                'Authorization' => sprintf('Bearer %s', $response->toArray()['token']),
            ],
        ]);
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
        try {
            self::assertJsonContains([
                'email' => 'admin@example.com',
                'roles' => ['ROLE_ADMIN', 'ROLE_USER'],
            ]);
        } catch (ClientExceptionInterface
        |DecodingExceptionInterface
        |RedirectionExceptionInterface
        |ServerExceptionInterface
        |TransportExceptionInterface $e) {
        }
    }
}
