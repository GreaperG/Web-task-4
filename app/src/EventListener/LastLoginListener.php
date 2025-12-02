<?php
namespace App\EventListener;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use App\Entity\User;

class LastLoginListener
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        /** @var \App\Entity\User $user */
        $user = $event->getAuthenticationToken()->getUser();
        
        if ($user instanceof User) {
            $user->setLastLogin(new \DateTime());
            $this->em->flush();

        }
    }
}








?>