<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Offer;
use App\Entity\Ticket;
use App\Entity\TicketResponse;
use App\Form\TicketResponseType;
use App\Form\TicketType;
use App\Form\UserType;
use App\Repository\OfferRepository;
use App\Repository\OrderRepository;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry; // Importer ManagerRegistry
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(OrderRepository $orderRepository, TicketRepository $ticketRepository): Response
    {
        $user = $this->getUser();

        // Vérifiez si l'utilisateur est connecté et s'il n'est pas banni
        if (!$user || $user->isBanned()) {
            // Redirigez vers la page d'accueil si l'utilisateur n'est pas connecté ou est banni
            return $this->redirectToRoute('app_home');
        }

        // Récupération des commandes de l'utilisateur
        $orders = $orderRepository->findBy(['user' => $user]);

        // Récupération des tickets de l'utilisateur
        $tickets = $ticketRepository->findBy(['user' => $user]);

        // Vérification des tickets en attente de réponse
        $pendingTickets = array_filter($tickets, function($ticket) {
            return $ticket->getStatus() === 'En attente';
        });

        // Compter les articles dans le panier (exemple)
        // Ici, nous allons compter les commandes qui sont en attente de paiement
        $pendingOrdersCount = count(array_filter($orders, function(Order $order) {
            return $order->getStatus() === 'En attente de paiement';
        }));

        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'user' => $user,
            'orders' => $orders,
            'tickets' => $tickets,
            'pending_tickets' => $pendingTickets,
            'cartItemCount' => $pendingOrdersCount,
        ]);
    }

    // ------------------------------- NOS PRODUITS -----------------------------
    #[Route('/dashboard/products', name: 'app_products')]
    public function products(OfferRepository $offerRepository, OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();

        // Récupérer toutes les offres
        $offers = $offerRepository->findAll();

        // Compter les commandes en attente de paiement pour le compteur de panier
        if ($user) {
            $orders = $orderRepository->findBy(['user' => $user]);
            // Compter les commandes en attente de paiement
            $cartItemCount = count(array_filter($orders, function(Order $order) {
                return $order->getStatus() === 'En attente de paiement';
            }));
        } else {
            // Si l'utilisateur n'est pas connecté, on initialise à 0
            $cartItemCount = 0;
        }

        return $this->render('dashboard/products.html.twig', [
            'offers' => $offers,
            'user' => $user,
            'cartItemCount' => $cartItemCount, // Passer le compteur au template
        ]);
    }

    //------------------------- COMMANDES ----------------------------
    #[Route('/dashboard/orders', name: 'app_orders')]
    public function order(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Utilisateur non connecté.');
        }

        $orders = $entityManager->getRepository(Order::class)->findBy(['user' => $user], ['orderDate' => 'DESC']);

        // Calculer cartItemCount
        $cartItemCount = count(array_filter($orders, function($order) {
            return $order->getStatus() === 'En attente de paiement';
        }));

        $groupedOrders = [
            'En attente de paiement' => [],
            'Payé' => [],
            'Livré' => [],
            'Annulé' => []
        ];

        foreach ($orders as $order) {
            $groupedOrders[$order->getStatus()][] = $order;
        }

        return $this->render('dashboard/order.html.twig', [
            'groupedOrders' => $groupedOrders,
            'cartItemCount' => $cartItemCount, // Ajouter cette ligne
            'user' => $user,
        ]);
    }

    //------------------------- PANIER ----------------------------
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/dashboard/add-to-cart/{id}', name: 'app_add_to_cart')]
    public function addToCart(Offer $offer, ManagerRegistry $doctrine): Response
    {
        // Vérifiez que l'utilisateur est connecté
        if (!$this->getUser()) {
            return new Response('Utilisateur non connecté.', 403);
        }

        // Créer une nouvelle commande avec l'offre sélectionnée
        $order = new Order();
        $order->setOrderDate(new \DateTime());
        $order->setStatus('En attente de paiement');

        // Associer la commande à l'utilisateur et à l'offre
        $user = $this->getUser();
        $order->setUser($user);
        $order->setOffer($offer);

        // Définir le montant total de la commande
        $order->setTotalAmount($offer->getMonthlyPrice()); // Utiliser le prix de l'offre

        // Enregistrer la commande dans la base de données
        $entityManager = $doctrine->getManager();
        $entityManager->persist($order);
        $entityManager->flush();

        // Ajouter un message flash de succès
        $this->addFlash('success', sprintf('%s a été ajouté au panier.', $offer->getName()));

        // Rediriger vers la page des produits
        return $this->redirectToRoute('app_products');
    }

    #[Route('/dashboard/remove-from-cart/{id}', name: 'app_remove_from_cart')]
    public function removeFromCart(OrderRepository $orderRepository, ManagerRegistry $doctrine, int $id): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return new Response('Utilisateur non connecté.', Response::HTTP_FORBIDDEN);
        }

        // Récupérer la commande par ID
        $order = $orderRepository->find($id);

        if ($order && $order->getUser() === $user) {
            // Supprimer la commande
            $entityManager = $doctrine->getManager();
            $entityManager->remove($order);
            $entityManager->flush();

            // Ajouter un message flash de succès
            $this->addFlash('success', sprintf('%s a été retiré du panier.', $order->getOffer()->getName()));

            return $this->redirectToRoute('app_cart');
        }

        // Ajouter un message flash d'erreur si la commande n'est pas trouvée
        $this->addFlash('error', 'Commande non trouvée ou accès non autorisé.');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/dashboard/cart', name: 'app_cart')]
    public function cart(OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return new Response('Utilisateur non connecté.', 403);
        }

        // Récupérer toutes les commandes en attente de paiement pour l'utilisateur
        $orders = $orderRepository->findBy([
            'user' => $user,
            'status' => 'En attente de paiement'
        ]);

        return $this->render('dashboard/cart.html.twig', [
            'orders' => $orders,
            'cartItemCount' => count($orders), // Compte des articles dans le panier
            'user' => $user,
        ]);
    }

    #[Route('/dashboard/checkout', name: 'app_checkout')]
    public function checkout(OrderRepository $orderRepository, Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return new Response('Utilisateur non connecté.', Response::HTTP_FORBIDDEN);
        }

        // Récupérer toutes les commandes en attente de paiement pour l'utilisateur
        $orders = $orderRepository->findBy([
            'user' => $user,
            'status' => 'En attente de paiement'
        ]);

        if (empty($orders)) {
            return new Response('Aucune commande à payer.', Response::HTTP_BAD_REQUEST);
        }

        // Calculer le montant total à payer (en centimes)
        $totalAmount = array_reduce($orders, function($carry, Order $order) {
            return $carry + ($order->getTotalAmount() * 100); // Convertir en centimes
        }, 0);

        \Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']); // Définir la clé secrète

        // Obtenir l'URL de base
        $baseUrl = $request->getSchemeAndHttpHost();

        // Si nous sommes en environnement de développement, utiliser l'URL ngrok
        if ($_ENV['APP_ENV'] === 'dev') {
            $baseUrl = 'http://localhost'; // Remplacez par votre URL ngrok
        }

        // Générer les URLs
        $successUrl = $baseUrl . $this->generateUrl('app_success');
        $cancelUrl = $baseUrl . $this->generateUrl('app_cart');

        // Log des URLs générées
        $this->logger->info("Success URL: " . $successUrl);
        $this->logger->info("Cancel URL: " . $cancelUrl);

        try {
            // Créer une session de paiement Stripe
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => 'Panier d\'achats',
                        ],
                        'unit_amount' => $totalAmount,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ]);

            return $this->redirect($session->url); // Rediriger vers la page de paiement Stripe
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la création de la session Stripe: ' . $e->getMessage());
            return new Response('Une erreur est survenue lors de la préparation du paiement.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/dashboard/success', name: 'app_success')]
    public function success(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Utilisateur non connecté.');
        }

        $orders = $entityManager->getRepository(Order::class)->findBy([
            'user' => $user,
            'status' => 'En attente de paiement'
        ]);

        foreach ($orders as $order) {
            $order->setStatus('Payé');
        }
        $entityManager->flush();

        return $this->render('dashboard/payment/success.html.twig', [
            'message' => 'Paiement effectué avec succès !',
            'redirect_url' => $this->generateUrl('app_dashboard'),
            'redirect_time' => 30,
        ]);
    }

    #[Route('/dashboard/cancel', name: 'app_cancel')]
    public function cancel(): Response
    {
        // Le statut des commandes reste inchangé en cas d'annulation

        return $this->render('dashboard/payment/cancel.html.twig', [
            'message' => 'Le paiement a été annulé.',
            'redirect_url' => $this->generateUrl('app_dashboard'),
            'redirect_time' => 30,
        ]);
    }

    //------------------------- TICKET ----------------------------
    #[Route('/dashboard/tickets', name: 'app_tickets')]
    public function tickets(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $tickets = $entityManager->getRepository(Ticket::class)->findBy(['user' => $user], ['creationDate' => 'DESC']);

        // Calculer cartItemCount
        $cartItemCount = $entityManager->getRepository(Order::class)->count([
            'user' => $user,
            'status' => 'En attente de paiement'
        ]);

        return $this->render('dashboard/ticket.html.twig', [
            'tickets' => $tickets,
            'user' => $user,
            'cartItemCount' => $cartItemCount,
        ]);
    }

    #[Route('/dashboard/ticket/{id}', name: 'app_ticket_detail')]
    public function ticketDetail(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Charger le ticket avec la commande associée
        $ticket = $entityManager->getRepository(Ticket::class)->findTicketWithPurchase($id);

        if (!$ticket || $ticket->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à voir ce ticket.');
        }

        // Créer un nouveau TicketResponse
        $response = new TicketResponse();
        $form = $this->createForm(TicketResponseType::class, $response);
        $form->handleRequest($request);

        // Calculer cartItemCount
        $cartItemCount = $entityManager->getRepository(Order::class)->count([
            'user' => $user,
            'status' => 'En attente de paiement'
        ]);

        if ($form->isSubmitted() && $form->isValid()) {
            // Associer la réponse au ticket et à l'utilisateur
            $response->setUser($user);
            $response->setTicket($ticket);
            $response->setResponseDate(new \DateTime());

            // Mettre à jour le statut du ticket à "En attente"
            if ($ticket->getStatus() !== 'Clôturé') {
                $ticket->setStatus('En attente');
            }

            // Persister les changements
            $entityManager->persist($response);
            $entityManager->persist($ticket); // Assurez-vous de persister le ticket après avoir modifié son statut
            $entityManager->flush();

            return $this->redirectToRoute('app_ticket_detail', ['id' => $ticket->getId()]);
        }

        return $this->render('dashboard/ticket_detail.html.twig', [
            'ticket' => $ticket,
            'form' => $form->createView(),
            'user' => $user,
            'cartItemCount' => $cartItemCount,
        ]);
    }

    #[Route('/dashboard/create-ticket', name: 'app_create_ticket')]
    public function createTicket(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour créer un ticket.');
        }

        $ticket = new Ticket();
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Associer l'utilisateur au ticket
            $ticket->setUser($user);
            $ticket->setCreationDate(new \DateTime());
            $ticket->setStatus('En attente');

            // Si une commande est associée, définissez-la
            if ($ticket->getPurchase()) { // Vérifiez si une commande a été sélectionnée
                $ticket->setPurchase($ticket->getPurchase());
            }

            // Persist the ticket
            $entityManager->persist($ticket);
            $entityManager->flush();

            return $this->redirectToRoute('app_ticket_detail', ['id' => $ticket->getId()]);
        }

        // Récupérer les commandes de l'utilisateur
        $orders = $entityManager->getRepository(Order::class)->findBy(['user' => $user]);

        // Calculer cartItemCount
        $cartItemCount = $entityManager->getRepository(Order::class)->count([
            'user' => $user,
            'status' => 'En attente de paiement'
        ]);

        return $this->render('dashboard/create_ticket.html.twig', [
            'form' => $form->createView(),
            'orders' => $orders,
            'cartItemCount' => $cartItemCount,
            'user' => $user,
        ]);
    }

    #[Route('/dashboard/close-ticket/{id}', name: 'app_close_ticket')]
    public function closeTicket(Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        if ($ticket->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à fermer ce ticket.');
        }

        $ticket->setStatus('Clôturé');
        $ticket->setClosingDate(new \DateTime());

        $entityManager->flush();

        $this->addFlash('success', 'Le ticket a été clôturé avec succès.');

        return $this->redirectToRoute('app_tickets');
    }

    //------------------------- PARAMETER USER ----------------------------
    #[Route('/user/settings', name: 'app_user_settings')]
    public function settings(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        // Initialiser $cartItemCount en dehors du bloc if
        $cartItemCount = 0;

        // Compter les commandes en attente de paiement pour le compteur de panier
        if ($user) {
            $orders = $orderRepository->findBy(['user' => $user]);
            // Compter les commandes en attente de paiement
            $cartItemCount = count(array_filter($orders, function($order) {
                return $order->getStatus() === 'En attente de paiement';
            }));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $currentPassword = $form->get('currentPassword')->getData();

            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('error', 'Le mot de passe actuel est incorrect.');
                return $this->redirectToRoute('app_user_settings');
            }

            $newPassword = $form->get('newPassword')->getData();
            if ($newPassword) {
                $confirmNewPassword = $form->get('confirmNewPassword')->getData();
                if ($newPassword !== $confirmNewPassword) {
                    $this->addFlash('error', 'Les nouveaux mots de passe ne correspondent pas.');
                    return $this->redirectToRoute('app_user_settings');
                }
                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            }

            $entityManager->flush();
            $this->addFlash('success', 'Vos paramètres ont été mis à jour avec succès.');
            return $this->redirectToRoute('app_user_settings');
        }

        return $this->render('dashboard/settings.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'cartItemCount' => $cartItemCount,
        ]);
    }
}