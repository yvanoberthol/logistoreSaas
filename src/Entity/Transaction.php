<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TransactionRepository::class)
 */
class Transaction
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $transactionCode;

    /**
     * @ORM\Column(type="float")
     */
    private $amount = 0.0;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=Bank::class, inversedBy="transactions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $bank;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="float")
     */
    private $fee = 0.0;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="transactions")
     */
    private $recorder;

    /**
     * @ORM\OneToOne(targetEntity=Expense::class, mappedBy="transaction", cascade={"persist", "remove"})
     */
    private $expense;

    /**
     * @ORM\ManyToOne(targetEntity=Store::class, inversedBy="transactions")
     */
    private $store;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getTransactionCode(): ?string
    {
        return $this->transactionCode;
    }

    public function setTransactionCode(string $transactionCode): self
    {
        $this->transactionCode = $transactionCode;

        return $this;
    }

    public function getAmount(): ?float
    {
        if ($this->getExpense() !== null){
            return $this->getExpense()->getAmount();
        }
        return $this->amount;
    }

    public function getTotalAmount(): ?float
    {
        return $this->getAmount() + $this->getFee();
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDescription(): ?string
    {
        if ($this->getExpense() !== null){
            return $this->getExpense()->getDescription();
        }
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getBank(): ?Bank
    {
        return $this->bank;
    }

    public function setBank(?Bank $bank): self
    {
        $this->bank = $bank;

        return $this;
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

    public function getFee(): ?float
    {
        return $this->fee;
    }

    public function setFee(float $fee): self
    {
        $this->fee = $fee;

        return $this;
    }

    public function getRecorder(): ?User
    {
        return $this->recorder;
    }

    public function setRecorder(?User $recorder): self
    {
        $this->recorder = $recorder;

        return $this;
    }

    public function getExpense(): ?Expense
    {
        return $this->expense;
    }

    public function setExpense(?Expense $expense): self
    {
        // unset the owning side of the relation if necessary
        if ($expense === null && $this->expense !== null) {
            $this->expense->setTransaction(null);
        }

        // set the owning side of the relation if necessary
        if ($expense !== null && $expense->getTransaction() !== $this) {
            $expense->setTransaction($this);
        }

        $this->expense = $expense;

        return $this;
    }

    public function isDeletable(){
        return $this->getExpense() === null;
    }

    public function getStore(): ?Store
    {
        return $this->store;
    }

    public function setStore(?Store $store): self
    {
        $this->store = $store;

        return $this;
    }
}
