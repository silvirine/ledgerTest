<?php

namespace App\Entity;

use App\Repository\WalletRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: WalletRepository::class)]
#[ORM\Table(name: 'wallet', indexes: [new ORM\Index(name: 'idx_wallet_name', columns: ['name'])])]
class Wallet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'Wallet name cannot be blank.')]
    private ?string $name = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\NotNull(message: 'Wallet balance must be provided.')]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'Wallet balance cannot be negative.')]
    private ?string $balance = null;

    #[ORM\OneToMany(mappedBy: 'wallet', targetEntity: \App\Entity\Ledger::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $ledgers;


    public function __construct()
    {
        $this->ledgers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getBalance(): ?string
    {
        return $this->balance;
    }

    public function setBalance(string $balance): self
    {
        $this->balance = $balance;
        return $this;
    }

    /**
     * @return Collection<int, Ledger>
     */
    public function getLedgers(): Collection
    {
        return $this->ledgers;
    }

    public function addLedger(Ledger $ledger): static
    {
        if (!$this->ledgers->contains($ledger)) {
            $this->ledgers->add($ledger);
            $ledger->setWallet($this);
        }

        return $this;
    }

    public function removeLedger(Ledger $ledger): static
    {
        if ($this->ledgers->removeElement($ledger)) {
            // set the owning side to null (unless already changed)
            if ($ledger->getWallet() === $this) {
                $ledger->setWallet(null);
            }
        }

        return $this;
    }
}
