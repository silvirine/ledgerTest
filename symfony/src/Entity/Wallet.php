<?php

namespace App\Entity;

use App\Repository\WalletRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

#[ORM\Entity(repositoryClass: WalletRepository::class)]
#[ORM\Table(name: 'wallet', indexes: [new ORM\Index(name: 'idx_wallet_name', columns: ['name'])])]
#[OA\Schema(
    name: 'Wallet',
    title: 'Wallet',
    description: 'A wallet holding a balance.',
    required: ['id', 'name', 'balance'],
    properties: [
        new OA\Property(
            property: 'id',
            type: 'integer',
            readOnly: true,
            description: 'Unique identifier of the wallet'
        ),
        new OA\Property(
            property: 'name',
            type: 'string',
            example: 'My Wallet',
            description: 'The name of the wallet'
        ),
        new OA\Property(
            property: 'balance',
            type: 'number',
            format: 'float',
            example: 100.00,
            description: 'The current balance of the wallet'
        )
    ]
)]
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
}
