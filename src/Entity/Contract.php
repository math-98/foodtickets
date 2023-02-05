<?php

namespace App\Entity;

use App\Repository\ContractRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContractRepository::class)]
class Contract
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'contracts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Account $account = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $start = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $end = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    private ?string $monthly_amount = null;

    #[ORM\Column]
    private ?bool $reception_delayed = null;

    #[ORM\Column]
    private ?bool $billing_delayed = null;

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

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): self
    {
        $this->account = $account;

        return $this;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(\DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(?\DateTimeInterface $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function getMonthlyAmount(): ?string
    {
        return $this->monthly_amount;
    }

    public function setMonthlyAmount(?string $monthly_amount): self
    {
        $this->monthly_amount = $monthly_amount;

        return $this;
    }

    public function isReceptionDelayed(): ?bool
    {
        return $this->reception_delayed;
    }

    public function setReceptionDelayed(bool $reception_delayed): self
    {
        $this->reception_delayed = $reception_delayed;

        return $this;
    }

    public function isBillingDelayed(): ?bool
    {
        return $this->billing_delayed;
    }

    public function setBillingDelayed(bool $billing_delayed): self
    {
        $this->billing_delayed = $billing_delayed;

        return $this;
    }
}
