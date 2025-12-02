<?php


namespace App\EventListener;

use App\Entity\User;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;


class UserStatusListener
{
    private RouterInterface $router;
    private TokenStorageInterface $tokenStorage;

    public function __construct(RouterInterface $router, TokenStorageInterface $tokenStorage)
    {
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $excludeRoutes = [
            'app_login',
            'app_register',
            'app_logout',
            'app_verify_email',
        ];

        $route = $request->attributes->get('_route');
        if (!$route || in_array($route, $excludeRoutes, true)) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return;
        }

        $user = $token->getUser();

   
        if (!$user instanceof User) {
            $event->setResponse(new RedirectResponse($this->router->generate('app_login')));
            return;
        }


        if ($user->getStatus() === 'blocked') {
            $event->setResponse(new RedirectResponse($this->router->generate('app_login')));
            return;
        }
    }
}

