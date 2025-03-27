<?php

namespace App\Service;

use App\Entity\Specification;
use App\Entity\TextAnalyses;
use App\Entity\User;
use App\Repository\SpecificationRepository;
use App\Repository\TextAnalysesRepository;

class HistoryService
{
    private TextAnalysesRepository $textAnalysesRepository;
    private SpecificationRepository $specificationRepository;

    /**
     * @param TextAnalysesRepository $textAnalysesRepository
     * @param SpecificationRepository $specificationRepository
     */
    public function __construct(TextAnalysesRepository $textAnalysesRepository, SpecificationRepository $specificationRepository)
    {
        $this->textAnalysesRepository = $textAnalysesRepository;
        $this->specificationRepository = $specificationRepository;
    }


    public function getUserHistoryFile(int $userId): array
    {
        $files = $this->textAnalysesRepository->findByUserId($userId);

        return array_map(function (TextAnalyses $file) {
            return [
                'id' => $file->getId(),
                'title' => $file->getTitle(),
                'description' => $file->getDescription(),
                'createdAt' => $file->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $files);
    }

    public function getUserHistorySpecification(?int $userId)
    {
        $specifications = $this->specificationRepository->findByUserId($userId);

        return array_map(function (Specification $specification) {
            return [
                'id' => $specification->getId(),
                'title' => $specification->getTitle(),
                'content' => $specification->getContent(),
                'createdAt' => $specification->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $specifications);
    }
}