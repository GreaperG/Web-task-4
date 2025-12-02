<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/admin/users', name: 'admin_users')]
class UserController extends AbstractController

{
 #[Route('/', name: '')]
 public function index(UserRepository $repo): Response
 {
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
    $user = $this->getUser();
    if($user instanceof \App\Entity\User && $user->getStatus() === 'blocked'){
        return $this->redirectToRoute('app_login');
    }

    $users = $repo->findBy([], ['lastLogin' => 'DESC']);

    return $this->render('admin/user/index.html.twig', ['users' => $users]);
 }

 #[Route('/action', name: '_action', methods:['POST'])]
 public function action(Request $request, UserRepository $repo, EntityManagerInterface $em): Response
{
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
/** @var \App\Entity\User $currentUser */
    $currentUser = $this->getUser();

    if ($currentUser instanceof \App\Entity\User && $currentUser->getStatus() === 'blocked') {
        $this->addFlash('error', 'Ваш аккаунт заблокирован.');
        return $this->redirectToRoute('app_login');
    }
    
    $ids = $request->request->all('users_ids');
    $action = $request->request->get('action');

    if(!$ids){
        $this->addFlash('error', 'No users selected');
        return $this->redirectToRoute('admin_users');
    }
            /** @var \App\Entity\User $user */
    $users = $repo->findBy(['id' => $ids]);

    $actions = [
        'block' => function($user) { 
            $user->setStatus('blocked'); 
        },

        'unblock' => function($user) { 
            $user->setStatus('active'); 
        },

        'delete' => function($user) use ($em) { 
            $em->remove($user); 
        },

        'deleteUnverified' => function($user) use ($em) {
            if ($user->getStatus() === 'unverified') {
                $em->remove($user);
            }
        },
    ];
        if (!isset($actions[$action])) {
        $this->addFlash('error', 'Unknown action');
        return $this->redirectToRoute('admin_users');
    }

    foreach ($users as $user) {
            if ($action === 'delete' && $user->getId() === $currentUser->getId()) {
        $this->container->get('security.token_storage')->setToken(null);
        $request->getSession()->invalidate();
       
    }
    $actions[$action]($user);
    }
    $em->flush();

    switch ($action) {
    case 'block':
        $this->addFlash('success', 'Selected users have been blocked successfully.');
        break;
    case 'unblock':
        $this->addFlash('success', 'Selected users have been unblocked successfully.');
        break;
    case 'delete':
        $this->addFlash('success', 'Selected users have been deleted successfully.');
        break;
    case 'deleteUnverified':
        $this->addFlash('success', 'Selected unverified users have been deleted successfully.');
        break;
}

    return $this->redirectToRoute('admin_users');
}

}