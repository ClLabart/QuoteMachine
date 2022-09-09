<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends AbstractController
{
    #[Route('/category', name: 'category')]
    public function index(ManagerRegistry $doctrine): Response
    {
        // Rechercher toutes les categories dans la BDD
        $cat = $doctrine->getRepository(Category::class)->findAll();

        // Gestion des erreurs si aucune catégorie n'a été trouvée
        if (!$cat) {
            $this->addFlash('error', 'Il n\'y a pas de catégories');
            return $this->render('error.html.twig');
        }

        // Rendre la page pour lister les catégories
        return $this->render('category/index.html.twig', [
            'cat' => $cat
        ]);
    }

    #[Route('/category/create', name: 'category_create')]
    public function register(Request $request, EntityManagerInterface $entityManager): Response
    {
        //Vérification qu'un utilisateur est bien connecté
        $user = $this->getUser();

        if (!$user) {
            // Redirection dans le cas où l'utilisateur n'est pas connecté
            return $this->redirectToRoute('category');
        }

        // Création de la nouvelle catégorie et du formulaire associé
        $cat = new Category();
        $form = $this->createForm(CategoryFormType::class, $cat);
        $form->handleRequest($request);

        // Le formulaire est soumit et validé
        if ($form->isSubmitted() && $form->isValid()) {
            // Quel utilisateur créer la catégorie
            $cat->setCreatedBy($user);

            // Sauvegarder la création
            $entityManager->persist($cat);
            $entityManager->flush();

            // Rediriger vers la catégorie créée
            return $this->redirectToRoute('category_id', ['id' => $cat->getId()]);
        }

        // Rendre la page de création avec le formulaire et la variable buttonText pour faire correspondre la page entre création et modification
        return $this->render('category/create.html.twig', [
            'categoryForm' => $form->createView(),
            'buttonText' => 'Créer'
        ]);
    }

    #[Route('/category/{id}/edit', name: 'category_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, ManagerRegistry $doctrine, int $id): Response
    {
        //Vérification qu'un utilisateur est bien connecté
        $cat = $doctrine->getRepository(Category::class)->find($id);
        $user = $this->getUser();

        if (!$user) {
            // Redirection dans le cas où l'utilisateur n'est pas connecté
            return $this->redirectToRoute('category_id', ['id' => $id]);
        } elseif ($user !== $cat->getCreatedBy() && !in_array('ROLE_ADMIN', $user->getRoles())) {
            // Redirection dans le cas où l'utilisateur n'est pas à l'origine de la catégorie ou n'as pas le rôle admin
            return $this->redirectToRoute('category_id', ['id' => $id]);
        }

        // modification de la catégorie et création du formulaire associé
        $form = $this->createForm(CategoryFormType::class, $cat);
        $form->handleRequest($request);

        // Le formulaire est soumit et validé
        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarder les modifications
            $entityManager->persist($cat);
            $entityManager->flush();

            // Redirection vers la citation une fois celle-ci modifiée
            return $this->redirectToRoute('category_id', ['id' => $cat->getId()]);
        }

        // Rendre la page de création avec le formulaire et la variable buttonText pour faire correspondre la page entre création et modification
        return $this->render('category/create.html.twig', [
            'categoryForm' => $form->createView(),
            'buttonText' => 'Modifier'
        ]);
    }

    #[Route('/category/{id}/delete', name: 'category_delete')]
    public function catDelete(ManagerRegistry $doctrine, int $id): Response
    {
        //Vérification qu'un utilisateur est bien connecté
        $cat = $doctrine->getRepository(Category::class)->find($id);
        $user = $this->getUser();

        if (!$user) {
            // Redirection dans le cas où l'utilisateur n'est pas connecté
            return $this->redirectToRoute('category_id', ['id' => $id]);
        } elseif ($user !== $cat->getCreatedBy() && !in_array('ROLE_ADMIN', $user->getRoles())) {
            // Redirection dans le cas où l'utilisateur n'est pas à l'origine de la catégorie ou n'as pas le rôle admin
            return $this->redirectToRoute('category_id', ['id' => $id]);
        }

        // Trouver toutes les citations reliées à la catégorie
        $quotes = $cat->getQuotes();
        $entityManager = $doctrine->getManager();

        // Supprimer toutes les citations reliées à la catégorie
        foreach ($quotes as $quote) {
            $entityManager->remove($quote);
        }

        // Suppression de la catégorie
        $entityManager->remove($cat);
        $entityManager->flush();

        // Redirection vers la page d'accueil
        return $this->redirectToRoute('category');
    }

    #[Route('/category/{id}', name: 'category_id')]
    public function catId(ManagerRegistry $doctrine, int $id): Response
    {
        // Rechercher la catégorie via son ID passé dans l'url
        $cat = $doctrine->getRepository(Category::class)->find($id);

        // Gérer l'erreur si aucune catégorie n'a été trouvée
        if (!$cat) {
            $this->addFlash('error', 'Il n\'y a pas de catégories');
            return $this->render('error.html.twig');
        }

        // Rendre la page d'affichage de la catégorie
        return $this->render('category/cat.html.twig', [
            'cat' => $cat,
            'quotes' => $cat->getQuotes()
        ]);
    }
}
