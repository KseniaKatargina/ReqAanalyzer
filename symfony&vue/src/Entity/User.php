<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToMany(mappedBy: 'userId', targetEntity: TextAnalyses::class)]
    private Collection $textAnalyses;

    #[ORM\OneToMany(mappedBy: 'userId', targetEntity: Specification::class)]
    private Collection $content;

    #[ORM\OneToMany(mappedBy: 'userId', targetEntity: Specification::class)]
    private Collection $specification;

    public function __construct()
    {
        $this->textAnalyses = new ArrayCollection();
        $this->content = new ArrayCollection();
        $this->specification = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, TextAnalyses>
     */
    public function getTextAnalyses(): Collection
    {
        return $this->textAnalyses;
    }

    public function addTextAnalysis(TextAnalyses $textAnalysis): static
    {
        if (!$this->textAnalyses->contains($textAnalysis)) {
            $this->textAnalyses->add($textAnalysis);
            $textAnalysis->setUserId($this);
        }

        return $this;
    }

    public function removeTextAnalysis(TextAnalyses $textAnalysis): static
    {
        if ($this->textAnalyses->removeElement($textAnalysis)) {
            // set the owning side to null (unless already changed)
            if ($textAnalysis->getUserId() === $this) {
                $textAnalysis->setUserId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Specification>
     */
    public function getContent(): Collection
    {
        return $this->content;
    }

    public function addContent(Specification $content): static
    {
        if (!$this->content->contains($content)) {
            $this->content->add($content);
            $content->setUserId($this);
        }

        return $this;
    }

    public function removeContent(Specification $content): static
    {
        if ($this->content->removeElement($content)) {
            // set the owning side to null (unless already changed)
            if ($content->getUserId() === $this) {
                $content->setUserId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Specification>
     */
    public function getSpecification(): Collection
    {
        return $this->specification;
    }

    public function addSpecification(Specification $specification): static
    {
        if (!$this->specification->contains($specification)) {
            $this->specification->add($specification);
            $specification->setUserId($this);
        }

        return $this;
    }

    public function removeSpecification(Specification $specification): static
    {
        if ($this->specification->removeElement($specification)) {
            // set the owning side to null (unless already changed)
            if ($specification->getUserId() === $this) {
                $specification->setUserId(null);
            }
        }

        return $this;
    }
}
