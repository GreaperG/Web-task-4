<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class VerificationController extends AbstractController
{
    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        $token = $request->query->get('token');
        if(!$token){
            $this->addFlash('error', 'Invalid verification link');
            return $this->redirectToRoute('app_login');
        }

        $user = $userRepository->findOneBy(['emailVerificationToken' => $token]);
        if(!$user) {
            $this->addFlash('error', 'User not found or token invalid');
            return $this->redirectToRoute('app_login');
        }

        $user->setStatus('active');
        $user->setEmailVerificationToken(null);
        $em->flush();

        $this->addFlash('success', 'Email verified successfully! You can now login');
        return $this->redirectToRoute('app_login');
    }
}
