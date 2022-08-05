<?php


namespace App\Service;


use App\Entity\LossType;
use App\Entity\Package;
use App\Entity\PageSize;
use App\Entity\PaymentMethod;
use App\Entity\Permission;
use App\Entity\Product;
use App\Entity\ProductStock;
use App\Entity\Role;
use App\Entity\Setting;
use App\Entity\Stock;
use App\Entity\Store;
use App\Entity\Subscription;
use App\Entity\User;
use App\Repository\ProductRepository;
use App\Repository\ProductStockRepository;
use App\Util\GlobalConstant;
use App\Util\PackageConstant;
use App\Util\RoleConstant;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\RequestStack;

class StoreService
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var ProductService
     */
    private $productService;
    /**
     * @var Setting
     */
    private $setting;


    /**
     * OrderService constructor.
     * @param ProductService $productService
     * @param RequestStack $requestStack
     * @param ProductRepository $productRepository
     */
    public function __construct(ProductService $productService,
                                RequestStack $requestStack,
                                ProductRepository $productRepository)
    {
        $this->productService = $productService;
        $this->productRepository = $productRepository;
        $this->setting = $requestStack->getSession()->get('setting');
    }


    private function getLineProductState(Product $product): array
    {
        $producStocks =
            $this->productService->getProductStockDispoByProduct($product);

        $unitPrice = round(array_sum(array_map(static function(ProductStock $productStock){
            return $productStock->getUnitPrice();
        },$producStocks)) / count($producStocks));

        $sellPrice = $product->getSellPrice();
        if ($this->setting->getProductWithDiscount()){
            $sellPrice = $product->getSellPriceWithDiscount();
        }

        $salePrice = $product->getStock() * $sellPrice;
        $purchasePrice = $product->getStock() * $unitPrice;

        $profit = $product->getStock() * ($sellPrice - $unitPrice);

        $percentProfit =((float)$salePrice === 0.0) ?0
            :round(($profit * 100)/$salePrice,2);
        $unitProfit =((int)$product->getStock() === 0) ?0
            :round($profit/$product->getStock());
        return [
            'id' => $product->getId(),
            'barcode' => $product->getQrCode(),
            'name' => $product->getNameWithCategory(),
            'sellPrice' => $product->getSellPrice(),
            'buyPrice' => $unitPrice,
            'qty' => $product->getStock(),
            'salePrice' => $salePrice,
            'purchasePrice' => $purchasePrice,
            'profit' => $profit,
            'unitProfit' => $unitProfit,
            'percentProfit' => $percentProfit
        ];
    }

    /**
     * @param Store|null $store
     * @return array
     * @throws Exception
     */
    public function getProductStoreValue(Store $store=null): array
    {
        $products =
            $this->productService
                ->getProductByStockNotFinish($this->productRepository
                    ->findBy(['store' => $store]),$store);

        $lines = [];
        foreach ($products as $product){
            $lines[] = $this->getLineProductState($product);
        }

        return $lines;
    }

    public function getStore(User $user, Store $store): ?Store
    {
        $user->setRoles($store);
        return $store->setRole($user->getRole());
    }

    public function getStores(User $user): array
    {
        return array_map(static function(Store $store) use($user){
            $role = $user->getRules()
                ->filter(static function(Role $role) use($store){
                return $role->getStore() === $store;
            })->first();
            return $store->setRole($role);
        },$user->getStore()->toArray());

    }

    public function generateCode(Store $store): string
    {

      return $_ENV['APP_CODE'].str_pad($store->getId(),4,
              '0',STR_PAD_LEFT);
    }

    public function processCreateStore(EntityManagerInterface $entityManager,
                                        User $user,
                                        $store): void
    {
        $packageRepository = $entityManager->getRepository(Package::class);

        $packageDefault = $packageRepository
            ->findOneBy(['name' => PackageConstant::MENSUAL]);

        //subscription
        $subscription = new Subscription();
        $subscription->setStore($store)->setPackage($packageDefault)
            ->setDate(new DateTime())->setCreatedAt(new DateTime())
            ->setEnabled(true);
        $entityManager->persist($subscription);

        $cash = new PaymentMethod();
        $cash->setName('CASH');
        $cash->setStore($store);
        $entityManager->persist($cash);

        $allowedPermissionCahsier = [
            'ACCOUNT_CHANGE_LANGUAGE',
            'ACCOUNT_LOGOUT',
            'ACCOUNT_PROFILE',
            'ACCOUNT_RESET_PASSWORD',
            'HOME',
            'SALE_CHANGE_NUM_INVOICE',
            'SALE_CHOICE',
            'SALE_NEW',
            'SALE_MINE',
            'SALE_SOFT_DELETE',
            'SALE_PRINT',
            'SALE_WHOLESALER_NEW',
            'SEARCH_PRODUCT',
            'SEARCH_SALE',
            'STORE_INDEX',
            'STORE_SELECT',
            'STORE_LIST',
        ];

        $disallowedPermissionManager = [
            'ROLE_ADD_PERMISSION',
            'ROLE_DELETE',
            'ROLE_EDIT',
            'ROLE_INDEX',
            'ROLE_NEW',
            'ROLE_REMOVE_PERMISSION',
            'USER_CHANGE_ROLE',
            'USER_INDEX',
            'USER_NEW',
            'USER_ADD_CATEGORY',
            'USER_DETAIL',
            'USER_UPDATE_CUSTOMER',
            'USER_REMOVE_CATEGORY',
            'USER_UPDATE_STATUS',
            'ADDONS_NEW',
            'ADDONS_UPDATE_STATUS',
            'SETTING_THEME_ADD',
            'SETTING_THEME_DELETE',
            'SETTING_UPDATE',
            'SETTING_INDEX',
            'SETTING_FORMAT_DELETE',
            'SETTING_FORMAT_ADD',
        ];

        //roles
        foreach (RoleConstant::ROLES as $key=>$value){
            $role = new Role();
            $role->setName($key)
                ->setRank($value)->setUpdatable(false)->setStore($store);
            $permissionRepository = $entityManager->getRepository(Permission::class);

            if ($key === RoleConstant::ROLE_ADMIN){
                foreach ($permissionRepository->findAll() as $permission){
                    $role->addPermission($permission);
                }
            }

            if ($key === RoleConstant::ROLE_MANAGER){
                foreach ($permissionRepository->findAll() as $permission){

                    if (!in_array($permission->getCode(),
                        $disallowedPermissionManager,true)){
                        $role->addPermission($permission);
                    }

                }
            }

            if ($key === RoleConstant::ROLE_CASHIER){
                foreach ($permissionRepository->findAll() as $permission){

                    if (in_array($permission->getCode(),
                        $allowedPermissionCahsier,true)){
                        $role->addPermission($permission);
                    }

                }
            }

            $entityManager->persist($role);

            if ($key === RoleConstant::ROLE_ADMIN){
                //user
                $user->addRule($role);
                $user->addStore($store);
                $entityManager->persist($user);
            }
        }

        //loss type
        $lossTypeOutOfDate = new LossType();
        $lossTypeOutOfDate->setName(GlobalConstant::OUTOFDATE)
            ->setUpdatable(false)->setStore($store);
        $entityManager->persist($lossTypeOutOfDate);

        //format pDF
        $formatA4 = new PageSize();
        $formatA4->setName('A4');
        $formatA4->setWidth(210);
        $formatA4->setHeight(297);
        $formatA4->setDeletable(false);
        $formatA4->setStore($store);
        $entityManager->persist($formatA4);

        $formatA5 = new PageSize();
        $formatA5->setName('A5');
        $formatA5->setWidth(148);
        $formatA5->setHeight(210);
        $formatA5->setDeletable(false);
        $formatA5->setStore($store);
        $entityManager->persist($formatA5);

        $formatA6 = new PageSize();
        $formatA6->setName('A6');
        $formatA6->setWidth(105);
        $formatA6->setHeight(148);
        $formatA6->setDeletable(false);
        $formatA6->setStore($store);
        $entityManager->persist($formatA6);


        $setting = new Setting();
        $setting->setStore($store);

        $entityManager->persist($setting);
    }
}
