<?php

namespace App\Tests\Service;

use App\Entity\Car;
use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use App\Service\ReservationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ReservationServiceTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private ReservationRepository $reservationRepository;

    private ReservationService $reservationService;

    private UserRepository $userRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = $this->getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->userRepository = $container->get(UserRepository::class);
        $this->reservationRepository = $container->get(ReservationRepository::class);
        $this->reservationService = new ReservationService($this->entityManager, $this->reservationRepository);
    }

    public function testReserve(): void
    {
        // Arrange
        $car = $this->createTestCar();
        $startDate = new \DateTimeImmutable('tomorrow');
        $endDate = new \DateTimeImmutable('tomorrow + 1 day');
        $user = $this->userRepository->findOneBy([]);

        // Act
        $result = $this->reservationService->reserve($car, $startDate, $endDate, $user);

        // Assert
        $this->assertEmpty($result, 'The reservation should be successful.');
    }

    public function testReserveWithInvalidDates(): void
    {
        // Arrange
        $car = $this->createTestCar();
        $startDate = new \DateTimeImmutable('yesterday');
        $endDate = new \DateTimeImmutable('yesterday + 1 day');
        $user = $this->userRepository->findOneBy([]);

        // Act
        $result = $this->reservationService->reserve($car, $startDate, $endDate, $user);

        // Assert
        $this->assertNotEmpty($result, 'The reservation should not be successful.');
    }

    public function testUpdateReservation(): void
    {
        // Arrange
        $reservation = $this->createTestReservation();
        $newStartDate = new \DateTime('tomorrow + 1 day');
        $newEndDate = new \DateTime('tomorrow + 2 days');

        // Act
        $this->reservationService->updateReservation($reservation, $reservation->getCar(), $newStartDate, $newEndDate);

        // Assert
        $updatedReservation = $this->entityManager->getRepository(Reservation::class)->find($reservation->getId());

        $this->assertEquals($newStartDate, $updatedReservation->getStartDate(), 'The start date should be updated.');
        $this->assertEquals($newEndDate, $updatedReservation->getEndDate(), 'The end date should be updated.');
    }

    public function testDeleteReservation(): void
    {
        // Arrange
        $reservation = $this->createTestReservation();
        $id = $reservation->getId();

        $reservationService = new ReservationService($this->entityManager, $this->reservationRepository);

        // Act
        $reservationService->deleteReservation($reservation);

        // Assert
        $deletedReservation = $this->entityManager->getRepository(Reservation::class)->find($id);

        $this->assertNull($deletedReservation, 'The reservation should be deleted from the database.');
    }

    private function createTestCar(): Car
    {
        // Create a test car entity for testing
        $car = new Car();
        $car->setName('Test Car');
        $car->setSeats(4);
        $car->setIsPublished(true);
        $car->setCreatedAt(new \DateTimeImmutable('now'));

        $this->entityManager->persist($car);
        $this->entityManager->flush();

        return $car;
    }

    private function createTestReservation(): Reservation
    {
        // Create a test reservation entity for testing
        $car = $this->createTestCar();
        $user = $this->userRepository->findOneBy([]);

        $reservation = new Reservation();
        $reservation->setCar($car);
        $reservation->setStartDate(new \DateTimeImmutable('tomorrow'));
        $reservation->setEndDate(new \DateTimeImmutable('tomorrow + 1 day'));
        $reservation->setUser($user);
        $reservation->setCreatedAt(new \DateTimeImmutable('now'));

        $this->entityManager->persist($reservation);
        $this->entityManager->flush();

        return $reservation;
    }
}
