<?php

namespace App\Controller;

use App\Entity\Sale;

use App\Entity\Setting;
use App\Entity\Store;
use App\Repository\ProductRepository;
use App\Repository\StoreRepository;
use App\Repository\PaymentMethodRepository;
use App\Repository\ProductSaleRepository;
use App\Repository\SaleRepository;
use App\Repository\StockRepository;
use App\Service\CartService;
use App\Service\ProductService;
use App\Util\GlobalConstant;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Dompdf\Dompdf;
use Exception;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
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
     * @Route("/search/product", name="search_product", methods={"GET","POST"})
     * @param Request $request
     * @param ProductService $productService
     * @param ProductRepository $productRepository
     * @return Response
     * @throws Exception
     */
    public function product(Request $request,
                             ProductService $productService,
                             ProductRepository $productRepository): Response
    {

        if ($request->isMethod('POST')){
            $model['products'] =
                $productService
                    ->countStocks($productRepository
                        ->getBegin($request->get('name'),$this->store),$this->store) ;
        }

        //breadcumb
        $model['entity'] = 'controller.search.product.entity';
        $model['page'] = 'controller.search.product.page';
        return $this->render('search/product.html.twig', $model);
    }


    /**
     * @Route("/search/sale", name="search_sale", methods={"GET","POST"})
     * @param Request $request
     * @param SaleRepository $saleRepository
     * @return Response
     * @throws NonUniqueResultException
     */
    public function sale(Request $request,
                             SaleRepository $saleRepository): Response
    {

        $model['search'] = empty($request->get('sale'))?null:$request->get('sale');
        if ($request->isMethod('POST')){
            if ($model['search'] === null){
                $model['sale'] = null;
            }else{
                $model['sale'] =
                    $saleRepository->findByIdOrNumInvoice($request->get('sale'),$this->store);
            }
        }

        //breadcumb
        $model['entity'] = 'controller.search.sale.entity';
        $model['page'] = 'controller.search.sale.page';
        return $this->render('search/sale.html.twig', $model);
    }

    /**
     * @Route("/search/stock", name="search_stock", methods={"GET","POST"})
     * @param Request $request
     * @param StockRepository $stockRepository
     * @return Response
     */
    public function stock(Request $request,
                          StockRepository $stockRepository): Response
    {

        if ($request->isMethod('POST')){
            $model['stock'] =
                $stockRepository->findOneBy(['numInvoice' => $request->get('numInvoice'),
                    'store'=> $this->store]) ;
        }

        //breadcumb
        $model['entity'] = 'controller.search.stock.entity';
        $model['page'] = 'controller.search.stock.page';
        return $this->render('search/stock.html.twig', $model);
    }
}
