<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use http\Message;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getUser'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['getUser'])]
    #[Assert\Length(
        min:2,
        max: 255,
        minMessage: "L'email doit comporter au minimum {{ limit }} caractères",
        maxMessage: "L'email doit comporter au maximum {{ limit }} caractères"
    )]
    #[Assert\Email(message: "L'email n'est pas valide")]
    #[Assert\Regex(
        '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.(fr|com)$',
        message: "Votre email doit contenir un @ et doit finir par .fr ou .com"
    )]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\Regex(
        '/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/',
        message: "Votre mot de passe doit contenir un caractère majuscule, minuscule et un caractère spécial"
    )]
    private ?string $password = null;

    #[ORM\Column(nullable: true)]
    private ?bool $bannite = null;

    #[ORM\ManyToMany(targetEntity: Address::class, inversedBy: 'users')]
    private Collection $address;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Command::class)]
    private Collection $command;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Opinion::class)]
    private Collection $opinion;

    public function __construct()
    {
        $this->address = new ArrayCollection();
        $this->command = new ArrayCollection();
        $this->opinion = new ArrayCollection();
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

    public function isBannite(): ?bool
    {
        return $this->bannite;
    }

    public function setBannite(?bool $bannite): static
    {
        $this->bannite = $bannite;

        return $this;
    }

    /**
     * @return Collection<int, Address>
     */
    public function getAddress(): Collection
    {
        return $this->address;
    }

    public function addAddress(Address $address): static
    {
        if (!$this->address->contains($address)) {
            $this->address->add($address);
        }

        return $this;
    }

    public function removeAddress(Address $address): static
    {
        $this->address->removeElement($address);

        return $this;
    }

    /**
     * @return Collection<int, Command>
     */
    public function getCommand(): Collection
    {
        return $this->command;
    }

    public function addCommand(Command $command): static
    {
        if (!$this->command->contains($command)) {
            $this->command->add($command);
            $command->setUser($this);
        }

        return $this;
    }

    public function removeCommand(Command $command): static
    {
        if ($this->command->removeElement($command)) {
            // set the owning side to null (unless already changed)
            if ($command->getUser() === $this) {
                $command->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Opinion>
     */
    public function getOpinion(): Collection
    {
        return $this->opinion;
    }

    public function addOpinion(Opinion $opinion): static
    {
        if (!$this->opinion->contains($opinion)) {
            $this->opinion->add($opinion);
            $opinion->setUser($this);
        }

        return $this;
    }

    public function removeOpinion(Opinion $opinion): static
    {
        if ($this->opinion->removeElement($opinion)) {
            // set the owning side to null (unless already changed)
            if ($opinion->getUser() === $this) {
                $opinion->setUser(null);
            }
        }

        return $this;
    }

}
