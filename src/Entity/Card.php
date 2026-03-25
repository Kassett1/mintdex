<?php

namespace App\Entity;

use App\Repository\CardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CardRepository::class)]
class Card
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(length: 255)]
    private ?string $localId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\ManyToOne(inversedBy: 'cards')]
    private ?Set $set = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $illustrator = null;

    /**
     * @var Collection<int, UserCard>
     */
    #[ORM\OneToMany(targetEntity: UserCard::class, mappedBy: 'card')]
    private Collection $userCards;

    /**
     * @var Collection<int, WishlistCard>
     */
    #[ORM\OneToMany(targetEntity: WishlistCard::class, mappedBy: 'card')]
    private Collection $wishlistCards;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cardmarketId = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $price = null;

    public function __construct()
    {
        $this->userCards = new ArrayCollection();
        $this->wishlistCards = new ArrayCollection();
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getLocalId(): ?string
    {
        return $this->localId;
    }

    public function setLocalId(string $localId): static
    {
        $this->localId = $localId;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getset(): ?Set
    {
        return $this->set;
    }

    public function setset(?Set $set): static
    {
        $this->set = $set;

        return $this;
    }

    public function getIllustrator(): ?string
    {
        return $this->illustrator;
    }

    public function setIllustrator(string $illustrator): static
    {
        $this->illustrator = $illustrator;

        return $this;
    }

    /**
     * @return Collection<int, UserCard>
     */
    public function getUserCards(): Collection
    {
        return $this->userCards;
    }

    public function addUserCard(UserCard $userCard): static
    {
        if (!$this->userCards->contains($userCard)) {
            $this->userCards->add($userCard);
            $userCard->setCard($this);
        }

        return $this;
    }

    public function removeUserCard(UserCard $userCard): static
    {
        if ($this->userCards->removeElement($userCard)) {
            // set the owning side to null (unless already changed)
            if ($userCard->getCard() === $this) {
                $userCard->setCard(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, WishlistCard>
     */
    public function getWishlistCards(): Collection
    {
        return $this->wishlistCards;
    }

    public function addWishlistCard(WishlistCard $wishlistCard): static
    {
        if (!$this->wishlistCards->contains($wishlistCard)) {
            $this->wishlistCards->add($wishlistCard);
            $wishlistCard->setCard($this);
        }

        return $this;
    }

    public function removeWishlistCard(WishlistCard $wishlistCard): static
    {
        if ($this->wishlistCards->removeElement($wishlistCard)) {
            // set the owning side to null (unless already changed)
            if ($wishlistCard->getCard() === $this) {
                $wishlistCard->setCard(null);
            }
        }

        return $this;
    }

    public function getCardmarketId(): ?string
    {
        return $this->cardmarketId;
    }

    public function setCardmarketId(string $cardmarketId): static
    {
        $this->cardmarketId = $cardmarketId;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): static
    {
        $this->price = $price;

        return $this;
    }
}
