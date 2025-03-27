<?php

namespace App\Service;

use App\Entity\Specification;
use App\Repository\SpecificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class SpecificationService
{
    private SpecificationRepository $specificationRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(SpecificationRepository $specificationRepository, EntityManagerInterface $entityManager)
    {
        $this->specificationRepository = $specificationRepository;
        $this->entityManager = $entityManager;
    }

    public function saveSpecification(string $content, $userId): Specification
    {
        $specification = new Specification();
        $specification->setUserId($userId);
        $specification->setContent($content);
        $specification->setTitle("Спецификация" . $specification->getId());
        $this->entityManager->persist($specification);
        $this->entityManager->flush();

        return $specification;
    }

    public function getSpecificationById(int $id): Specification
    {
        $specification = $this->specificationRepository->find($id);

        if (!$specification) {
            throw new \RuntimeException('Спецификация не найдена');
        }

        return $specification;
    }

    public function deleteSpecification(int $id, \App\Entity\User $userId)
    {
        $specification = $this->specificationRepository->find($id);
        if (!$specification) {
            throw new \RuntimeException('Файл не найден', Response::HTTP_NOT_FOUND);
        }

        if ($specification->getUserId()->getId() !== $userId->getId()) {
            throw new \RuntimeException('Этот файл не принадлежит текущему пользователю', Response::HTTP_FORBIDDEN);
        }

        $this->entityManager->remove($specification);
        $this->entityManager->flush();
    }

    public function editSpecification(int $id, \App\Entity\User $userId, mixed $data)
    {

        $specification = $this->specificationRepository->find($id);
        if (!$specification) {
            throw new \RuntimeException('Файл не найден', Response::HTTP_NOT_FOUND);
        }

        if ($specification->getUserId()->getId() !== $userId->getId()) {
            throw new \RuntimeException('Этот файл не принадлежит текущему пользователю', Response::HTTP_FORBIDDEN);
        }

        if (isset($data['title'])) {
            $specification->setTitle($data['title']);
        }

        if (isset($data['specification'])) {
            $specification->setContent(json_encode($data['specification']));
        }
        $this->entityManager->flush();
    }

    public function getSpec(int $id, int $userId): ?array
    {
        $specification = $this->specificationRepository->findOneBy(['id' => $id, 'userId' => $userId]);

        if (!$specification) {
            return null;
        }

        return [
            'title' => $specification->getTitle(),
            'content' => $specification->getContent(),
            'created_at' => $specification->getCreatedAt(),
        ];
    }

}

