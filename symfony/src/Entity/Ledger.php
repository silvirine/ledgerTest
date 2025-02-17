<?php

namespace App\Entity;

use App\Repository\LedgerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

#[ORM\Entity(repositoryClass: LedgerRepository::class)]
#[ORM\Table(name: 'ledger')]
#[OA\Schema(
    name: 'Ledger',
    title: 'Ledger Entry',
    description: 'A ledger entry associated with a wallet and a transaction',
    required: ['id', 'amount', 'description', 'transactionDate', 'transactionType', 'wallet', 'transaction'],
    properties: [
        new OA\Property(
            property: 'id',
            type: 'integer',
            readOnly: true,
            description: 'Unique identifier of the ledger entry'
        ),
        new OA\Property(
            property: 'amount',
            type: 'number',
            format: 'float',
            example: 50.00,
            description: 'The monetary amount for this ledger entry'
        ),
        new OA\Property(
            property: 'description',
            type: 'string',
            example: 'Payment received',
            description: 'A brief description of the ledger entry'
        ),
        new OA\Property(
            property: 'transactionDate',
            type: 'string',
            format: 'date-time',
            example: '2023-03-01T12:00:00Z',
            description: 'The date and time when the transaction occurred'
        ),
        new OA\Property(
            property: 'transactionType',
            type: 'string',
            enum: ['credit', 'debit'],
            example: 'credit',
            description: 'The type of transaction (credit or debit)'
        ),
        new OA\Property(
            property: 'wallet',
            ref: '#/components/schemas/Wallet',
            description: 'The wallet associated with this ledger entry'
        ),
        new OA\Property(
            property: 'transaction',
            ref: '#/components/schemas/Transaction',
            description: 'The transaction grouping this ledger entry'
        )
    ]
)]
class Ledger
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\NotNull(message: 'Amount must be provided.')]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'Amount cannot be negative.')]
    private ?string $amount = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Description cannot be blank.')]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull(message: 'Transaction date is required.')]
    private ?\DateTimeInterface $transactionDate = null;

    #[ORM\Column(type: 'string', length: 50)]
    #[Assert\NotBlank(message: 'Transaction type is required.')]
    private ?string $transactionType = null;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Wallet::class, inversedBy: 'ledgers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Wallet must be associated with the ledger entry.')]
    private ?\App\Entity\Wallet $wallet = null;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Transaction::class, inversedBy: 'ledgerEntries')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Transaction must be associated with the ledger entry.')]
    private ?\App\Entity\Transaction $transaction = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;
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

    public function getTransactionType(): ?string
    {
        return $this->transactionType;
    }

    public function setTransactionType(string $transactionType): self
    {
        $this->transactionType = $transactionType;
        return $this;
    }

    public function getWallet(): ?\App\Entity\Wallet
    {
        return $this->wallet;
    }

    public function setWallet(?\App\Entity\Wallet $wallet): self
    {
        $this->wallet = $wallet;
        return $this;
    }

    public function getTransaction(): ?\App\Entity\Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(?\App\Entity\Transaction $transaction): self
    {
        $this->transaction = $transaction;
        return $this;
    }
}
