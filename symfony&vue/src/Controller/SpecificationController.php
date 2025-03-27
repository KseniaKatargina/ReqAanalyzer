<?php

namespace App\Controller;

use App\Service\FileService;
use App\Service\JwtService;
use App\Service\SpecificationService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SpecificationController extends AbstractController
{
    private SpecificationService $specificationService;
    private JwtService $jwtService;
    private UserService $userService;
    private FileService $fileService;

    /**
     * @param SpecificationService $specificationService
     * @param JwtService $jwtService
     * @param UserService $userService
     * @param FileService $fileService
     */
    public function __construct(SpecificationService $specificationService, JwtService $jwtService, UserService $userService, FileService $fileService)
    {
        $this->specificationService = $specificationService;
        $this->jwtService = $jwtService;
        $this->userService = $userService;
        $this->fileService = $fileService;
    }


    #[Route('/specifications', name: 'save_specification', methods: ['POST'])]
    public function saveSpecification(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            foreach ($data['specification'] as &$spec) {
                if (empty($spec['content'])) {
                    unset($spec['content']);
                }
            }

            $userId = $this->getUserIdFromToken($request);

            $content = json_encode($data['specification']);
            if ($content === false) {
                return new JsonResponse(['error' => 'Не удалось сериализовать данные'], 400);
            }

            $specification = $this->specificationService->saveSpecification($content, $userId);
            return new JsonResponse(['success' => 'Спецификация сохранена', 'id' => $specification->getId()], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/specifications/{id}', name: 'get_specification', methods: ['GET'])]
    public function getSpecification(int $id): JsonResponse
    {
        try {
            $specification = $this->specificationService->getSpecificationById($id);
            return new JsonResponse([
                'id' => $specification->getId(),
                'title'=> $specification->getTitle(),
                'content' => json_decode($specification->getContent(), true),
                'createdAt' => $specification->getCreatedAt()->format('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        }
    }

    #[Route('/specificationDelete/{id}', name: 'delete_specification', methods: ['DELETE'])]
    public function deleteSpecification(int $id, Request $request): JsonResponse
    {
        try {
            $userId = $this->getUserIdFromToken($request);
            $this->specificationService->deleteSpecification($id, $userId);
            return new JsonResponse(['success' => 'Файл успешно удален'], Response::HTTP_OK);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], $e->getCode());
        }
    }

    #[Route('/specificationEdit/{id}', name: 'edit_specification', methods: ['PUT'])]
    public function editSpecification(int $id, Request $request): JsonResponse
    {
        try {
            $userId = $this->getUserIdFromToken($request);
            $data = json_decode($request->getContent(), true);

            if (!$data) {
                return new JsonResponse(['error' => 'Некорректные данные'], Response::HTTP_BAD_REQUEST);
            }

            $this->specificationService->editSpecification($id, $userId, $data);
            return new JsonResponse(['success' => 'Файл успешно обновлен'], Response::HTTP_OK);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], $e->getCode());
        }
    }

    #[Route('/specificationEdit/{id}', name: 'get_edit_specification', methods: ['GET'])]
    public function getEditSpecification(int $id, Request $request): JsonResponse
    {
        $specification = $this->specificationService->getSpecificationById($id);
        if (!$specification) {
            return new JsonResponse(['error' => 'Specification not found'], 404);
        }
        $responseData = [
            'title' => $specification->getTitle(),
            'content' =>json_decode($specification->getContent(), true)
        ];
        return new JsonResponse($responseData);
    }

    #[Route('/downloadSpec/{id}', name: 'download_spec', methods: ['POST'])]
    public function downloadFile(Request $request): Response
    {
        try {
            $format = $request->query->get('format', 'txt');

            if (!in_array($format, ['txt', 'pdf', 'docx'])) {
                return new JsonResponse(['error' => 'Неподдерживаемый формат'], Response::HTTP_BAD_REQUEST);
            }

            // Получаем тело запроса в виде JSON
            $data = json_decode($request->getContent(), true);
            if (!isset($data['formattedText']) || empty($data['formattedText'])) {
                return new JsonResponse(['error' => 'Отсутствует содержимое файла'], Response::HTTP_BAD_REQUEST);
            }

            // Берем готовый текст из запроса
            $formattedText = $data['formattedText'];

            // Название файла (можно оставить фиксированным или взять из БД)
            $fileName = 'specification';

            // Генерируем файл
            $filePath = $this->fileService->generateFile($fileName, $formattedText, $format);

            if (!file_exists($filePath)) {
                return new JsonResponse(['error' => 'Файл не удалось создать'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $finalFileName = $fileName . '.' . $format;

            return $this->file($filePath, $finalFileName);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    private function getUserIdFromToken(Request $request)
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

        return $user;
    }
}

