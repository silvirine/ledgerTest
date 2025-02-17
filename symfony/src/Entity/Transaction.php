<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[ORM\Table(name: 'transaction')]
#[OA\Schema(
    name: 'Transaction',
    title: 'Transaction',
    description: 'A transaction grouping multiple ledger entries',
    required: ['id', 'reference', 'description', 'transactionDate'],
    properties: [
        new OA\Property(
            property: 'id',
            type: 'integer',
            readOnly: true,
            description: 'Unique identifier of the transaction'
        ),
        new OA\Property(
            property: 'reference',
            type: 'string',
            example: 'TX123456',
            description: 'A unique reference for the transaction'
        ),
        new OA\Property(
            property: 'description',
            type: 'string',
            example: 'Transaction description',
            description: 'A brief description of the transaction'
        ),
        new OA\Property(
            property: 'transactionDate',
            type: 'string',
            format: 'date-time',
            example: '2023-03-01T12:00:00Z',
            description: 'The date and time when the transaction occurred'
        ),
        new OA\Property(
            property: 'ledgerEntries',
            type: 'array',
            items: new OA\Items(ref: 'Ledger'),
            description: 'List of ledger entries associated with the transaction'
        )
    ]
)]
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

    #[ORM\OneToMany(mappedBy: 'transaction', targetEntity: \App\Entity\Ledger::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $ledgerEntries;

    public function __construct()
    {
        $this->ledgerEntries = new ArrayCollection();
    }

    // Getters and setters

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

    public function addLedgerEntry(\App\Entity\Ledger $ledgerEntry): self
    {
        if (!$this->ledgerEntries->contains($ledgerEntry)) {
            $this->ledgerEntries[] = $ledgerEntry;
            $ledgerEntry->setTransaction($this);
        }
        return $this;
    }

    public function removeLedgerEntry(\App\Entity\Ledger $ledgerEntry): self
    {
        if ($this->ledgerEntries->removeElement($ledgerEntry)) {
            // set the owning side to null (unless already changed)
            if ($ledgerEntry->getTransaction() === $this) {
                $ledgerEntry->setTransaction(null);
            }
        }
        return $this;
    }
}
