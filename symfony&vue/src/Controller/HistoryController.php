<?php

namespace App\Controller;

use App\Service\HistoryService;
use App\Service\JwtService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HistoryController extends AbstractController
{
    private UserService $userService;
    private JwtService $jwtService;
    private HistoryService $historyService;

    public function __construct(UserService $userService, JwtService $jwtService, HistoryService $historyService)
    {
        $this->userService = $userService;
        $this->jwtService = $jwtService;
        $this->historyService = $historyService;
    }

    #[Route('/history/files', name: 'historyFile', methods: ['GET'])]
    public function getUserHistoryFile(Request $request): JsonResponse
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

        $data = $this->historyService->getUserHistoryFile($user->getId());

        return new JsonResponse($data);
    }
    #[Route('/history/specifications', name: 'historySpecification', methods: ['GET'])]
    public function getUserHistorySpecification(Request $request): JsonResponse
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

        $data = $this->historyService->getUserHistorySpecification($user->getId());

        return new JsonResponse($data);
    }
}