<?php

namespace App\Entity;

use App\Repository\TransactionLineRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TransactionLineRepository::class)]
class TransactionLine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['transaction:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'transactionLines')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['transaction:read'])]
    private ?Transaction $transaction = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Account $account = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['transaction:read'])]
    private ?int $amount = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(?Transaction $transaction): self
    {
        $this->transaction = $transaction;

        return $this;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): self
    {
        $this->account = $account;

        return $this;
    }

    #[Groups(['transaction:read'])]
    public function getAccountId(): ?int
    {
        return $this->account->getId();
    }

    public function getAmount(): ?float
    {
        if (!$this->account->getIndividualPrice()) {
            return $this->amount / 100;
        }

        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        if ($this->account->getIndividualPrice()) {
            $amount = $amount * 100;
        }
        $this->amount = $amount;

        return $this;
    }
}
