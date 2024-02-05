<?php

namespace App\Tests\FunctionalTest;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class ReservationTest extends ApiTestCase
{
    public function testListReservations(): void
    {
        $client = static::createClient();
        $user = $client->getContainer()->get('App\Repository\UserRepository')->findOneBy(['username' => 'admin']);
        $client->loginUser($user);

        $client->loginUser($user);
        $client->request('GET', '/api/users/ '.$user->getId().'/reservations', ['json' => []]);

        $this->assertResponseIsSuccessful();
    }

    public function testListReservationsOfOtherUser(): void
    {
        $client = static::createClient();
        $user = $client->getContainer()->get('App\Repository\UserRepository')->findOneBy(['username' => 'admin']);
        $client->loginUser($user);

        $client->loginUser($user);
        $client->request('GET', '/api/users/ '.($user->getId() + 1).'/reservations', ['json' => []]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testCreateReservation(): void
    {
        $client = static::createClient();
        $user = $client->getContainer()->get('App\Repository\UserRepository')->findOneBy(['username' => 'admin']);
        $car = $client->getContainer()->get('App\Repository\CarRepository')->findOneBy([], ['id' => 'ASC']);
        $client->loginUser($user);
        $client->request('POST', '/api/reservations', ['json' => [
            'carId' => $car->getId(),
            'startDate' => date('Y-m-d'),
            'endDate' => date('Y-m-d', strtotime('+1 day')),
        ]]);

        $this->assertResponseIsSuccessful();
    }

    public function testCreateReservationWithInvalidCar(): void
    {
        $client = static::createClient();
        $user = $client->getContainer()->get('App\Repository\UserRepository')->findOneBy(['username' => 'admin']);
        $client->loginUser($user);
        $client->request('POST', '/api/reservations', ['json' => [
            'carId' => '1000',
            'startDate' => date('Y-m-d'),
            'endDate' => date('Y-m-d', strtotime('+1 day')),
        ]]);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdateReservation()
    {
        $client = static::createClient();
        $user = $client->getContainer()->get('App\Repository\UserRepository')->findOneBy(['username' => 'admin']);
        $reservation = $client->getContainer()->get('App\Repository\ReservationRepository')->findOneBy([], ['id' => 'DESC']);
        $client->loginUser($user);
        $client->request('PUT', '/api/reservations/'.$reservation->getId(), ['json' => [
            'endDate' => date('Y-m-d', strtotime('+2 day')),
        ]]);

        $this->assertResponseIsSuccessful();
    }

    public function testUpdateReservationWithNotOwner()
    {
        $client = static::createClient();
        $user = $client->getContainer()->get('App\Repository\UserRepository')->findOneBy(['username' => 'admin']);
        $reservation = $client->getContainer()->get('App\Repository\ReservationRepository')->findFirstNotOwned($user);
        $client->loginUser($user);
        if (!$reservation) {
            $this->markTestSkipped('No reservation found');
        }
        $client->request('PUT', '/api/reservations/'.$reservation->getId(), ['json' => [
            'endDate' => date('Y-m-d', strtotime('+10 day')),
        ]]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testDeleteReservation()
    {
        $client = static::createClient();
        $user = $client->getContainer()->get('App\Repository\UserRepository')->findOneBy(['username' => 'admin']);
        $reservation = $client->getContainer()->get('App\Repository\ReservationRepository')->findOneBy([], ['id' => 'DESC']);
        $client->loginUser($user);
        $client->request('DELETE', '/api/reservations/'.$reservation->getId());

        $this->assertResponseIsSuccessful();
    }

    public function testDeleteReservationNotOwned()
    {
        $client = static::createClient();
        $user = $client->getContainer()->get('App\Repository\UserRepository')->findOneBy(['username' => 'admin']);
        $reservation = $client->getContainer()->get('App\Repository\ReservationRepository')->findFirstNotOwned($user);
        $client->loginUser($user);
        if (!$reservation) {
            $this->markTestSkipped('No reservation found');
        }
        $client->request('DELETE', '/api/reservations/'.$reservation->getId());

        $this->assertResponseStatusCodeSame(401);
    }
}
