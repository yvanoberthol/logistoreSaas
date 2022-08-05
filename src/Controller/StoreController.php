<?php

namespace App\Controller;

use App\Entity\Connection;
use App\Entity\LossType;
use App\Entity\Package;
use App\Entity\PageSize;
use App\Entity\PaymentMethod;
use App\Entity\Permission;
use App\Entity\Role;
use App\Entity\Setting;
use App\Entity\Store;
use App\Entity\Subscription;
use App\Entity\Supplier;
use App\Entity\Theme;
use App\Entity\User;
use App\Form\StoreType;
use App\Repository\SettingRepository;
use App\Repository\StoreRepository;
use App\Repository\ThemeRepository;
use App\Service\ProductService;
use App\Service\StoreService;
use App\Util\GlobalConstant;
use App\Util\PackageConstant;
use App\Util\RoleConstant;
use App\Util\SecurityUtil;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;

class StoreController extends AbstractController
{

    /**
     * @var Store
     */
    private $store;

    private $session;
    /**
     * @var StoreService
     */
    private $storeService;

    /**
     * ExpenseController constructor.
     * @param RequestStack $requestStack
     * @param StoreService $storeService
     */
    public function __construct(RequestStack $requestStack, StoreService $storeService)
    {
        $this->session = $requestStack->getSession();
        $this->store = $this->session->get('store');
        $this->storeService = $storeService;
    }

    /**
     * @Route("/store", name="store_index")
     * @return Response
     */
    public function index(): Response
    {

        $model['stores'] = $this->storeService->getStores($this->getUser());

        //dd($model['form']);
        //breadcumb
        $model['entity'] = 'controller.store.index.entity';
        $model['page'] = 'controller.store.index.page';
        return $this->render('store/index.html.twig',$model);
    }

    /**
     * @Route("/store/list", name="store_list")
     * @return Response
     */
    public function list(): Response
    {

        $model['stores'] =$this->storeService->getStores($this->getUser());

        if (empty($model['stores'])){
            return $this->redirectToRoute('account_logout');
        }

        //breadcumb
        $model['entity'] = 'controller.store.list.entity';
        $model['page'] = 'controller.store.list.page';
        return $this->render('store/list.html.twig',$model);
    }


    /**
     * @Route("/store/new", name="store_new")
     * @param Request $request
     * @param StoreService $storeService
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function new(Request $request,
                          StoreService $storeService,
                          EntityManagerInterface $entityManager): Response
    {


        $store = new Store();
        $form = $this->createForm(StoreType::class,$store);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $entityManager->persist($store);
            $storeService->processCreateStore($entityManager,$this->getUser(),$store);

            $entityManager->flush();

            $this->addFlash('success',"controller.store.new.flash.success");
        }

        $model['form'] = $form->createView();

        //dd($model['form']);
        //breadcumb
        $model['entity'] = 'controller.store.new.entity';
        $model['page'] = 'controller.store.new.page';
        return $this->render('store/new.html.twig',$model);
    }

    /**
     * @Route("/store/edit/{id}", name="store_edit")
     * @param Store $store
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function edit(Store $store,
                          Request $request,
                          EntityManagerInterface $entityManager): Response
    {


        $form = $this->createForm(StoreType::class,$store);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $entityManager->persist($store);
            $entityManager->flush();
            $this->addFlash('success',"controller.store.edit.flash.success");
            return $this->redirectToRoute('store_index');
        }

        $model['form'] = $form->createView();

        //dd($model['form']);
        //breadcumb
        $model['entity'] = 'controller.store.edit.entity';
        $model['page'] = 'controller.store.edit.page';
        return $this->render('store/edit.html.twig',$model);
    }

    /**
     * @Route("/store/generateCode/{id}", name="store_generate_code")
     * @param Store $store
     * @param StoreService $storeService
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function generateAppCode(Store $store,
                        StoreService $storeService,
                        EntityManagerInterface $entityManager): Response
    {


        $store->setAppCode($storeService->generateCode($store));
        $entityManager->persist($store);
        $entityManager->flush();

        $this->session->set('store',$store);

        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/store/select/{id}", name="store_select")
     * @param Store $store
     * @param EntityManagerInterface $entityManager
     * @param UserAuthenticatorInterface $authenticator
     * @param FormLoginAuthenticator $formLoginAuthenticator
     * @param SettingRepository $settingRepository
     * @param ProductService $productService
     * @param ThemeRepository $themeRepository
     * @return Response
     * @throws \Exception
     */
    public function select(Store $store,
                           EntityManagerInterface $entityManager,
                           UserAuthenticatorInterface $authenticator,
                           FormLoginAuthenticator $formLoginAuthenticator,
                           SettingRepository $settingRepository,
                           ProductService $productService,
                           ThemeRepository $themeRepository): Response
    {

        $user = $this->getUser();


        // set last user connection
        $user->setLastConnection(new DateTime());

        $connection = new Connection();
        $connection->setUser($user);

        $entityManager->persist($user);
        $entityManager->persist($connection);
        $entityManager->flush();


        $this->session->set('store',$store);


        // manage setting default
        $setting = $settingRepository->get($store);
        $this->session->set('setting',$setting);

        $themeDefault = $themeRepository
            ->find((int) $setting->getThemeId());

        if ($themeDefault !== null){
            $this->session->set('theme',$themeDefault);
        }else{
            $themeDefault = new Theme();
            $themeDefault->setBackcolorSideMenu($_ENV['BACK_COLOR_SIDEMENU']);
            $themeDefault->setColorSideMenuLink($_ENV['BACK_COLOR_SIDEMENU_LINK']);
            $themeDefault->setGeneralColorDark($_ENV['GENERAL_COLOR_DARK']);
            $themeDefault->setGeneralColorLight($_ENV['GENERAL_COLOR_LIGHT']);
            $this->session->set('theme',$themeDefault);
        }

        // set role store
        $user->setRoles($store);
        if ($user->getRole()->getRank() > 1){
            $stockOutOfDateCount = count($productService
                ->getProductStockNearExpirationDate(0,$store));

            $stockExpiryDateCount = count($productService
                ->getProductStockNearExpirationDate($setting->getDaysBeforeExpiration(),$store));

            $this->session->set('stockOutOfDateCount',$stockOutOfDateCount);
            $this->session->set('stockExpiryDateCount',$stockExpiryDateCount);
        }


        // authenticate user with role allowed for the store
        $request = new Request();
        $request->query->set('_login',$user->getUserIdentifier());
        $request->query->set('_password',$user->getPassword());
        $authenticator->authenticateUser($this->getUser(),$formLoginAuthenticator, $request);

        return $this->redirectToRoute('home');
    }


    /**
     * @Route("/store/value", name="store_value")
     * @param StoreService $storeService
     * @return Response
     * @throws \Exception
     */
    public function value(StoreService $storeService): Response
    {


        $model['products']= $storeService->getProductStoreValue($this->store);

        //breadcumb
        $model['entity'] = 'controller.store.value.entity';
        $model['page'] = 'controller.store.value.page';
        return $this->render('store/value.html.twig',$model);
    }

}
