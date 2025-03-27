<?php

namespace App\Controller;

use App\Repository\TextAnalysesRepository;
use App\Service\FileUploadService;
use App\Service\JwtService;
use App\Service\UserService;
use App\Service\FileProcessingService;
use App\Service\NlpService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FileUploadController extends AbstractController
{
    private UserService $userService;
    private JwtService $jwtService;
    private FileUploadService $fileUploadService;
    private FileProcessingService $fileProcessingService;
    private NlpService $nlpService;
    private TextAnalysesRepository $textAnalysesRepository;

    public function __construct(
        UserService $userService,
        JwtService $jwtService,
        FileUploadService $fileUploadService,
        FileProcessingService $fileProcessingService,
        NlpService $nlpService,
        TextAnalysesRepository $textAnalysesRepository,
    ) {
        $this->userService = $userService;
        $this->jwtService = $jwtService;
        $this->fileUploadService = $fileUploadService;
        $this->fileProcessingService = $fileProcessingService;
        $this->nlpService = $nlpService;
        $this->textAnalysesRepository = $textAnalysesRepository;
    }

    #[Route('/task/complete', name: 'task_complete', methods: ['POST'])]
    public function taskComplete(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['task_id']) || !isset($data['result'])) {
            return new JsonResponse(['error' => 'Неверные данные'], Response::HTTP_BAD_REQUEST);
        }

        $taskId = $data['task_id'];
        $result = $data['result'];

        // Находим запись в базе данных по task_id
        $textAnalysis = $this->textAnalysesRepository->findOneBy(['taskId' => $taskId]);

        if (!$textAnalysis) {
            return new JsonResponse(['error' => 'Задача не найдена'], Response::HTTP_NOT_FOUND);
        }

        $textAnalysis->setProcessedText(json_encode($result, JSON_UNESCAPED_UNICODE));
        $textAnalysis->setOriginalText($result['original_text']);
        $textAnalysis->setCreatedAt(new \DateTime());

        $entityManager->flush();

        return new JsonResponse([
            'status' => 'success',
            'text_id' => $textAnalysis->getId(),
        ], Response::HTTP_OK);
    }
    #[Route('/text/{id}', name: 'get_processed_text', methods: ['GET'])]
    public function getProcessedText(int $id): JsonResponse
    {
        $textAnalysis = $this->fileUploadService->getProcessedText($id);

        if (!$textAnalysis) {
            return new JsonResponse(['error' => 'Текст не найден'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'title' => $textAnalysis->getTitle(),
            'processed_text' => mb_convert_encoding($textAnalysis->getProcessedText(), "UTF-8", "auto"),
            'original_text' => $textAnalysis->getOriginalText(),
            'created_at' => $textAnalysis->getCreatedAt()->format('d.m.Y H:i:s'),
        ], Response::HTTP_OK, (array)JSON_UNESCAPED_UNICODE);
    }

    #[Route('/text/{id}', name: 'update_processed_text', methods: ['PUT'])]
    public function updateProcessedText(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['original_text'])) {
            return new JsonResponse(['error' => 'Исходный текст не передан'], Response::HTTP_BAD_REQUEST);
        }

        $this->fileUploadService->updateProcessedText($id, $data['original_text']);

        return new JsonResponse(['message' => 'Текст успешно обновлен'], Response::HTTP_OK);
    }

    #[Route('/upload', name: 'file_upload', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
        $token = $request->headers->get('Authorization');
        if (!$token || !str_starts_with($token, 'Bearer ')) {
            return new JsonResponse(['error' => 'JWT токен отсутствует'], Response::HTTP_UNAUTHORIZED);
        }

        $token = str_replace('Bearer ', '', $token);
        try {
            $decoded = $this->jwtService->decodeToken($token);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Ошибка декодирования токена'], Response::HTTP_UNAUTHORIZED);
        }

        $user = $this->userService->checkUser($decoded->email);
        if (!$user) {
            return new JsonResponse(['error' => 'Пользователь не найден'], Response::HTTP_UNAUTHORIZED);
        }

        $file = $request->files->get('file');
        if (!$file) {
            return new JsonResponse(['error' => 'Файл не выбран'], Response::HTTP_BAD_REQUEST);
        }

        $allowedMimeTypes = [
            'text/plain',
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/msword'
        ];
        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            return new JsonResponse(['error' => 'Неверный формат файла'], Response::HTTP_BAD_REQUEST);
        }

        $text = $this->fileProcessingService->convertFileToText($file);
        if (!$text) {
            return new JsonResponse(['error' => 'Ошибка обработки файла'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Отправляем задачу в NLP-сервис и получаем task_id
        $nlpResponse = $this->nlpService->sendToNLPService(['text' => $text], $token);
        if (isset($nlpResponse['error'])) {
            return new JsonResponse(['error' => 'Ошибка обработки текста'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        try {
            $textAnalysis = $this->fileUploadService->createPendingAnalysis($file, $user, $nlpResponse['task_id']);
        } catch (\Exception $e) {
            error_log('Ошибка сохранения задачи: ' . $e->getMessage());
            return new JsonResponse(['error' => 'Ошибка сохранения задачи'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Возвращаем task_id фронтенду
        return new JsonResponse([
            'status' => 'pending',
            'task_id' => $nlpResponse['task_id'],
            'text_id' => $textAnalysis->getId(),
        ], Response::HTTP_ACCEPTED);
    }
//    #[Route('/upload', name: 'file_upload', methods: ['POST'])]
//    public function upload(Request $request): JsonResponse
//    {
//        $token = $request->headers->get('Authorization');
//        if (!$token || !str_starts_with($token, 'Bearer ')) {
//            return new JsonResponse(['error' => 'JWT токен отсутствует'], Response::HTTP_UNAUTHORIZED);
//        }
//
//        $token = str_replace('Bearer ', '', $token);
//        try {
//            $decoded = $this->jwtService->decodeToken($token);
//        } catch (\Exception $e) {
//            return new JsonResponse(['error' => 'Ошибка декодирования токена'], Response::HTTP_UNAUTHORIZED);
//        }
//
//        $user = $this->userService->checkUser($decoded->email);
//        if (!$user) {
//            return new JsonResponse(['error' => 'Пользователь не найден'], Response::HTTP_UNAUTHORIZED);
//        }
//
//        $file = $request->files->get('file');
//        if (!$file) {
//            return new JsonResponse(['error' => 'Файл не выбран'], Response::HTTP_BAD_REQUEST);
//        }
//
//        $allowedMimeTypes = [
//            'text/plain',
//            'application/pdf',
//            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
//            'application/msword'
//        ];
//        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
//            return new JsonResponse(['error' => 'Неверный формат файла'], Response::HTTP_BAD_REQUEST);
//        }
//
//        $text = $this->fileProcessingService->convertFileToText($file);
//        if (!$text) {
//            return new JsonResponse(['error' => 'Ошибка обработки файла'], Response::HTTP_INTERNAL_SERVER_ERROR);
//        }
//
//        $nlpResponse = $this->nlpService->sendToNLPService(['text' => $text], $token);
//        if (isset($nlpResponse['error'])) {
//            return new JsonResponse(['error' => 'Ошибка обработки текста'], Response::HTTP_INTERNAL_SERVER_ERROR);
//        }
//
//        try {
//            $textAnalysis = $this->fileUploadService->uploadFile($file, $user, $nlpResponse);
//        } catch (\Exception $e) {
//            return new JsonResponse(['error' => 'Ошибка сохранения анализа'], Response::HTTP_INTERNAL_SERVER_ERROR);
//        }
//
//        return new JsonResponse([
//            'status' => 'success',
//            'message' => 'Текст обработан и сохранен',
//            'text_id' => $textAnalysis->getId(),
//        ], Response::HTTP_OK);
//    }
}
