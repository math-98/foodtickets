<?php

namespace App\Entity;

use App\Repository\ContractRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ContractRepository::class)]
class Contract
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['contract:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['contract:read'])]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'contracts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Account $account = null;

    #[ORM\ManyToOne(inversedBy: 'contracts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['contract:read'])]
    private ?\DateTimeInterface $start = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['contract:read'])]
    private ?\DateTimeInterface $end = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    #[Groups(['contract:read'])]
    private ?string $monthly_amount = null;

    #[ORM\Column]
    #[Groups(['contract:read'])]
    private ?bool $reception_delayed = null;

    #[ORM\Column]
    #[Groups(['contract:read'])]
    private ?bool $billing_delayed = null;

    #[ORM\OneToMany(mappedBy: 'contract', targetEntity: ContractIncome::class, orphanRemoval: true)]
    #[ORM\OrderBy(['period' => 'ASC'])]
    private Collection $incomes;

    public function __construct()
    {
        $this->incomes = new ArrayCollection();
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

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): self
    {
        $this->account = $account;

        return $this;
    }

    #[Groups(['contract:read'])]
    public function getAccountId(): ?int
    {
        return $this->account->getId();
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

    public function getLastPeriod(): ?\DateTimeInterface
    {
        if (is_null($this->end)) {
            return null;
        }

        if ($this->reception_delayed || $this->billing_delayed) {
            return (clone $this->end)->modify('last day of next month');
        } else {
            return $this->end;
        }
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

    /**
     * @return Collection<int, ContractIncome>
     */
    public function getIncomes(): Collection
    {
        return $this->incomes;
    }

    public function addIncome(ContractIncome $income): self
    {
        if (!$this->incomes->contains($income)) {
            $this->incomes->add($income);
            $income->setContract($this);
        }

        return $this;
    }

    public function removeIncome(ContractIncome $income): self
    {
        if ($this->incomes->removeElement($income)) {
            // set the owning side to null (unless already changed)
            if ($income->getContract() === $this) {
                $income->setContract(null);
            }
        }

        return $this;
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
