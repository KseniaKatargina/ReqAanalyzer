<?php

namespace App\Service;

use App\Entity\TextAnalyses;
use App\Entity\User;
use App\Repository\TextAnalysesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class FileUploadService
{
    private TextAnalysesRepository $textAnalysesRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(TextAnalysesRepository $textAnalysesRepository, EntityManagerInterface $entityManager)
    {
        $this->textAnalysesRepository = $textAnalysesRepository;
        $this->entityManager = $entityManager;
    }

    public function uploadFile(UploadedFile $file, User $user, array $nlpResponse): TextAnalyses
    {
        $textAnalysis = new TextAnalyses();
        $textAnalysis->setProcessedText(json_encode($nlpResponse['result'], JSON_UNESCAPED_UNICODE));
        $textAnalysis->setTitle($file->getClientOriginalName());
        $textAnalysis->setOriginalText($nlpResponse['result']['original_text']);
        $textAnalysis->setCreatedAt(new \DateTime());
        $textAnalysis->setUserId($user);

        $this->entityManager->persist($textAnalysis);
        $this->entityManager->flush();

        return $textAnalysis;
    }

    public function getProcessedText(int $id): ?TextAnalyses
    {
        return $this->textAnalysesRepository->find($id);
    }

    public function updateProcessedText(int $id, string $originalText): void
    {
        $textAnalysis = $this->textAnalysesRepository->find($id);
        if (!$textAnalysis) {
            throw new \RuntimeException('Текст не найден', Response::HTTP_NOT_FOUND);
        }

        $textAnalysis->setOriginalText($originalText);
        $this->entityManager->flush();
    }

    public function createPendingAnalysis(mixed $file, User $user, mixed $taskId)
    {
        $textAnalysis = new TextAnalyses();
        $textAnalysis->setUserId($user);
        $textAnalysis->setTitle($file->getClientOriginalName());
        $textAnalysis->setTaskId($taskId);
        $textAnalysis->setOriginalText("");
        $textAnalysis->setCreatedAt(new \DateTime());
        $textAnalysis->setProcessedText("");
        $this->entityManager->persist($textAnalysis);
        $this->entityManager->flush();

        return $textAnalysis;
    }
}