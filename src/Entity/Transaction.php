<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    public const TYPES = [
        'expense' => [
            'icon' => 'fas fa-shopping-cart',
            'name' => 'Dépense',
            'inbound' => false,
            'outbound' => true,
        ],
        'exchange' => [
            'icon' => 'fas fa-exchange-alt',
            'name' => 'Échange',
            'inbound' => true,
            'outbound' => true,
        ],
        'bought' => [
            'icon' => 'fas fa-plus',
            'name' => 'Achat',
            'inbound' => true,
            'outbound' => false,
        ],
        'sell' => [
            'icon' => 'fas fa-minus',
            'name' => 'Vente',
            'inbound' => false,
            'outbound' => true,
        ],
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    #[Groups(['transaction:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 1024)]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'transaction', targetEntity: TransactionLine::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $transactionLines;

    public function __construct()
    {
        $this->transactionLines = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, TransactionLine>
     */
    public function getTransactionLines(): Collection
    {
        return $this->transactionLines;
    }

    public function addTransactionLine(TransactionLine $transactionLine): self
    {
        if (!$this->transactionLines->contains($transactionLine)) {
            $this->transactionLines->add($transactionLine);
            $transactionLine->setTransaction($this);
        }

        return $this;
    }

    public function removeTransactionLine(TransactionLine $transactionLine): self
    {
        if ($this->transactionLines->removeElement($transactionLine)) {
            // set the owning side to null (unless already changed)
            if ($transactionLine->getTransaction() === $this) {
                $transactionLine->setTransaction(null);
            }
        }

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->transactionLines->reduce(function ($amount, TransactionLine $transactionLine) {
            $subAmount = $transactionLine->getAmount();
            if ($transactionLine->getAccount()->getIndividualPrice()) {
                $subAmount *= $transactionLine->getAccount()->getIndividualPrice();
            }

            return $amount + $subAmount;
        }, 0.0);
    }
}
