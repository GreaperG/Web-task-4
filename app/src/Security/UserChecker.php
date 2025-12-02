<?php 


// src/Security/UserChecker.php
namespace App\Security;

use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class UserChecker implements UserCheckerInterface
{
  public function __construct(private EntityManagerInterface $em) {}

    public function checkPreAuth(UserInterface $user): void
    {
    if (!$user instanceof User) {
        return;
    }

    // Проверка существования в базе
    $existingUser = $this->em->getRepository(User::class)->find($user->getId());
    if (!$existingUser) {
        // Завершаем сессию, чтобы Symfony не пытался обновить пользователя
        throw new CustomUserMessageAuthenticationException('Your account no longer exists.');
    }

    if ($existingUser->getStatus() === 'blocked') {
        throw new CustomUserMessageAuthenticationException('Your account is blocked.');
    }
}

    public function checkPostAuth(UserInterface $user) :void {}
    

}













?>