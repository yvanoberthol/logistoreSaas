<?php

namespace App\Controller;

use App\Entity\Setting;
use App\Entity\Store;
use App\Form\StoreType;
use App\Repository\StoreRepository;
use App\Service\StoreService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StoreController extends AbstractController
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
     * @Route("/store", name="store_index")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function index(Request $request,
                          EntityManagerInterface $entityManager): Response
    {


        $store =$this->store;

        $form = $this->createForm(StoreType::class,$store);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $entityManager->persist($store);
            $entityManager->flush();
            $this->addFlash('success',"controller.store.index.flash.success");
        }

        $model['form'] = $form->createView();

        //dd($model['form']);
        //breadcumb
        $model['entity'] = 'controller.store.index.entity';
        $model['page'] = 'controller.store.index.page';
        return $this->render('store/new.html.twig',$model);
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
