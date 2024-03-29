<?php

namespace App\Entity;

use App\Repository\AccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AccountRepository::class)]
class Account
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['account:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'accounts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    #[Groups(['account:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['account:read'])]
    private ?string $color = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Groups(['account:read'])]
    private ?int $individual_price = null;

    #[ORM\OneToMany(mappedBy: 'account', targetEntity: Contract::class)]
    private Collection $contracts;

    #[ORM\OneToMany(mappedBy: 'account', targetEntity: TransactionLine::class, orphanRemoval: true)]
    private Collection $transactions;

    public function __construct()
    {
        $this->contracts = new ArrayCollection();
        $this->transactions = new ArrayCollection();
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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getIndividualPrice(): ?float
    {
        $individual_price = $this->individual_price;
        if (!is_null($individual_price)) {
            $individual_price /= 100;
        }

        return $individual_price;
    }

    public function setIndividualPrice(?float $individual_price): self
    {
        if (!is_null($individual_price)) {
            $individual_price *= 100;
        }
        $this->individual_price = $individual_price;

        return $this;
    }

    /**
     * @return Collection<int, Contract>
     */
    public function getContracts(): Collection
    {
        return $this->contracts;
    }

    public function addContract(Contract $contract): self
    {
        if (!$this->contracts->contains($contract)) {
            $this->contracts->add($contract);
            $contract->setAccount($this);
        }

        return $this;
    }

    public function removeContract(Contract $contract): self
    {
        if ($this->contracts->removeElement($contract)) {
            // set the owning side to null (unless already changed)
            if ($contract->getAccount() === $this) {
                $contract->setAccount(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TransactionLine>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransactionLine(TransactionLine $transactionLine): self
    {
        if (!$this->transactions->contains($transactionLine)) {
            $this->transactions->add($transactionLine);
            $transactionLine->setAccount($this);
        }

        return $this;
    }

    public function removeTransactionLine(TransactionLine $transactionLine): self
    {
        if ($this->transactions->removeElement($transactionLine)) {
            // set the owning side to null (unless already changed)
            if ($transactionLine->getAccount() === $this) {
                $transactionLine->setAccount(null);
            }
        }

        return $this;
    }

    #[Groups(['account:read'])]
    public function getBalance(): float
    {
        $balance = 0;
        $balance += $this->contracts->reduce(function (float $balance, Contract $contract) {
            return $balance + $contract->getIncomes()->reduce(function (float $balance, ContractIncome $income) {
                return $balance + $income->getReceived();
            }, 0.0);
        }, 0.0);

        $balance += $this->transactions->reduce(function (float $balance, TransactionLine $transaction) {
            return $balance + $transaction->getAmount();
        }, 0.0);

        return $balance;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
