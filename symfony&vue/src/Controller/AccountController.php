<?php

namespace App\Controller;

use App\Service\JwtService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    private UserService $userService;
    private JwtService $jwtService;

    public function __construct(UserService $userService, JwtService $jwtService)
    {
        $this->userService = $userService;
        $this->jwtService = $jwtService;
    }

    #[Route('/account', name: 'app_account', methods: ['GET', 'PUT'])]
    public function account(Request $request): JsonResponse
    {
        $token = $request->headers->get('Authorization');
        if (!$token || !str_starts_with($token, 'Bearer ')) {
            return new JsonResponse(['error' => 'JWT токен отсутствует'], Response::HTTP_UNAUTHORIZED);
        }

        $token = str_replace('Bearer ', '', $token);

        try {
            $decoded = $this->jwtService->decodeToken($token);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Неверный JWT токен'], Response::HTTP_UNAUTHORIZED);
        }

        $user = $this->userService->checkUser($decoded->email);
        if (!$user) {
            return new JsonResponse(['error' => 'Пользователь не найден'], Response::HTTP_UNAUTHORIZED);
        }

        if ($request->getMethod() === 'GET') {
            return new JsonResponse(['email' => $user->getEmail()]);
        }

        $data = json_decode($request->getContent(), true);

        try {
            $this->userService->updateUser($user, $data);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $newToken = $this->jwtService->createToken($user->getEmail());

        return new JsonResponse(['token' => $newToken, 'message' => 'Данные успешно обновлены'], Response::HTTP_OK);
    }
}