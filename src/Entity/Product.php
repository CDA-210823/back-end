<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['product', 'image', 'cart'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['product', 'image', 'cart'])]
    #[Assert\Length(
        min: 4,
        max: 50,
        minMessage: "Le nom de produit doit être de {{ limit }} caractères minimum",
        maxMessage: "Le nom du produit doit être de {{ limit }} caractères maximum",
    )]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['product', 'image', 'cart'])]
    #[Assert\Length(
        min: 10,
        minMessage: "La description doit contenir {{ limit }} caractères minimum",
    )]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['product', 'image', 'cart'])]
    #[Assert\Positive(message:"Le prix doit être positif")]
    #[Assert\NotBlank(message: "Le champ ne peut pas être vide")]
    private ?float $price = null;

    #[ORM\Column]
    #[Groups(['product', 'image', 'cart'])]
    #[Assert\NotBlank(message: "Le champ ne peut pas être vide")]
    private ?int $stock = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $dateAdd = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: CommandProduct::class)]
    private Collection $commandProducts;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Image::class)]
    #[Groups(['product'])]
    private Collection $imageProduct;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[Groups(['cart'])]
    private ?Category $category = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: CartProduct::class)]
    private Collection $cartProducts;



    public function __construct()
    {
        $this->carts = new ArrayCollection();
        $this->commandProducts = new ArrayCollection();
        $this->imageProduct = new ArrayCollection();
        $this->cartProducts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * @return Collection<int, CommandProduct>
     */
    public function getCommandProducts(): Collection
    {
        return $this->commandProducts;
    }

    public function addCommandProduct(CommandProduct $commandProduct): static
    {
        if (!$this->commandProducts->contains($commandProduct)) {
            $this->commandProducts->add($commandProduct);
            $commandProduct->setProduct($this);
        }

        return $this;
    }

    public function removeCommandProduct(CommandProduct $commandProduct): static
    {
        if ($this->commandProducts->removeElement($commandProduct)) {
            // set the owning side to null (unless already changed)
            if ($commandProduct->getProduct() === $this) {
                $commandProduct->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Image>
     */
    public function getImageProduct(): Collection
    {
        return $this->imageProduct;
    }

    public function addImageProduct(Image $imageProduct): static
    {
        if (!$this->imageProduct->contains($imageProduct)) {
            $this->imageProduct->add($imageProduct);
            $imageProduct->setProduct($this);
        }

        return $this;
    }

    public function removeImageProduct(Image $imageProduct): static
    {
        if ($this->imageProduct->removeElement($imageProduct)) {
            // set the owning side to null (unless already changed)
            if ($imageProduct->getProduct() === $this) {
                $imageProduct->setProduct(null);
            }
        }

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getDateAdd(): ?\DateTimeInterface
    {
        return $this->dateAdd;
    }

    public function setDateAdd(\DateTimeInterface $dateAdd): static
    {
        $this->dateAdd = $dateAdd;

        return $this;
    }

    /**
     * @return Collection<int, CartProduct>
     */
    public function getCartProducts(): Collection
    {
        return $this->cartProducts;
    }

    public function addCartProduct(CartProduct $cartProduct): static
    {
        if (!$this->cartProducts->contains($cartProduct)) {
            $this->cartProducts->add($cartProduct);
            $cartProduct->setProduct($this);
        }

        return $this;
    }

    public function removeCartProduct(CartProduct $cartProduct): static
    {
        if ($this->cartProducts->removeElement($cartProduct)) {
            // set the owning side to null (unless already changed)
            if ($cartProduct->getProduct() === $this) {
                $cartProduct->setProduct(null);
            }
        }

        return $this;
    }


}
