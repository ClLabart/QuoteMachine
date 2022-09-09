<?php

namespace App\Controller;

use App\Entity\Quotes;
use App\Form\QuoteFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;

class QuoteMachineController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        //Redirection vers la page d'affichage des citations
        return $this->redirectToRoute('quote_machine', [], 301);
    }
    
    #[Route('/quote', name: 'quote_machine')]
    public function quoteMachine(ManagerRegistry $doctrine, PaginatorInterface $paginator, Request $request): Response
    {
        // Rechercher toutes les citations dans la BDD
        $quote = $doctrine->getRepository(Quotes::class)->findAll();
        $request = Request::createFromGlobals();

        //search est le paramètre passé par le formulaire dans la navbar
        if ($research = $request->query->get('search')) {
            //findBySearchField est une fonction SQL 'LIKE %search%' dans QuotesRepository
            $quote = $doctrine->getRepository(Quotes::class)->findBySearchField($research);
        }
        
        //Gestion des cas où il n'y a pas de citations de trouvées
        if (!$quote) {
            $this->addFlash('error','Il n\'y a pas de citations');
            if(!$research) {
                return $this->render('error.html.twig');
            }
            return $this->render('error.html.twig', [
                'research' => $research,
            ]);
        }

        //Initialise la pagination
        $quote = $paginator->paginate(
            $quote, //La requête 
            $request->query->getInt('page', 1), //page est le paramètre GET et 1 la page de départ
            4 //La limite de quotes affichées par pages
        );
        //Modifie la mise en forme de la pagination
        $quote->setTemplate('Pagination/pagination.html.twig');
        
        //Afficher la page
        return $this->render('quote_machine/index.html.twig', [
            'quotes' => $quote,
            'research' => $research,
        ]);
    }
    
    #[Route('/quote/random', name: 'quote_machine_random')]
    public function quoteMachineRandom(ManagerRegistry $doctrine): Response
    {
        $quotes = $doctrine->getRepository(Quotes::class)->findAll();

        //Génère un nombre aléatoire de 0 (début du tableau) jusqu'à la taille du tableau (-1 le premier élément étant 0)
        $quoteNum = rand(0, count($quotes) - 1);

        //Redirection vers la page d'affichage d'une seule citation avec l'id aléatoire
        return $this->redirectToRoute('quote_machine_id', ['id' => $quotes[$quoteNum]->getId()]);
    }
    
    #[Route('/quote/create', name: 'quote_machine_create')]
    public function quoteMachineCreate(Request $request, EntityManagerInterface $entityManager): Response
    {
        //Vérification qu'un utilisateur est bien connecté
        $user = $this->getUser();
        
        if (!$user) {
            // Redirection dans le cas où l'utilisateur n'est pas connecté
            return $this->redirectToRoute('quote_machine'); 
        }
        
        // Création de la nouvelle citation et du formulaire associé
        $quote = new Quotes();
        $form = $this->createForm(QuoteFormType::class, $quote);
        $form->handleRequest($request);
        
        // Le formulaire est soumit et validé
        if ($form->isSubmitted() && $form->isValid()) {
            // Quel utilisateur créer la citation
            $quote->setUserQuoting($user);
            // Heure de création de la citation
            $quote->setCreatedAt(new \DateTimeImmutable('now'));
            
            // Sauvegarder la création
            $entityManager->persist($quote);
            $entityManager->flush();
            
            // Redirection vers la citation créée
            return $this->redirectToRoute('quote_machine_id', ['id' => $quote->getId()]);
        }
        
        // Rendre la page de création avec le formulaire et la variable buttonText pour faire correspondre la page entre création et modification
        return $this->render('quote_machine/create.html.twig', [
            'quoteForm' => $form->createView(),
            'buttonText' => 'Créer'
        ]);
    }
    
    #[Route('/quote/{id}/edit', name: 'quote_machine_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, ManagerRegistry $doctrine, int $id): Response
    {
        //Vérification qu'un utilisateur est bien connecté
        $quote = $doctrine->getRepository(Quotes::class)->find($id);
        $user = $this->getUser();

        if (!$user) {
            // Redirection dans le cas où l'utilisateur n'est pas connecté
            return $this->redirectToRoute('quote_machine_id', ['id' => $id]); 
        } elseif ($user !== $quote->getUserQuoting() && !in_array('ROLE_ADMIN', $user->getRoles())) {
            // Redirection dans le cas où l'utilisateur n'est pas à l'origine de la citation ou n'as pas le rôle admin
            return $this->redirectToRoute('quote_machine_id', ['id' => $id]); 
        }

        // modification de la citation et création du formulaire associé
        $form = $this->createForm(QuoteFormType::class, $quote);
        $form->handleRequest($request);
        
        // Le formulaire est soumit et validé
        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarder les modifications
            $entityManager->persist($quote);
            $entityManager->flush();
            
            // Redirection vers la citation une fois celle-ci modifiée
            return $this->redirectToRoute('quote_machine_id', ['id' => $quote->getId()]);
        }
        
        // Rendre la page de modification avec le formulaire et la variable buttonText pour faire correspondre la page entre création et modification
        return $this->render('quote_machine/create.html.twig', [
            'quoteForm' => $form->createView(),
            'buttonText' => 'Modifier',
        ]);
    }

    #[Route('/quote/{id}/delete', name: 'quote_machine_delete')]
    public function delete(ManagerRegistry $doctrine, int $id): Response
    {
        //Vérification qu'un utilisateur est bien connecté
        $quote = $doctrine->getRepository(Quotes::class)->find($id);
        $user = $this->getUser();
        
        if (!$user) {
            // Redirection dans le cas où l'utilisateur n'est pas connecté
            return $this->redirectToRoute('quote_machine_id', ['id' => $id]); 
        } elseif ($user !== $quote->getUserQuoting() && !in_array('ROLE_ADMIN', $user->getRoles())) {
            // Redirection dans le cas où l'utilisateur n'est pas à l'origine de la citation ou n'as pas le rôle admin
            return $this->redirectToRoute('quote_machine_id', ['id' => $id]); 
        }
        
        // Suppression de la citation
        $entityManager = $doctrine->getManager();
        $entityManager->remove($quote);
        $entityManager->flush();
        
        // Redirection vers la page d'accueil
        return $this->redirectToRoute('quote_machine', []); 
    }

    #[Route('/quote/{id}/like', name: 'quote_machine_like')]
    public function quoteMachineLike(ManagerRegistry $doctrine, int $id): Response
    {
        //Vérification qu'un utilisateur est bien connecté
        $quote = $doctrine->getRepository(Quotes::class)->find($id);
        $user = $this->getUser();

        if (!$user) {
            // Redirection dans le cas où l'utilisateur n'est pas connecté
            return $this->redirectToRoute('quote_machine_id', ['id' => $id]); 
        }
        
        // Ajout d'un j'aime à la citation de la part de l'utilisateur
        $quote->addLike($user);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($quote);
        $entityManager->flush();
        
        // Redirection vers la citation
        return $this->redirectToRoute('quote_machine_id', ['id' => $id]); 
    }
    
    #[Route('/quote/{id}/unlike', name: 'quote_machine_unlike')]
    public function quoteMachineUnlike(ManagerRegistry $doctrine, int $id): Response
    {
        //Vérification qu'un utilisateur est bien connecté
        $quote = $doctrine->getRepository(Quotes::class)->find($id);
        $user = $this->getUser();
        
        if (!$user) {
            // Redirection dans le cas où l'utilisateur n'est pas connecté
            return $this->redirectToRoute('quote_machine_id', ['id' => $id]); 
        }
        
        // Suppression d'un j'aime de la part de l'utilisateur
        $quote->removeLike($user);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($quote);
        $entityManager->flush();
        
        // Redirection vers la citation
        return $this->redirectToRoute('quote_machine_id', ['id' => $id]); 
    }
    
    #[Route('/quote/{id}', name: 'quote_machine_id')]
    public function quoteMachineId(ManagerRegistry $doctrine, int $id): Response
    {
        // Trouver la citation dans la BDD via son ID passé dans l'url
        $quote = $doctrine->getRepository(Quotes::class)->find($id);

        // Rendre la page d'affichage de la citation
        return $this->render('quote_machine/unique.html.twig', [
            'quote' => $quote
        ]);
    }
}
