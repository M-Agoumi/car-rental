<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class UserController extends AbstractController
{
    #[Route('/sign-up', name: 'app_security_signup', methods: ['POST'])]
    public function index(Request $request, UserService $userService): JsonResponse
    {
        $data = json_decode($request->getContent());
        $user = $userService->signUpUser($data);

        if (is_array($user))
            return $this->json(['errors' => $user], 400);

        return $this->json(['message' => 'you have signed up successfully', 'username' => $user->getUsername()]);
    }
}
