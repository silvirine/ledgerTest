<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[ORM\Table(name: 'transaction')]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    #[Assert\NotBlank(message: 'Transaction reference cannot be blank.')]
    private ?string $reference = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Description cannot be blank.')]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull(message: 'Transaction date is required.')]
    private ?\DateTimeInterface $transactionDate = null;

    #[ORM\OneToMany(mappedBy: 'transaction', targetEntity: Ledger::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $ledgerEntries;

    public function __construct()
    {
        $this->ledgerEntries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getTransactionDate(): ?\DateTimeInterface
    {
        return $this->transactionDate;
    }

    public function setTransactionDate(\DateTimeInterface $transactionDate): self
    {
        $this->transactionDate = $transactionDate;
        return $this;
    }

    /**
     * @return Collection|Ledger[]
     */
    public function getLedgerEntries(): Collection
    {
        return $this->ledgerEntries;
    }

    public function addLedgerEntry(Ledger $ledger): self
    {
        if (!$this->ledgerEntries->contains($ledger)) {
            $this->ledgerEntries[] = $ledger;
            $ledger->setTransaction($this);
        }
        return $this;
    }

    public function removeLedgerEntry(Ledger $ledger): self
    {
        if ($this->ledgerEntries->removeElement($ledger)) {
            // set the owning side to null (unless already changed)
            if ($ledger->getTransaction() === $this) {
                $ledger->setTransaction(null);
            }
        }
        return $this;
    }
}
