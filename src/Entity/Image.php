<?php

namespace App\Entity;

use App\Repository\ImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
class Image
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['image', 'product'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['image', 'product'])]
    private ?string $name = null;


    #[ORM\ManyToOne(inversedBy: 'imageProduct')]
    #[Groups(['image'])]
    private ?Product $product = null;

    #[ORM\Column(length: 255)]
    #[Groups(['image', 'product'])]
    private ?string $path = null;

    #[ORM\Column(length: 20)]
    #[Groups(['product'])]
    private ?string $ext = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): void
    {
        $this->product = $product;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    public function getExt(): ?string
    {
        return $this->ext;
    }

    public function setExt(string $ext): static
    {
        $this->ext = $ext;

        return $this;
    }

}
