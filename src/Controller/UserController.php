<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/utilisateur/{id}', name: 'app_user')]
    public function index(ManagerRegistry $doctrine, int $id): Response
    {
        // Trouver l'utilisateur cherché dans la BDD
        $userViewed = $doctrine->getRepository(User::class)->find($id);
        
        // Gérer les erreurs si l'utilisateur n'a pas été trouvé
        if (!$userViewed) {
            $this->addFlash('error','Il n\'y a pas d\'utilisateur');
            return $this->render('error.html.twig');
        }

        // Rendre la page pour afficher l'utilisateur demandé via la variable userViewed en twig
        return $this->render('user/index.html.twig', [
            'userViewed' => $userViewed,
        ]);
    }
}
