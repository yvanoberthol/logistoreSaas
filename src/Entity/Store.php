<?php

namespace App\Entity;

use App\Repository\StoreRepository;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @Vich\Uploadable()
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass=StoreRepository::class)
 */
class Store
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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imageName;

    /**
     * @Assert\Image()
     * @Vich\UploadableField(fileNameProperty="imageName",mapping="image_store")
     * @var File|null
     */
    protected $imageFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $slogan;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $year;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $webSite;

    /**
     * @Assert\Email(message="entity.store.email")
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $address;

    /**
     * @ORM\OneToMany(targetEntity=Product::class, mappedBy="store")
     */
    private $products;

    /**
     * @ORM\OneToMany(targetEntity=Sale::class, mappedBy="store")
     */
    private $sales;

    /**
     * @ORM\OneToMany(targetEntity=Adjustment::class, mappedBy="store")
     */
    private $adjustments;

    /**
     * @ORM\OneToMany(targetEntity=Attendance::class, mappedBy="store")
     */
    private $attendances;

    /**
     * @ORM\OneToMany(targetEntity=Bank::class, mappedBy="store")
     */
    private $banks;

    /**
     * @ORM\OneToMany(targetEntity=Connection::class, mappedBy="store")
     */
    private $connections;

    /**
     * @ORM\OneToMany(targetEntity=Customer::class, mappedBy="store")
     */
    private $customers;

    /**
     * @ORM\OneToMany(targetEntity=Encashment::class, mappedBy="store")
     */
    private $encashments;

    /**
     * @ORM\OneToMany(targetEntity=Expense::class, mappedBy="store")
     */
    private $expenses;

    /**
     * @ORM\OneToMany(targetEntity=ExpenseType::class, mappedBy="store")
     */
    private $expenseTypes;

    /**
     * @ORM\OneToMany(targetEntity=Loss::class, mappedBy="store")
     */
    private $losses;

    /**
     * @ORM\OneToMany(targetEntity=LossType::class, mappedBy="store")
     */
    private $lossTypes;

    /**
     * @ORM\OneToMany(targetEntity=NoticeBoard::class, mappedBy="store")
     */
    private $noticeBoards;

    /**
     * @ORM\OneToMany(targetEntity=PageSize::class, mappedBy="store")
     */
    private $pageSizes;

    /**
     * @ORM\OneToMany(targetEntity=PaymentMethod::class, mappedBy="store")
     */
    private $paymentMethods;

    /**
     * @ORM\OneToMany(targetEntity=ProductCategory::class, mappedBy="store")
     */
    private $productCategories;

    /**
     * @ORM\OneToMany(targetEntity=ProductPackaging::class, mappedBy="store")
     */
    private $productPackagings;

    /**
     * @ORM\OneToMany(targetEntity=Role::class, mappedBy="store")
     */
    private $roles;

    /**
     * @ORM\OneToMany(targetEntity=Setting::class, mappedBy="store")
     */
    private $settings;

    /**
     * @ORM\OneToMany(targetEntity=Stock::class, mappedBy="store")
     */
    private $stocks;

    /**
     * @ORM\OneToMany(targetEntity=Subscription::class, mappedBy="store")
     */
    private $subscriptions;

    /**
     * @ORM\OneToMany(targetEntity=Supplier::class, mappedBy="store")
     */
    private $suppliers;

    /**
     * @ORM\OneToMany(targetEntity=Tax::class, mappedBy="store")
     */
    private $taxes;

    /**
     * @ORM\OneToMany(targetEntity=Theme::class, mappedBy="store")
     */
    private $themes;

    /**
     * @ORM\OneToMany(targetEntity=Transaction::class, mappedBy="store")
     */
    private $transactions;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="store")
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity=Addons::class, mappedBy="store")
     */
    private $addons;

    /**
     * @ORM\OneToMany(targetEntity=SalaryPayment::class, mappedBy="store")
     */
    private $salaryPayments;

    /**
     * @ORM\OneToMany(targetEntity=EmployeeFee::class, mappedBy="store")
     */
    private $employeeFees;

    private $role;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $appCode="";

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->sales = new ArrayCollection();
        $this->adjustments = new ArrayCollection();
        $this->attendances = new ArrayCollection();
        $this->banks = new ArrayCollection();
        $this->connections = new ArrayCollection();
        $this->customers = new ArrayCollection();
        $this->encashments = new ArrayCollection();
        $this->expenses = new ArrayCollection();
        $this->expenseTypes = new ArrayCollection();
        $this->losses = new ArrayCollection();
        $this->lossTypes = new ArrayCollection();
        $this->noticeBoards = new ArrayCollection();
        $this->pageSizes = new ArrayCollection();
        $this->paymentMethods = new ArrayCollection();
        $this->productCategories = new ArrayCollection();
        $this->productPackagings = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->settings = new ArrayCollection();
        $this->stocks = new ArrayCollection();
        $this->subscriptions = new ArrayCollection();
        $this->suppliers = new ArrayCollection();
        $this->taxes = new ArrayCollection();
        $this->themes = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->addons = new ArrayCollection();
        $this->salaryPayments = new ArrayCollection();
        $this->employeeFees = new ArrayCollection();
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

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): self
    {
        $this->imageName = $imageName;

        return $this;
    }

    /**
     * @return File|null
     */
    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    /**
     * @param File|null $imageFile
     * @return Store
     * @throws Exception
     */
    public function setImageFile(?File $imageFile): self
    {
        $this->imageFile = $imageFile;
        if (null !== $imageFile){
            $this->updatedAt = new DateTimeImmutable();
        }
        return $this;
    }

    public function getSlogan(): ?string
    {
        return $this->slogan;
    }

    public function setSlogan(?string $slogan): self
    {
        $this->slogan = $slogan;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): self
    {
        $this->year = $year;

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

    public function getWebSite(): ?string
    {
        return $this->webSite;
    }

    public function setWebSite(?string $webSite): self
    {
        $this->webSite = $webSite;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return DateTimeInterface
     */
    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTimeInterface $updatedAt
     * @return Store
     */
    public function setUpdatedAt(?DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return Role
     */
    public function getRole(): ?Role
    {
        return $this->role;
    }

    /**
     * @param Role $role
     * @return Store
     */
    public function setRole(Role $role): self
    {
        $this->role = $role;
        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setDate(): void {
        $this->updatedAt = new DateTime();
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

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setStore($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getStore() === $this) {
                $product->setStore(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Sale>
     */
    public function getSales(): Collection
    {
        return $this->sales;
    }

    public function addSale(Sale $sale): self
    {
        if (!$this->sales->contains($sale)) {
            $this->sales[] = $sale;
            $sale->setStore($this);
        }

        return $this;
    }

    public function removeSale(Sale $sale): self
    {
        if ($this->sales->removeElement($sale)) {
            // set the owning side to null (unless already changed)
            if ($sale->getStore() === $this) {
                $sale->setStore(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Adjustment>
     */
    public function getAdjustments(): Collection
    {
        return $this->adjustments;
    }

    public function addAdjustment(Adjustment $adjustment): self
    {
        if (!$this->adjustments->contains($adjustment)) {
            $this->adjustments[] = $adjustment;
            $adjustment->setStore($this);
        }

        return $this;
    }

    public function removeAdjustment(Adjustment $adjustment): self
    {
        if ($this->adjustments->removeElement($adjustment)) {
            // set the owning side to null (unless already changed)
            if ($adjustment->getStore() === $this) {
                $adjustment->setStore(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Attendance>
     */
    public function getAttendances(): Collection
    {
        return $this->attendances;
    }

    public function addAttendance(Attendance $attendance): self
    {
        if (!$this->attendances->contains($attendance)) {
            $this->attendances[] = $attendance;
            $attendance->setStore($this);
        }

        return $this;
    }

    public function removeAttendance(Attendance $attendance): self
    {
        if ($this->attendances->removeElement($attendance)) {
            // set the owning side to null (unless already changed)
            if ($attendance->getStore() === $this) {
                $attendance->setStore(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Bank>
     */
    public function getBanks(): Collection
    {
        return $this->banks;
    }

    public function addBank(Bank $bank): self
    {
        if (!$this->banks->contains($bank)) {
            $this->banks[] = $bank;
            $bank->setStore($this);
        }

        return $this;
    }

    public function removeBank(Bank $bank): self
    {
        if ($this->banks->removeElement($bank)) {
            // set the owning side to null (unless already changed)
            if ($bank->getStore() === $this) {
                $bank->setStore(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Connection>
     */
    public function getConnections(): Collection
    {
        return $this->connections;
    }

    public function addConnection(Connection $connection): self
    {
        if (!$this->connections->contains($connection)) {
            $this->connections[] = $connection;
            $connection->setStore($this);
        }

        return $this;
    }

    public function removeConnection(Connection $connection): self
    {
        if ($this->connections->removeElement($connection)) {
            // set the owning side to null (unless already changed)
            if ($connection->getStore() === $this) {
                $connection->setStore(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Customer>
     */
    public function getCustomers(): Collection
    {
        return $this->customers;
    }

    public function addCustomer(Customer $customer): self
    {
        if (!$this->customers->contains($customer)) {
            $this->customers[] = $customer;
            $customer->setStore($this);
        }

        return $this;
    }

    public function removeCustomer(Customer $customer): self
    {
        if ($this->customers->removeElement($customer)) {
            // set the owning side to null (unless already changed)
            if ($customer->getStore() === $this) {
                $customer->setStore(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Encashment>
     */
    public function getEncashments(): Collection
    {
        return $this->encashments;
    }

    public function addEncashment(Encashment $encashment): self
    {
        if (!$this->encashments->contains($encashment)) {
            $this->encashments[] = $encashment;
            $encashment->setStore($this);
        }

        return $this;
    }

    public function removeEncashment(Encashment $encashment): self
    {
        if ($this->encashments->removeElement($encashment)) {
            // set the owning side to null (unless already changed)
            if ($encashment->getStore() === $this) {
                $encashment->setStore(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Expense>
     */
    public function getExpenses(): Collection
    {
        return $this->expenses;
    }

    public function addExpense(Expense $expense): self
    {
        if (!$this->expenses->contains($expense)) {
            $this->expenses[] = $expense;
            $expense->setStore($this);
        }

        return $this;
    }

    public function removeExpense(Expense $expense): self
    {
        if ($this->expenses->removeElement($expense)) {
            // set the owning side to null (unless already changed)
            if ($expense->getStore() === $this) {
                $expense->setStore(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ExpenseType>
     */
    public function getExpenseTypes(): Collection
    {
        return $this->expenseTypes;
    }

    public function addExpenseType(ExpenseType $expenseType): self
    {
        if (!$this->expenseTypes->contains($expenseType)) {
            $this->expenseTypes[] = $expenseType;
            $expenseType->setStore($this);
        }

        return $this;
    }

    public function removeExpenseType(ExpenseType $expenseType): self
    {
        if ($this->expenseTypes->removeElement($expenseType)) {
            // set the owning side to null (unless already changed)
            if ($expenseType->getStore() === $this) {
                $expenseType->setStore(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Loss>
     */
    public function getLosses(): Collection
    {
        return $this->losses;
    }

    public function addLoss(Loss $loss): self
    {
        if (!$this->losses->contains($loss)) {
            $this->losses[] = $loss;
            $loss->setStore($this);
        }

        return $this;
    }

    public function removeLoss(Loss $loss): self
    {
        if ($this->losses->removeElement($loss)) {
            // set the owning side to null (unless already changed)
            if ($loss->getStore() === $this) {
                $loss->setStore(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, LossType>
     */
    public function getLossTypes(): Collection
    {
        return $this->lossTypes;
    }

    public function addLossType(LossType $lossType): self
    {
        if (!$this->lossTypes->contains($lossType)) {
            $this->lossTypes[] = $lossType;
            $lossType->setStore($this);
        }

        return $this;
    }

    public function removeLossType(LossType $lossType): self
    {
        if ($this->lossTypes->removeElement($lossType)) {
            // set the owning side to null (unless already changed)
            if ($lossType->getStore() === $this) {
                $lossType->setStore(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, NoticeBoard>
     */
    public function getNoticeBoards(): Collection
    {
        return $this->noticeBoards;
    }

    public function addNoticeBoard(NoticeBoard $noticeBoard): self
    {
        if (!$this->noticeBoards->contains($noticeBoard)) {
            $this->noticeBoards[] = $noticeBoard;
            $noticeBoard->setStore($this);
        }

        return $this;
    }

    public function removeNoticeBoard(NoticeBoard $noticeBoard): self
    {
        if ($this->noticeBoards->removeElement($noticeBoard)) {
            // set the owning side to null (unless already changed)
            if ($noticeBoard->getStore() === $this) {
                $noticeBoard->setStore(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PageSize>
     */
    public function getPageSizes(): Collection
    {
        return $this->pageSizes;
    }

    public function addPageSize(PageSize $pageSize): self
    {
        if (!$this->pageSizes->contains($pageSize)) {
            $this->pageSizes[] = $pageSize;
            $pageSize->setStore($this);
        }

        return $this;
    }

    public function removePageSize(PageSize $pageSize): self
    {
        if ($this->pageSizes->removeElement($pageSize)) {
            // set the owning side to null (unless already changed)
            if ($pageSize->getStore() === $this) {
                $pageSize->setStore(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PaymentMethod>
     */
    public function getPaymentMethods(): Collection
    {
        return $this->paymentMethods;
    }

    public function addPaymentMethod(PaymentMethod $paymentMethod): self
    {
        if (!$this->paymentMethods->contains($paymentMethod)) {
            $this->paymentMethods[] = $paymentMethod;
            $paymentMethod->setStore($this);
        }

        return $this;
    }

    public function removePaymentMethod(PaymentMethod $paymentMethod): self
    {
        if ($this->paymentMethods->removeElement($paymentMethod)) {
            // set the owning side to null (unless already changed)
            if ($paymentMethod->getStore() === $this) {
                $paymentMethod->setStore(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductCategory>
     */
    public function getProductCategories(): Collection
    {
        return $this->productCategories;
    }

    public function addProductCategory(ProductCategory $productCategory): self
    {
        if (!$this->productCategories->contains($productCategory)) {
            $this->productCategories[] = $productCategory;
            $productCategory->setStore($this);
        }

        return $this;
    }

    public function removeProductCategory(ProductCategory $productCategory): self
    {
        if ($this->productCategories->removeElement($productCategory)) {
            // set the owning side to null (unless already changed)
            if ($productCategory->getStore() === $this) {
                $productCategory->setStore(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductPackaging>
     */
    public function getProductPackagings(): Collection
    {
        return $this->productPackagings;
    }

    public function addProductPackaging(ProductPackaging $productPackaging): self
    {
        if (!$this->productPackagings->contains($productPackaging)) {
            $this->productPackagings[] = $productPackaging;
            $productPackaging->setStore($this);
        }

        return $this;
    }

    public function removeProductPackaging(ProductPackaging $productPackaging): self
    {
        if ($this->productPackagings->removeElement($productPackaging)) {
            // set the owning side to null (unless already changed)
            if ($productPackaging->getStore() === $this) {
                $productPackaging->setStore(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Role>
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
            $role->setStore($this);
        }

        return $this;
    }

    public function removeRole(Role $role): self
    {
        if ($this->roles->removeElement($role)) {
            // set the owning side to null (unless already changed)
            if ($role->getStore() === $this) {
                $role->setStore(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Setting>
     */
    public function getSettings(): Collection
    {
        return $this->settings;
    }

    public function addSetting(Setting $setting): self
    {
        if (!$this->settings->contains($setting)) {
            $this->settings[] = $setting;
            $setting->setStore($this);
        }

        return $this;
    }

    public function removeSetting(Setting $setting): self
    {
        if ($this->settings->removeElement($setting)) {
            // set the owning side to null (unless already changed)
            if ($setting->getStore() === $this) {
                $setting->setStore(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Stock>
     */
    public function getStocks(): Collection
    {
        return $this->stocks;
    }

    public function addStock(Stock $stock): self
    {
        if (!$this->stocks->contains($stock)) {
            $this->stocks[] = $stock;
            $stock->setStore($this);
        }

        return $this;
    }

    public function removeStock(Stock $stock): self
    {
        if ($this->stocks->removeElement($stock)) {
            // set the owning side to null (unless already changed)
            if ($stock->getStore() === $this) {
                $stock->setStore(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Subscription>
     */
    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    public function addSubscription(Subscription $subscription): self
    {
        if (!$this->subscriptions->contains($subscription)) {
            $this->subscriptions[] = $subscription;
            $subscription->setStore($this);
        }

        return $this;
    }

    public function removeSubscription(Subscription $subscription): self
    {
        if ($this->subscriptions->removeElement($subscription)) {
            // set the owning side to null (unless already changed)
            if ($subscription->getStore() === $this) {
                $subscription->setStore(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Supplier>
     */
    public function getSuppliers(): Collection
    {
        return $this->suppliers;
    }

    public function addSupplier(Supplier $supplier): self
    {
        if (!$this->suppliers->contains($supplier)) {
            $this->suppliers[] = $supplier;
            $supplier->setStore($this);
        }

        return $this;
    }

    public function removeSupplier(Supplier $supplier): self
    {
        if ($this->suppliers->removeElement($supplier)) {
            // set the owning side to null (unless already changed)
            if ($supplier->getStore() === $this) {
                $supplier->setStore(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Tax>
     */
    public function getTaxes(): Collection
    {
        return $this->taxes;
    }

    public function addTax(Tax $tax): self
    {
        if (!$this->taxes->contains($tax)) {
            $this->taxes[] = $tax;
            $tax->setStore($this);
        }

        return $this;
    }

    public function removeTax(Tax $tax): self
    {
        if ($this->taxes->removeElement($tax)) {
            // set the owning side to null (unless already changed)
            if ($tax->getStore() === $this) {
                $tax->setStore(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Theme>
     */
    public function getThemes(): Collection
    {
        return $this->themes;
    }

    public function addTheme(Theme $theme): self
    {
        if (!$this->themes->contains($theme)) {
            $this->themes[] = $theme;
            $theme->setStore($this);
        }

        return $this;
    }

    public function removeTheme(Theme $theme): self
    {
        if ($this->themes->removeElement($theme)) {
            // set the owning side to null (unless already changed)
            if ($theme->getStore() === $this) {
                $theme->setStore(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions[] = $transaction;
            $transaction->setStore($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getStore() === $this) {
                $transaction->setStore(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addStore($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeStore($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Addons>
     */
    public function getAddons(): Collection
    {
        return $this->addons;
    }

    public function addAddon(Addons $addon): self
    {
        if (!$this->addons->contains($addon)) {
            $this->addons[] = $addon;
            $addon->setStore($this);
        }

        return $this;
    }

    public function removeAddon(Addons $addon): self
    {
        if ($this->addons->removeElement($addon)) {
            // set the owning side to null (unless already changed)
            if ($addon->getStore() === $this) {
                $addon->setStore(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SalaryPayment>
     */
    public function getSalaryPayments(): Collection
    {
        return $this->salaryPayments;
    }

    public function addSalaryPayment(SalaryPayment $salaryPayment): self
    {
        if (!$this->salaryPayments->contains($salaryPayment)) {
            $this->salaryPayments[] = $salaryPayment;
            $salaryPayment->setStore($this);
        }

        return $this;
    }

    public function removeSalaryPayment(SalaryPayment $salaryPayment): self
    {
        if ($this->salaryPayments->removeElement($salaryPayment)) {
            // set the owning side to null (unless already changed)
            if ($salaryPayment->getStore() === $this) {
                $salaryPayment->setStore(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EmployeeFee>
     */
    public function getEmployeeFees(): Collection
    {
        return $this->employeeFees;
    }

    public function addEmployeeFee(EmployeeFee $employeeFee): self
    {
        if (!$this->employeeFees->contains($employeeFee)) {
            $this->employeeFees[] = $employeeFee;
            $employeeFee->setStore($this);
        }

        return $this;
    }

    public function removeEmployeeFee(EmployeeFee $employeeFee): self
    {
        if ($this->employeeFees->removeElement($employeeFee)) {
            // set the owning side to null (unless already changed)
            if ($employeeFee->getStore() === $this) {
                $employeeFee->setStore(null);
            }
        }

        return $this;
    }

    public function getAppCode(): ?string
    {
        return $this->appCode;
    }

    public function setAppCode(string $appCode): self
    {
        $this->appCode = $appCode;

        return $this;
    }

    public function __toString(){
        return 'Store';
    }
}
