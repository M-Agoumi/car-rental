<?php

namespace App\Service;

use App\Entity\Car;
use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface as User;

readonly class ReservationService
{
    public function __construct(
        private EntityManagerInterface $manager,
        private ReservationRepository $reservationRepository
    ) {
    }

    public function reserve(Car $car, \DateTimeInterface $startDate, \DateTimeInterface $endDate, User $user): array
    {
        $reservation = new Reservation();

        // check if the dates are in the past
        $error = $this->areDateInThePast($startDate, $endDate);
        if ($error) {
            return $error;
        }

        // check if the car is already reserved for the given dates
        $existingReservation = $this->reservationRepository->findExistingReservation($car, $startDate, $endDate);
        if (count($existingReservation) > 0) {
            return ['carId' => 'Car is not available for the given dates'];
        }

        $reservation->setCar($car);
        $reservation->setStartDate($startDate);
        $reservation->setEndDate($endDate);
        $reservation->setUser($user);
        $reservation->setCreatedAt(new \DateTimeImmutable());

        $this->manager->persist($reservation);
        $this->manager->flush();

        return [];
    }

    public function validateDate(string $date): ?\DateTimeInterface
    {
        $date = \DateTime::createFromFormat('Y-m-d', $date);
        if (false === $date) {
            return null;
        }
        $date->setTime(12, 0, 0);

        return $date;
    }

    public function getReservations(?User $getUser, int $page, int $elementByPage): array
    {
        return $this->reservationRepository->findBy(['user' => $getUser], [], $elementByPage, ($page - 1) * $elementByPage);
    }

    public function updateReservation(Reservation $reservation, Car $car, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        // check if the dates are in the past
        $error = $this->areDateInThePast($startDate, $endDate);
        if ($error) {
            return $error;
        }

        // check if the car is already reserved for the given dates
        $existingReservation = $this->reservationRepository->findExistingReservation($car, $startDate, $endDate, $reservation);
        if (count($existingReservation) > 0) {
            return ['carId' => 'Car is not available for the given dates'];
        }

        $reservation->setCar($car);
        $reservation->setStartDate($startDate);
        $reservation->setEndDate($endDate);
        $reservation->setUpdatedAt(new \DateTimeImmutable());

        $this->manager->persist($reservation);
        $this->manager->flush();

        return [];
    }

    private function areDateInThePast(\DateTimeInterface $startDate, \DateTimeInterface $endDate): ?array
    {
        // check if the start date is in the past
        if ($startDate < new \DateTimeImmutable('today')) {
            return ['startDate' => 'Start date cannot be in the past'];
        }

        // check if the end date is before the start date
        if ($endDate < $startDate) {
            return ['endDate' => 'End date cannot be before the start date'];
        }

        return null;
    }

    public function deleteReservation(Reservation $reservation)
    {
        $this->manager->remove($reservation);
        $this->manager->flush();
    }
}
