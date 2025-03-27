<?php

namespace App\Controller;

use App\Service\FileService;
use App\Service\JwtService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FileController extends AbstractController
{
    private UserService $userService;
    private JwtService $jwtService;
    private FileService $fileService;

    public function __construct(UserService $userService, JwtService $jwtService, FileService $fileService)
    {
        $this->userService = $userService;
        $this->jwtService = $jwtService;
        $this->fileService = $fileService;
    }

    #[Route('/fileDelete/{id}', name: 'delete_history', methods: ['DELETE'])]
    public function deleteFile(int $id, Request $request): JsonResponse
    {
        try {
            $userId = $this->getUserIdFromToken($request);
            $this->fileService->deleteFile($id, $userId);
            return new JsonResponse(['success' => 'Файл успешно удален'], Response::HTTP_OK);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], $e->getCode());
        }
    }

    #[Route('/fileEdit/{id}', name: 'edit_history', methods: ['PUT'])]
    public function editFile(int $id, Request $request): JsonResponse
    {
        try {
            $userId = $this->getUserIdFromToken($request);
            $data = json_decode($request->getContent(), true);

            if (!$data) {
                return new JsonResponse(['error' => 'Некорректные данные'], Response::HTTP_BAD_REQUEST);
            }

            $this->fileService->editFile($id, $userId, $data);
            return new JsonResponse(['success' => 'Файл успешно обновлен'], Response::HTTP_OK);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], $e->getCode());
        }
    }
    #[Route('/download/{id}', name: 'download_file', methods: ['GET'])]
    public function downloadFile(int $id, Request $request): Response
    {
        try {
            $userId = $this->getUserIdFromToken($request);

            $format = $request->query->get('format', 'txt');

            if (!in_array($format, ['txt', 'pdf', 'docx'])) {
                return new JsonResponse(['error' => 'Неподдерживаемый формат'], Response::HTTP_BAD_REQUEST);
            }

            $fileData = $this->fileService->getFile($id, $userId);
            if (!$fileData) {
                return new JsonResponse(['error' => 'Файл не найден'], Response::HTTP_NOT_FOUND);
            }

            $fileContent = $fileData['original_text'];
            $fileName = $fileData['title'] ?? 'Без названия';

            // Генерация файла
            $filePath = $this->fileService->generateFile($fileName, $fileContent, $format);

            if (!file_exists($filePath)) {
                return new JsonResponse(['error' => 'Файл не удалось создать'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $finalFileName = $fileName . '.' . $format; // Добавляем только выбранное расширение

            return $this->file($filePath, $finalFileName);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    private function getUserIdFromToken(Request $request): int
    {
        $token = $request->headers->get('Authorization');
        if (!$token || !str_starts_with($token, 'Bearer ')) {
            throw new \RuntimeException('JWT токен отсутствует', Response::HTTP_UNAUTHORIZED);
        }

        $token = str_replace('Bearer ', '', $token);

        try {
            $decoded = $this->jwtService->decodeToken($token);
        } catch (\Exception $e) {
            throw new \RuntimeException('Неверный JWT токен', Response::HTTP_UNAUTHORIZED);
        }

        $user = $this->userService->checkUser($decoded->email);
        if (!$user) {
            throw new \RuntimeException('Пользователь не найден', Response::HTTP_UNAUTHORIZED);
        }

        return $user->getId();
    }
}