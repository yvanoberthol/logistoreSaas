<?php

namespace App\Entity;

use App\Repository\BankRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BankRepository::class)
 */
class Bank
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $accountName;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $accountNumber;

    /**
     * @ORM\OneToMany(targetEntity=Transaction::class, mappedBy="bank")
     */
    private $transactions;

    /**
     * @ORM\Column(type="float")
     */
    private $initialBalance = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status = true;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phoneNumber;

    /**
     * @ORM\ManyToOne(targetEntity=Store::class, inversedBy="banks")
     */
    private $store;

    public function __construct()
    {
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

    public function getTitle(): ?string
    {
        return $this->getName().'('.$this->getAccountName().')';
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAccountName(): ?string
    {
        return $this->accountName;
    }

    public function setAccountName(string $accountName): self
    {
        $this->accountName = $accountName;

        return $this;
    }

    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(string $accountNumber): self
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    public function getBalance(){
        return $this->getInitialBalance() + $this->getAmountCredited() - $this->getAmountDebited();
    }

    public function getAmountCredited(){
        $transactionCredited = array_filter(
            $this->getTransactions()->toArray(), static function(Transaction $transaction){
            return $transaction->getType() === '1';
        });

        return array_sum(array_map(static function(Transaction $transaction){
            return $transaction->getTotalAmount();
        },$transactionCredited ));
    }

    public function getAmountDebited(){
        $transactionDebited = array_filter(
            $this->getTransactions()->toArray(), static function(Transaction $transaction){
            return $transaction->getType() === '0';
        });

        return array_sum(array_map(static function(Transaction $transaction){
            return $transaction->getTotalAmount();
        },$transactionDebited ));
    }

    /**
     * @return Collection|Transaction[]
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions[] = $transaction;
            $transaction->setBank($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getBank() === $this) {
                $transaction->setBank(null);
            }
        }

        return $this;
    }

    public function getInitialBalance(): ?float
    {
        return $this->initialBalance;
    }

    public function setInitialBalance(float $initialBalance): self
    {
        $this->initialBalance = $initialBalance;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
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
