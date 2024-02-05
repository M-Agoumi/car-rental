<?php

namespace App\Tests\FunctionalTest;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class CarTest extends ApiTestCase
{
    /**
     * @throws TransportExceptionInterface
     */
    public function testUpdateAsAdmin()
    {
        $client = static::createClient();
        $user = $client->getContainer()->get('App\Repository\UserRepository')->findOneBy(['username' => 'admin']);
        $client->loginUser($user);
        $car = $client->getContainer()->get('App\Repository\CarRepository')->findOneBy([], ['id' => 'ASC']);
        $client->request('PUT', '/api/cars/' . $car->getId(), ['json' => [
            'name' => 'Seat Ibiza updated',
            'description' => 'A nice car',
            'seats' => 4,
            'isPublished' => true,
        ]]);

        $this->assertResponseIsSuccessful();
    }


    /**
     * @throws TransportExceptionInterface
     */
    public function testUpdateAsUser()
    {
        $client = static::createClient();
        $user = $client->getContainer()->get('App\Repository\UserRepository')->findOneBy(['username' => 'testuser1']);
        $client->loginUser($user);
        $car = $client->getContainer()->get('App\Repository\CarRepository')->findOneBy([], ['id' => 'ASC']);
        $client->request('PUT', '/api/cars/' . $car->getId(), ['json' => [
            'name' => 'Toyota Corolla updated',
            'description' => 'A nice car',
            'seats' => 4,
            'isPublished' => true,
        ]]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testDeleteAsAdmin()
    {
        $client = static::createClient();
        $user = $client->getContainer()->get('App\Repository\UserRepository')->findOneBy(['username' => 'admin']);
        // get last inserted car
        $car = $client->getContainer()->get('App\Repository\CarRepository')->findOneBy([], ['id' => 'DESC']);
        $client->loginUser($user);
        $client->request('DELETE', '/api/cars/' . $car->getId());

        $this->assertResponseIsSuccessful();
    }

    public function testDeleteAsUser()
    {
        $client = static::createClient();
        $user = $client->getContainer()->get('App\Repository\UserRepository')->findOneBy(['username' => 'testuser1']);
        // get last inserted car
        $car = $client->getContainer()->get('App\Repository\CarRepository')->findOneBy([], ['id' => 'DESC']);
        $client->loginUser($user);
        $client->request('DELETE', '/api/cars/' . $car->getId());

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCreateAsAdmin(): void
    {
        $client = static::createClient();
        $user = $client->getContainer()->get('App\Repository\UserRepository')->findOneBy(['username' => 'admin']);
        $client->loginUser($user);
        $client->request('POST', '/api/cars', ['json' => [
            'name' => 'Seat Ibiza',
            'description' => 'A nice car',
            'seats' => 4,
            'isPublished' => true,
        ]]);

        $this->assertResponseIsSuccessful();
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testCreateAsUser(): void
    {
        $client = static::createClient();
        $user = $client->getContainer()->get('App\Repository\UserRepository')->findOneBy(['username' => 'testuser1']);
        $client->loginUser($user);
        $client->request('POST', '/api/cars', ['json' => [
            'name' => 'Seat Ibiza',
            'description' => 'A nice car',
            'seats' => 4,
            'isPublished' => true,
        ]]);

        $this->assertResponseStatusCodeSame(403);
    }
}
