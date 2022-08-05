<?php


namespace App\Suscriber;



use DateTime;
use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class StoreSelectedSubscriber implements EventSubscriberInterface
{

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var SessionInterface
     */
    private $session;
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * SubscriptionSubscriber constructor.
     * @param RouterInterface $router
     * @param TokenStorageInterface $tokenStorage
     * @param SessionInterface $session
     */
    public function __construct(RouterInterface $router,
                                TokenStorageInterface $tokenStorage,
                                SessionInterface $session)
    {
        $this->router = $router;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['processStoreSelected', -5],
        ];
    }

    /**
     * @param RequestEvent $event
     * @throws Exception
     */
    public function processStoreSelected(RequestEvent $event): void
    {

        $allowedPaths = [
            'imageLogo',
            'titleStore',
            'linkStore',
            'notice',
            'nbProduct',
            'account_login',
            'account_register',
            'account_logout',
            'store_select',
        ];

        // use substring to remove lang on the url
        $path = $event->getRequest()->get('_route');


        if (!in_array($path, $allowedPaths, true) &&
                !str_contains($path, 'rest_')&&
                !str_contains($path, 'test_')){


            if ($this->tokenStorage->getToken() !== null){

                $user = $this->tokenStorage->getToken()->getUser();
                if ($user !== null) {

                    $roles = $user->getRules();

                    $stores =$user->getStore();

                    if (!empty($roles) && !empty($stores)){
                        $setting = $this->session->get('setting');
                        $store = $this->session->get('store');

                        if ($path !== 'store_list' && ($setting === null || $store === null)) {
                            $route = $this->router->generate('store_list');
                            $response = new RedirectResponse($route);
                            $event->setResponse($response);
                        }
                    }else{
                        $route =  $this->router->generate('account_logout');
                        $response = new RedirectResponse($route);
                        $event->setResponse($response);
                    }

                }
            }


        }


    }
}
