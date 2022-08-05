<?php

namespace App\Controller;

use App\Dto\InstallationDto;
use App\Entity\Language;
use App\Entity\LossType;
use App\Entity\Package;
use App\Entity\PageSize;
use App\Entity\PaymentMethod;
use App\Entity\Permission;
use App\Entity\Store;
use App\Entity\Role;
use App\Entity\Setting;
use App\Entity\Subscription;
use App\Entity\User;
use App\Repository\ProductionRepository;
use App\Repository\ProductRepository;
use App\Repository\LossRepository;
use App\Repository\RawMaterialRepository;
use App\Repository\SubscriptionRepository;
use App\Repository\StockRepository;
use App\Repository\SaleRepository;
use App\Util\GlobalConstant;
use App\Util\PackageConstant;
use App\Util\RoleConstant;
use App\Util\SecurityUtil;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class HomeController extends AbstractController
{

    /**
     * @var Store
     */
    private $store;

    /**
     * ExpenseController constructor.
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->store = $requestStack->getSession()->get('store');
    }

    /**
     * @Route("/", name="home", methods={"GET","POST"})
     * @param Request $request
     * @param SaleRepository $saleRepository
     * @param ProductRepository $productRepository
     * @param SubscriptionRepository $subscriptionRepository
     * @param LossRepository $lossRepository
     * @param StockRepository $stockRepository
     * @return Response
     */
    public function index(Request $request,SaleRepository $saleRepository,
                          ProductRepository $productRepository,
                          SubscriptionRepository $subscriptionRepository,
                          LossRepository $lossRepository,
                          StockRepository $stockRepository): Response
    {

        //breadcumb
        $model['entity'] = 'controller.home.index.entity';
        $model['page'] = 'controller.home.index.page';

        $user = $this->getUser();
        if(!$user){
            return $this->redirectToRoute('account_login');
        }


        $model['user'] = $user;

        $model = GlobalConstant::getMonthsAndYear($request,$model);

        if ($user->getRole()->getRank() === 1){
            $model['saleStats'] = $saleRepository->getSaleByYear($model['year'],$user,$this->store);
            $model['sales'] = $saleRepository->countAll($user);

            return $this->render('home/homeCashier.html.twig',$model);
        }

        $model['saleStats'] = $saleRepository
            ->getSaleByYear($model['year'],null,$this->store);

        $model['sales'] = $saleRepository->countAll(null,$this->store);
        $model['products'] = $productRepository->countAll($this->store);
        $model['losses'] = $lossRepository->countAll($this->store);
        $model['orders'] = $stockRepository->countAll($this->store);

        $model['subscription'] = $subscriptionRepository->get($this->store);

        $model['saleByProducts'] = $productRepository
            ->saleByProduct($model['monthNow'],$model['year'],$this->store);

        return $this->render('home/home.html.twig',$model);
    }

    /**
     * @Route("/documentation", name="documentation")
     */
    public function documentation(): Response
    {
        return $this->render('docs/documentation.html.twig');
    }
}
