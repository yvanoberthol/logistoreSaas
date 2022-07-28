<?php


namespace App\Controller;


use App\Entity\Store;
use App\Repository\NoticeBoardEmployeeRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class GlobalController extends AbstractController
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
     * @Route("/imageLogo", name="imageLogo")
     * @param Request $request
     * @return Response
     */
    public function imageLogo(Request $request)
    {
        $model['height'] = $request->get('height') ?? 100;
        $model['width'] = $request->get('width') ?? 100;
        $model['store'] = $this->store;
        return $this->render('partials/logo.html.twig',$model);
    }


    /**
     * @Route("/notice", name="notice")
     * @param NoticeBoardEmployeeRepository $noticeBoardEmployeeRepository
     * @return Response
     */
    public function notice(NoticeBoardEmployeeRepository $noticeBoardEmployeeRepository)
    {
        $model['noticeEmployees'] = $noticeBoardEmployeeRepository
            ->findByUser($this->getUser(),false,$this->store);
        return $this->render('partials/notice.html.twig',$model);
    }


    /**
     * @Route("/titleStore", name="titleStore")
     * @return Response
     */
    public function titleStore()
    {

        $model['store'] = $this->store;
        return $this->render('partials/titleStore.html.twig',$model);
    }

    /**
     * @Route("/linkStore", name="linkStore")
     * @return Response
     */
    public function linkStore()
    {
        $model['store'] = $this->store;
        return $this->render('partials/linklogo.html.twig',$model);
    }

    /**
     * @Route("/nbProduct", name="nbProduct")
     * @param ProductRepository $productRepository
     * @return Response
     */
    public function nbProduct(ProductRepository $productRepository)
    {
        $model['nbProducts'] = $productRepository->countAll($this->store);
        return new Response($model['nbProducts']);
    }
}
