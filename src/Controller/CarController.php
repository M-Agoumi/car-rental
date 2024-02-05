<?php

namespace App\Controller;

use App\Repository\CarRepository;
use App\Service\CarService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
#[IsGranted('ROLE_USER')]
class CarController extends AbstractController
{
    #[Route('/cars', name: 'app_car', methods: ['GET'])]
    public function index(Request $request, CarService $carService): Response
    {
        return $this->json(
            $carService->getCars($request->get('page', 1), $request->get('elementsByPage', 10)),
            200,
            [],
            ['groups' => ['show_car']]
        );
    }

    #[Route('/cars/{id}', name: 'app_car_show', methods: ['GET'])]
    public function show(int $id, CarService $carService): Response
    {
        $car = $carService->getCar($id);
        if (!$car) {
            return $this->json(['error' => 'Car not found'], 404);
        }

        return $this->json($car, 200, [], ['groups' => ['show_car']]);
    }

    #[Route('/cars', name: 'app_car_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request, CarService $carService): Response
    {
        $data = json_decode($request->getContent(), true);
        $errors = $carService->createCar(
            $data['name'] ?? '',
            (int) ($data['seats'] ?? null),
            $data['description'] ?? '',
            (bool) ($data['isPublished'] ?? false)
        );
        if (count($errors) > 0) {
            return $this->json(['errors' => $errors], 400);
        }

        return $this->json(['message' => 'Car created successfully'], 201);
    }

    #[Route('/cars/{id}', name: 'app_car_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(int $id, Request $request, CarService $carService, CarRepository $carRepository): Response
    {
        $car = $carRepository->find($id);
        if (!$car) {
            return $this->json(['error' => 'Car not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $errors = $carService->updateCar($car,
            $data['name'] ?? null,
            (int) ($data['seats'] ?? null),
            $data['description'] ?? null,
            (bool) ($data['isPublished'] ?? false)
        );
        if (count($errors) > 0) {
            return $this->json(['errors' => $errors], 400);
        }

        return $this->json(['message' => 'Car updated successfully']);
    }

    #[Route('/cars/{id}', name: 'app_car_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(int $id, CarService $carService, CarRepository $carRepository): Response
    {
        $car = $carRepository->find($id);
        if (!$car) {
            return $this->json(['error' => 'Car not found'], 404);
        }
        $carService->deleteCar($car);

        return $this->json(['message' => 'Car deleted successfully']);
    }
}
