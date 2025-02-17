<?php

namespace App\Entity;

use App\Entity\Transaction;
use App\Enum\TransactionType;
use App\Repository\LedgerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LedgerRepository::class)]
#[ORM\Table(name: 'ledger')]
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

    #[ORM\Column(type: 'string', length: 50, enumType: TransactionType::class)]
    #[Assert\NotNull(message: 'Transaction type is required.')]
    private ?TransactionType $transactionType = null;

    #[ORM\ManyToOne(targetEntity: Wallet::class, inversedBy: 'ledgers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Wallet must be associated with the ledger entry.')]
    private ?Wallet $wallet = null;

    #[ORM\ManyToOne(targetEntity: Transaction::class, inversedBy: 'ledgerEntries')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Transaction must be associated with the ledger entry.')]
    private ?Transaction $transaction = null;

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

    public function getTransactionType(): ?TransactionType
    {
        return $this->transactionType;
    }

    public function setTransactionType(TransactionType $transactionType): self
    {
        $this->transactionType = $transactionType;
        return $this;
    }

    public function getWallet(): ?Wallet
    {
        return $this->wallet;
    }

    public function setWallet(Wallet $wallet): self
    {
        $this->wallet = $wallet;
        return $this;
    }

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(Transaction $transaction): self
    {
        $this->transaction = $transaction;
        return $this;
    }
}
