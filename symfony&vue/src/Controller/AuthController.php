<?php

namespace App\Controller;

use App\Service\UserService;
use App\Service\JwtService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    private UserService $userService;
    private JwtService $jwtService;

    public function __construct(UserService $userService, JwtService $jwtService)
    {
        $this->userService = $userService;
        $this->jwtService = $jwtService;
    }

    #[Route('/register', name: 'app_registration', methods: ['POST', 'OPTIONS'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data['email']) || empty($data['password'])) {
            return new JsonResponse(['error' => 'Все поля должны быть заполнены'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $response = $this->userService->registerUser($data['email'], $data['password']);
        return new JsonResponse($response, isset($response['error']) ? JsonResponse::HTTP_BAD_REQUEST : JsonResponse::HTTP_CREATED);
    }

    #[Route('/login', name: 'app_login', methods: ['POST', 'OPTIONS'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data['email']) || empty($data['password'])) {
            return new JsonResponse(['error' => 'Все поля должны быть заполнены'], JsonResponse::HTTP_BAD_REQUEST);
        }

        if (!$this->userService->validateUser($data['email'], $data['password'])) {
            return new JsonResponse(['error' => 'Неверные учетные данные'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $token = $this->jwtService->createToken($data['email']);
        return new JsonResponse(['token' => $token, 'message' => 'Авторизация успешна'], JsonResponse::HTTP_OK);
    }

    #[Route('/check-auth', name: 'check_auth', methods: ['GET'])]
    public function checkAuth(Request $request): JsonResponse
    {
        $authorizationHeader = $request->headers->get('Authorization');

        if (!$authorizationHeader) {
            return new JsonResponse(['authenticated' => false], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $jwt = str_replace('Bearer ', '', $authorizationHeader);

        try {
            $decoded = $this->jwtService->decodeToken($jwt);
            return new JsonResponse(['authenticated' => true, 'email' => $decoded->email]);
        } catch (\Exception $e) {
            return new JsonResponse(['authenticated' => false], JsonResponse::HTTP_UNAUTHORIZED);
        }
    }
}