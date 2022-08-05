<?php


namespace App\Controller;


use App\Entity\Customer;
use App\Entity\Sale;
use App\Entity\Setting;
use App\Entity\Store;
use App\Entity\Supplier;
use App\Entity\User;
use App\Extension\AppExtension;
use App\Repository\CustomerRepository;
use App\Repository\ProductCategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\PaymentMethodRepository;
use App\Repository\SalePaymentRepository;
use App\Repository\SaleRepository;
use App\Repository\StockPaymentRepository;
use App\Repository\StockRepository;
use App\Repository\SupplierRepository;
use App\Repository\UserRepository;
use App\Util\CustomerTypeConstant;
use App\Util\GlobalConstant;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class PerformanceController extends AbstractController
{
    
    /**
     * @var Setting
     */
    private $setting;

    /**
     * @var Store
     */
    private $store;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->setting = $requestStack->getSession()->get('setting');
        $this->store = $requestStack->getSession()->get('store');
    }

    /**
     * @Route("/performance/product",name="performance_product",methods={"GET","POST"})
     * @param Request $request
     * @param ProductRepository $productRepository
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @throws Exception
     */
    public function product(Request $request,
                        ProductRepository $productRepository,
                        EntityManagerInterface $entityManager): Response
    {
        $userRepository = $entityManager->getRepository(User::class);

        $model['products'] = null;
        $model['users'] = $userRepository->findByStore($this->store);

        $model['start'] = $request->get('start') ?? new DateTime();
        $model['end'] = $request->get('end') ?? new DateTime();

        if (!GlobalConstant::compareDate($model['start'],$model['end'])){
            $model['end'] = $model['start'];
        }

        if ($request->get('employee') !== null){
            $model['user'] = ($request->get('employee') === '0')?null:
                $userRepository->find((int) $request->get('employee'));
            $model['userId'] = (int) $request->get('employee');
        }else{
            $model['user'] = null;
        }

        if (!$model['start'] instanceof DateTime && !$model['end'] instanceof DateTime){
            $model['start'] = new DateTime($model['start']);
            $model['end'] = new DateTime($model['end']);
        }

        $model['products'] = $productRepository
            ->saleByPeriodDate($model['start'],$model['end'],$model['user'],$this->store);

        if (count($model['products']) > 0){
            $productStats = array_map(static function ($product){
                return [
                    'name' => $product[0]->getName(),
                    'qty' => $product['qtySold']
                ];
            }, $model['products']);
            krsort($productStats);

            if (count($productStats) > 10){
                $model['productStats'] = array_splice($productStats,0,10);
            }else{
                $model['productStats'] = $productStats;
            }
        }

        //breadcumb
        $model['entity'] = 'controller.performance.performance.entity';
        $model['page'] = 'controller.performance.performance.page';

        return $this->render('performance/performanceSaleProduct.html.twig',$model);
    }


    /**
     * @Route("/performance/employee/{id}", name="performance_employee")
     * @param User $user
     * @param SaleRepository $saleRepository
     * @param Request $request
     * @return Response
     */
    public function employee(User $user,
                          SaleRepository $saleRepository,
                          Request $request): Response
    {
        $model['employee'] = $user;
        $model['saleStats'] = $saleRepository
            ->groupByDateUser($user,$this->store);

        $model['salesYear'] = $saleRepository
            ->groupByYearUser($user,$this->store);

        $model = GlobalConstant::getMonthsAndYear($request,$model);

        //breadcumb
        $model['entity'] = 'controller.performance.file.entity';
        $model['page'] = 'controller.performance.file.page';

        return $this->render('performance/performanceEmployee.html.twig',$model);
    }

    /**
     * @Route("/performance/customer/{id}", name="performance_customer",methods={"GET","POST"})
     * @param Customer $customer
     * @param SalePaymentRepository $salePaymentRepository
     * @param SaleRepository $saleRepository
     * @param CustomerRepository $customerRepository
     * @param PaymentMethodRepository $paymentMethodRepository
     * @param ProductRepository $productRepository
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function customer(Customer $customer,
                             SalePaymentRepository $salePaymentRepository,
                             SaleRepository $saleRepository,
                             CustomerRepository $customerRepository,
                             PaymentMethodRepository $paymentMethodRepository,
                             ProductRepository $productRepository,
                             Request $request): Response
    {

        if ($request->get('customerSearch')){
            $customer = $customerRepository->find((int) $request->get('customerSearch'));
        }

        $model['customer'] = $customer;

        $model['customers'] = $customerRepository->findByTypes([
            CustomerTypeConstant::TYPEKEYS['Reseller'],
            CustomerTypeConstant::TYPEKEYS['Simple Customer'],
        ],$this->store);

        $model['saleStats'] = $saleRepository
            ->groupByDateCustomer($customer,$this->store);

        $model['salesYear'] = $saleRepository
            ->groupByYearCustomer($customer,$this->store);

        $model['products'] = $productRepository
            ->saleByCustomer($customer,null,null,$this->store);

        $model['start'] = $request->get('start') ?? new DateTime();
        $model['end'] = $request->get('end') ?? new DateTime();

        if (!$model['start'] instanceof DateTime && !$model['end'] instanceof DateTime){
            $model['start'] = new DateTime($model['start']);
            $model['end'] = new DateTime($model['end']);
        }

        $model['salePayments'] = $salePaymentRepository
            ->lastTenPayments($model['customer']);

        $model['sales'] = $saleRepository
            ->findByPeriodCustomer($model['start'],$model['end'],$customer,null,$this->store);

        $model['paymentMethods'] = $paymentMethodRepository
            ->findBy(['status' => true,'store' => $this->store]);


        $model['showModalSale'] = $request->get('showModalSale')??false;

        $model = GlobalConstant::getMonthsAndYear($request,$model);

        //breadcumb
        $model['entity'] = 'controller.performance.file.entity';
        $model['page'] = 'controller.performanceCustomer.file.page';

        return $this->render('performance/performanceCustomer.html.twig',$model);
    }

    /**
     * @Route("/performance/supplier/{id}", name="performance_supplier",methods={"GET","POST"})
     * @param Supplier $supplier
     * @param StockPaymentRepository $stockPaymentRepository
     * @param StockRepository $stockRepository
     * @param SupplierRepository $supplierRepository
     * @param PaymentMethodRepository $paymentMethodRepository
     * @param ProductRepository $productRepository
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function supplier(Supplier $supplier,
                             StockPaymentRepository $stockPaymentRepository,
                             StockRepository $stockRepository,
                             SupplierRepository $supplierRepository,
                             PaymentMethodRepository $paymentMethodRepository,
                             ProductRepository $productRepository,
                             Request $request): Response
    {

        if ($request->get('supplierSearch')){
            $supplier = $supplierRepository->find((int) $request->get('supplierSearch'));
        }

        $model['supplier'] = $supplier;

        $model['suppliers'] = $supplierRepository->findBy(['store' => $this->store]);

        $model['stockStats'] = $stockRepository
            ->groupByDateSupplier($supplier,$this->store);

        $model['stocksYear'] = $stockRepository
            ->groupByYearSupplier($supplier,$this->store);

        $model['products'] = $productRepository
            ->stockBySupplier($supplier,null,null,$this->store);

        $model['start'] = $request->get('start') ?? new DateTime();
        $model['end'] = $request->get('end') ?? new DateTime();

        if (!$model['start'] instanceof DateTime && !$model['end'] instanceof DateTime){
            $model['start'] = new DateTime($model['start']);
            $model['end'] = new DateTime($model['end']);
        }

        $model['stockPayments'] = $stockPaymentRepository
            ->lastTenPayments($model['supplier']);

        $model['stocks'] = $stockRepository
            ->findByPeriodSupplier($model['start'],$model['end'],$supplier,$this->store);

        $model['paymentMethods'] = $paymentMethodRepository
            ->findBy(['status' => true,'store' => $this->store]);


        $model['showModalStock'] = $request->get('showModalStock')??false;

        $model = GlobalConstant::getMonthsAndYear($request,$model);

        //breadcumb
        $model['entity'] = 'controller.performance.file.entity';
        $model['page'] = 'controller.performanceSupplier.file.page';

        return $this->render('performance/performanceSupplier.html.twig',$model);
    }


    /**
     * @Route("/performance/sale", name="performance_index",methods={"GET","POST"})
     * @param SaleRepository $saleRepository
     * @param UserRepository $userRepository
     * @param Request $request
     * @return Response
     */
    public function index(SaleRepository $saleRepository,
                          UserRepository $userRepository,
                          Request $request): Response
    {

        $model = GlobalConstant::getMonthsAndYear($request);

        $model['saleStats'] = $saleRepository->groupByDateUser(null,$this->store);

        $model['employeeByMonth'] = $userRepository
            ->getSaleByMonth($model['monthNow'],$model['year'],$this->store);

        $model['employeeByYear'] = $userRepository
            ->getSaleByYear($model['year'],$this->store);

        //breadcumb
        $model['title'] = "controller.performance.index.title";
        $model['entity'] = 'controller.performance.index.entity';
        $model['page'] = "controller.performance.index.page";


        return $this->render('performance/performanceStore.html.twig',$model);

    }

    /**
     * @Route("/performance/purchase", name="performance_purchase",methods={"GET","POST"})
     * @param StockRepository $stockRepository
     * @param Request $request
     * @return Response
     */
    public function stock(StockRepository $stockRepository,
                          Request $request): Response
    {

        $model = GlobalConstant::getMonthsAndYear($request);

        $model['stockStats'] = $stockRepository->groupByDate($this->store);

        //breadcumb
        $model['title'] = "controller.performance.stock.title";
        $model['entity'] = 'controller.performance.stock.entity';
        $model['page'] = "controller.performance.stock.page";


        return $this->render('performance/performanceStock.html.twig',$model);

    }

    /**
     * @Route("/performance/profit", name="performance_profit")
     * @param SaleRepository $saleRepository
     * @param Request $request
     * @return Response
     */
    public function profit(SaleRepository $saleRepository,Request $request): Response
    {

        if (!$this->isGranted('PERMISSION_VERIFY','SALE_PROFIT')){
            throw new NotFoundHttpException("this ressource don't exists");
        }

        $model = GlobalConstant::getMonthsAndYear($request);
        $model['saleStats'] = $saleRepository->groupByDateUser(null,$this->store);

        $model['salesYear'] = $saleRepository->groupByYearProfit($this->store);

        //breadcumb
        $model['title'] = "controller.performance.profit.title";
        $model['entity'] = 'controller.performance.profit.entity';
        $model['page'] = "controller.performance.profit.page";

        return $this->render('performance/performanceProfit.html.twig',$model);
    }

    /**
     * @Route("/performance/paymentMethod", name="performance_payment_method", methods={"GET","POST"})
     * @param PaymentMethodRepository $paymentMethodRepository
     * @param SaleRepository $saleRepository
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function paymentMethod(PaymentMethodRepository $paymentMethodRepository,
                                  SaleRepository $saleRepository,
                                  Request $request): Response
    {

        $intervalDays = $this->setting->getMaxIntervalPeriod();

        $today = (new DateTime())->format('Y-m-d');
        $model['start'] = $request->get('start') ?? new DateTime($today.' 00:00');
        $model['end'] = $request->get('end') ?? new DateTime($today.' 23:59');

        if (!GlobalConstant::compareDate($model['start'],$model['end'])){
            $model['end'] = $model['start'];
        }

        if (GlobalConstant::getInterval($model['start'],$model['end']) > $intervalDays){
            $model['start'] = new DateTime();
            $model['end'] = new DateTime();
            $this->addFlash('danger',"controller.sale.index.flash.danger");
        }

        if (!$model['start'] instanceof DateTime && !$model['end'] instanceof DateTime){
            $model['start'] = new DateTime($model['start']);
            $model['end'] = new DateTime($model['end']);
        }

        $recorder = ($this->getUser()->getRole()->getRank() === 1)? $this->getUser(): null;
        $paymentMethods = $paymentMethodRepository
            ->findBy(['status' => true,'store' => $this->store]);

        $model['paymentMethods'] = [];
        foreach ($paymentMethods as $paymentMethod){
            $paymentMethodLine['paymentMethod'] = $paymentMethod;
            $paymentMethodLine['sales'] = $saleRepository
                ->findSaleByMethodPeriod($model['start'],$model['end'],$paymentMethod,
                    $recorder,$this->store);

            $paymentMethodLine['amountDebt'] = array_sum(array_map(static function(Sale $sale){
                return $sale->getAmountDebt();
            },$paymentMethodLine['sales']));

            $paymentMethodLine['amountReceived'] = array_sum(array_map(static function(Sale $sale){
                return $sale->getAmountTotalReceived();
            },$paymentMethodLine['sales']));

            $paymentMethodLine['totalAmount'] = array_sum(array_map(static function(Sale $sale){
                return $sale->getAmount();
            },$paymentMethodLine['sales']));

            $model['paymentMethods'][] = $paymentMethodLine;
        }



        $model['colors'] = ['success','secondary','info','grey','light'];

        //breadcumb
        $model['entity'] = 'controller.performance.paymentMethod.entity';
        $model['page'] = 'controller.performance.paymentMethod.page';
        return $this->render('performance/performancePaymentMethod.html.twig', $model);
    }


    /**
     * @Route("/performance/productCategory", name="performance_product_category", methods={"GET","POST"})
     * @param ProductCategoryRepository $productCategoryRepository
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function productCategory(ProductCategoryRepository $productCategoryRepository,
                                    Request $request): Response
    {

        $today = (new DateTime())->format('Y-m-d');
        $model['start'] = $request->get('start') ?? new DateTime($today.' 00:00');
        $model['end'] = $request->get('end') ?? new DateTime($today.' 23:59');

        if (!GlobalConstant::compareDate($model['start'],$model['end'])){
            $model['end'] = $model['start'];
        }

        if (!$model['start'] instanceof DateTime && !$model['end'] instanceof DateTime){
            $model['start'] = new DateTime($model['start']);
            $model['end'] = new DateTime($model['end']);
        }

        $model['categories'] = $productCategoryRepository
            ->getByPeriodDate($model['start'],$model['end'],$this->store);


            $amounts = array_map(static function($category){
                return $category['amount'];
            },$model['categories']);
            $model['totalAmount'] = array_sum($amounts);

            $profits = array_map(static function($category){
                return $category['profit'];
            },$model['categories']);
            $model['totalProfit'] = array_sum($profits);

            $qtySolds = array_map(static function($category){
                return $category['qtySold'];
            },$model['categories']);
            $model['qtySold'] = array_sum($qtySolds);


            $model['maxAmount']= (empty($amounts))?null:max($amounts);
            $model['maxProfit']= (empty($profits))?null:max($profits);
            $model['maxQty']= (empty($qtySolds))?null:max($qtySolds);

            $model['minAmount']= (empty($amounts))?null:min($amounts);
            $model['minProfit']= (empty($profits))?null:min($profits);
            $model['minQty']= (empty($qtySolds))?null:min($qtySolds);




        //breadcumb
        $model['entity'] = 'controller.performance.productCategory.entity';
        $model['page'] = 'controller.performance.productCategory.page';
        return $this->render('performance/performanceProductCategory.html.twig', $model);
    }
}
