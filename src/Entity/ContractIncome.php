<?php

namespace App\Entity;

use App\Repository\ContractIncomeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ContractIncomeRepository::class)]
class ContractIncome
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['income:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'incomes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Contract $contract = null;

    #[ORM\Column(length: 255)]
    #[Groups(['income:read'])]
    private ?string $period = null;

    #[ORM\Column(length: 1024)]
    #[Groups(['income:read'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Groups(['income:read'])]
    private ?int $planned = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Groups(['income:read'])]
    private ?int $billed = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Groups(['income:read'])]
    private ?int $received = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContract(): ?Contract
    {
        return $this->contract;
    }

    public function setContract(?Contract $contract): self
    {
        $this->contract = $contract;

        return $this;
    }

    #[Groups(['income:read'])]
    public function getContractId(): ?int
    {
        return $this->contract->getId();
    }

    public function getPeriod(): ?\DateTimeInterface
    {
        return new \DateTime($this->period.'-01');
    }

    public function setPeriod(\DateTimeInterface $period): self
    {
        $this->period = $period->format('Y-m');

        return $this;
    }

    public function isPeriodDuringContract(): bool
    {
        $contract_start = clone $this->contract->getStart();
        $contract_start->modify('first day of this month');
        if ($this->getPeriod() < $contract_start) {
            return false;
        }

        if (!is_null($this->contract->getLastPeriod()) && $this->getPeriod() > $this->contract->getLastPeriod()) {
            return false;
        }

        return true;
    }

    public function isFirstPeriod(): bool
    {
        return $this->getPeriod()->format('Y-m') == $this->contract->getStart()->format('Y-m');
    }

    public function isLatestPeriod(): bool
    {
        if (!is_null($this->contract->getLastPeriod()) && $this->contract->getLastPeriod() < date_create()) {
            $lastPeriodStr = $this->contract->getLastPeriod()->format('Y-m');
        } else {
            $lastPeriodStr = date('Y-m');
        }

        return $this->getPeriod()->format('Y-m') == $lastPeriodStr;
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

    public function getPlanned(): ?float
    {
        $planned = $this->planned;
        if (!$planned) {
            return $planned;
        }
        if (!$this->getContract()->getAccount()->getIndividualPrice()) {
            $planned /= 100;
        }

        return $planned;
    }

    public function setPlanned(?float $planned): self
    {
        if (!$this->getIsPlanned() || is_null($planned)) {
            $this->planned = null;
        } elseif (!$this->contract->getAccount()->getIndividualPrice()) {
            $this->planned = $planned * 100;
        } else {
            $this->planned = $planned;
        }

        return $this;
    }

    public function getIsPlanned(): bool
    {
        if ($this->isLatestPeriod()) {
            return !$this->contract->isBillingDelayed() || !$this->contract->isReceptionDelayed();
        }

        return true;
    }

    public function getBilled(): ?float
    {
        $billed = $this->billed;
        if (!$billed) {
            return $billed;
        }
        if (!$this->getContract()->getAccount()->getIndividualPrice()) {
            $billed /= 100;
        }

        return $billed;
    }

    public function setBilled(?float $billed): self
    {
        if (!$this->getIsBilled() || is_null($billed)) {
            $this->billed = null;
        } elseif (!$this->contract->getAccount()->getIndividualPrice()) {
            $this->billed = $billed * 100;
        } else {
            $this->billed = $billed;
        }

        return $this;
    }

    public function getIsBilled(): bool
    {
        $isBilled = true;
        if ($this->isFirstPeriod()) {
            $isBilled = !$this->contract->isBillingDelayed();
        } elseif ($this->isLatestPeriod()) {
            $isBilled = $this->contract->isBillingDelayed() || !$this->contract->isReceptionDelayed();
        }

        return $isBilled;
    }

    public function getReceived(): ?float
    {
        $received = $this->received;
        if (!$received) {
            return $received;
        }
        if (!$this->getContract()->getAccount()->getIndividualPrice()) {
            $received /= 100;
        }

        return $received;
    }

    public function setReceived(?float $received): self
    {
        if (!$this->getIsReceived() || is_null($received)) {
            $this->received = null;
        } elseif (!$this->contract->getAccount()->getIndividualPrice()) {
            $this->received = $received * 100;
        } else {
            $this->received = $received;
        }

        return $this;
    }

    public function getIsReceived(): bool
    {
        return !$this->isFirstPeriod() || !$this->contract->isReceptionDelayed();
    }
}
