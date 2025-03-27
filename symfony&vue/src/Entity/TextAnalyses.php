<?php

namespace App\Entity;

use App\Repository\TextAnalysesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TextAnalysesRepository::class)]
class TextAnalyses
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'textAnalyses')]
    private ?User $userId = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $processedText = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $originalText = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $taskId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?User
    {
        return $this->userId;
    }

    public function setUserId(?User $user): static
    {
        $this->userId = $user;

        return $this;
    }

    public function getProcessedText(): ?string
    {
        return $this->processedText;
    }

    public function setProcessedText(string $processedText): static
    {
        $this->processedText = $processedText;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getOriginalText(): ?string
    {
        return $this->originalText;
    }

    public function setOriginalText(string $originalText): static
    {
        $this->originalText = $originalText;

        return $this;
    }

    public function getTaskId(): ?string
    {
        return $this->taskId;
    }

    public function setTaskId(?string $taskId): static
    {
        $this->taskId = $taskId;

        return $this;
    }
}
