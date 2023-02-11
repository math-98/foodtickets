<?php

namespace App\Entity;

use App\Repository\ContractIncomeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContractIncomeRepository::class)]
class ContractIncome
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'incomes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Contract $contract = null;

    #[ORM\Column(length: 255)]
    private ?string $period = null;

    #[ORM\Column(length: 1024)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2)]
    private ?string $planned = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2)]
    private ?string $billed = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2)]
    private ?string $received = null;

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

    public function getPlanned(): ?string
    {
        return $this->planned;
    }

    public function setPlanned(string $planned): self
    {
        if (!$this->getIsPlanned()) {
            $this->planned = 0;
        } elseif ($this->contract->getAccount()->getIndividualPrice()) {
            $this->planned = floor($planned);
        } else {
            $this->planned = $planned;
        }

        return $this;
    }

    public function getIsPlanned(): bool
    {
        return !$this->isLastPeriod() || !$this->contract->isReceptionDelayed();
    }

    public function getBilled(): ?string
    {
        return $this->billed;
    }

    public function setBilled(string $billed): self
    {
        if (!$this->getIsBilled()) {
            $this->billed = 0;
        } elseif ($this->contract->getAccount()->getIndividualPrice()) {
            $this->billed = floor($billed);
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
            $isBilled = !$this->contract->isReceptionDelayed();
        }

        return $isBilled;
    }

    public function getReceived(): ?string
    {
        return $this->received;
    }

    public function setReceived(string $received): self
    {
        if (!$this->getIsReceived()) {
            $this->received = 0;
        } elseif ($this->contract->getAccount()->getIndividualPrice()) {
            $this->received = floor($received);
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
