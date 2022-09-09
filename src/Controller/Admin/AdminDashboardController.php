<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\Category;
use App\Entity\Quotes;

class AdminDashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // Rendre la page contenant le panneau d'administration
        return $this->render('admin/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        // Créer le panneau d'administration
        return Dashboard::new()
            // Mettre comme titre du tableau Quote Machine
            ->setTitle('Quote Machine');
    }

    public function configureMenuItems(): iterable
    {
        // Rajouter le lien vers la page d'accueil du panneau d'administration
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        // Rajouter le crud des utilisateurs via Admin/UserCrudController
        yield MenuItem::linkToCrud('Utilisateurs', 'fa-solid fa-user', User::class);
        // Rajouter le crud des citations via Admin/QuotesCrudController
        yield MenuItem::linkToCrud('Citations', 'fas fa-list', Quotes::class);
        // Rajouter le crud des catégories via Admin/CategoryController
        yield MenuItem::linkToCrud('Catégories', 'fa-sharp fa-solid fa-user-secret', Category::class);
        // Rajouter un lien qui ramène vers le site
        yield MenuItem::linkToRoute('Retour au site', 'fa-solid fa-xmark', 'home');
        // Rajouter un lien qui déconnecte l'utilisateur
        yield MenuItem::linkToLogout('Déconnexion', 'fa-solid fa-xmark');
    }
}
