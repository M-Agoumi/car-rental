<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Service\CarService;
use App\Service\ReservationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
#[IsGranted('ROLE_USER')]
class ReservationController extends AbstractController
{
    #[Route('/reservations', name: 'app_create_reservation', methods: ['POST'])]
    public function createReservation(
        Request $request,
        ReservationService $reservationService,
        CarService $carService
    ): Response {
        // Get the data from the request
        $data = json_decode($request->getContent(), true);

        // Validate the car
        $car = $carService->getCar($data['carId'] ?? 0);
        if (!$car || !$car->getIsPublished()) {
            return $this->json(['error' => 'Car not found'], 404);
        }

        // Validate start date
        $startDate = $reservationService->validateDate($data['startDate'] ?? '');
        if (null === $startDate) {
            return $this->json(['error' => 'Start date is not valid'], 400);
        }

        // Validate end date
        $endDate = $reservationService->validateDate($data['endDate'] ?? '');
        if (null === $endDate) {
            return $this->json(['error' => 'End date is not valid'], 400);
        }

        // call the service to create the reservation
        $errors = $reservationService->reserve($car, $startDate, $endDate, $this->getUser());
        if (count($errors) > 0) {
            return $this->json(['errors' => $errors], 400);
        }

        return $this->json(['message' => 'Reservation created successfully'], 201);
    }

    #[Route('/users/{id}/reservations', name: 'app_list_reservations', methods: ['GET'])]
    public function listReservations(int $id, Request $request, ReservationService $reservationService): Response
    {
        if ($id !== $this->getUser()->getId()) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        return $this->json(
            $reservationService->getReservations(
                $this->getUser(),
                $request->get('page', 1),
                $request->get('elementsByPage', 10)
            ),
            200,
            [],
            ['groups' => ['show_reservation', 'show_car']]
        );
    }

    #[Route('/reservations/{reservation}', name: 'app_edit_reservation', methods: ['PUT'])]
    public function updateReservation(
        Reservation $reservation,
        Request $request,
        ReservationService $reservationService,
        CarService $carService
    ): Response {
        if ($reservation->getUser() !== $this->getUser()) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }
        $data = json_decode($request->getContent(), true);

        $startDate = $data['startDate'] ?? false ? $reservationService->validateDate($data['startDate']) : $reservation->getStartDate();
        if (null === $startDate) {
            return $this->json(['error' => 'Start date is not valid'], 400);
        }

        $endDate = $data['endDate'] ?? false ? $reservationService->validateDate($data['endDate']) : $reservation->getEndDate();
        if (null === $endDate) {
            return $this->json(['error' => 'End date is not valid'], 400);
        }

        $car = $reservation->getCar();
        if ($data['carId'] ?? false) {
            $car = $carService->getCar($data['carId']);
            if (!$car || !$car->getIsPublished()) {
                return $this->json(['error' => 'Car not found'], 404);
            }
        }

        $errors = $reservationService->updateReservation($reservation, $car, $startDate, $endDate);
        if (count($errors) > 0) {
            return $this->json(['errors' => $errors], 400);
        }

        return $this->json(['message' => 'Reservation updated successfully']);
    }

    #[Route('/reservations/{reservation}', name: 'app_delete_reservation', methods: ['DELETE'])]
    public function deleteReservation(Reservation $reservation, ReservationService $reservationService): Response
    {
        if ($reservation->getUser() !== $this->getUser()) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $reservationService->deleteReservation($reservation);

        return $this->json(['message' => 'Reservation deleted successfully']);
    }
}
