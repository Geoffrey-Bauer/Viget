<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Ticket;
use App\Entity\TicketResponse;
use App\Entity\User;
use App\Form\TicketResponseType;
use App\Repository\OrderRepository;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[isGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'app_admin_dashboard')]
    public function index(UserRepository $userRepository, OrderRepository $orderRepository, TicketRepository $ticketRepository): Response
    {
        $user = $this->getUser();

        // Récupération des statistiques
        $totalUsers = $userRepository->count([]);
        $totalOrders = $orderRepository->count([]);
        $totalTickets = $ticketRepository->count([]);
        $totalRevenue = $orderRepository->getTotalRevenue(); // Assurez-vous d'avoir cette méthode dans votre OrderRepository

        return $this->render('admin/index.html.twig', [
            'user' => $user,
            'totalUsers' => $totalUsers,
            'totalOrders' => $totalOrders,
            'totalTickets' => $totalTickets,
            'totalRevenue' => number_format($totalRevenue, 2, ',', ' ') . ' €', // Formatage du revenu
        ]);
    }

    // ----------------- USER -------------------
    #[Route('/users', name: 'app_admin_users')]
    public function users (UserRepository $userRepository) : Response
    {
        $users = $userRepository->findAll();
        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/users/{id}', name: 'app_admin_user_details')]
    public function userDetails(User $user, TicketRepository $ticketRepository, OrderRepository $orderRepository): Response
    {
        // Récupérer les tickets et commandes de l'utilisateur
        $tickets = $ticketRepository->findBy(['user' => $user]);
        $orders = $orderRepository->findBy(['user' => $user]);

        return $this->render('admin/user_details.html.twig', [
            'user' => $user,
            'tickets' => $tickets,
            'orders' => $orders,
        ]);
    }

    #[Route('/users/{id}/change-role', name: 'app_admin_user_change_role')]
    public function changeUserRole(User $user, EntityManagerInterface $entityManager): RedirectResponse
    {
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $user->setRoles(['ROLE_USER']);
        } else {
            $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        }
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/users/{id}/toggle-ban', name: 'app_admin_user_toggle_ban', methods: ['POST'])]
    public function toggleUserBan(User $user, EntityManagerInterface $entityManager): RedirectResponse
    {
        // Inverser l'état de bannissement
        $isBanned = !$user->isBanned();
        $user->setBanned($isBanned);

        // Si l'utilisateur est banni, définir la date et l'heure de bannissement
        if ($isBanned) {
            // Définir le fuseau horaire à Paris (CET/CEST)
            $user->setBanDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
        } else {
            $user->setBanDate(null); // Réinitialise la date de bannissement si l'utilisateur est débanni
        }

        // Enregistrer les modifications dans la base de données
        $entityManager->flush();

        $status = $isBanned ? 'banni' : 'débanni';
        $this->addFlash('success', "L'utilisateur a été {$status}.");

        return $this->redirectToRoute('app_admin_users');
    }

    // ----------------- TICKET -------------------
    #[Route('/tickets', name: 'app_admin_tickets')]
    public function tickets(EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les tickets avec les utilisateurs associés
        $allTickets = $entityManager->getRepository(Ticket::class)->findAll();

        // Séparer les tickets par statut
        $pendingTickets = [];
        $answeredTickets = [];
        $closedTickets = [];

        foreach ($allTickets as $ticket) {
            if ($ticket->getStatus() === 'En attente') {
                $pendingTickets[] = $ticket;
            } elseif ($ticket->getStatus() === 'Réponse de l\'équipe') {
                $answeredTickets[] = $ticket;
            } elseif ($ticket->getStatus() === 'Clôturé') {
                $closedTickets[] = $ticket;
            }
        }

        return $this->render('admin/ticket_management.html.twig', [
            'pendingTickets' => $pendingTickets,
            'answeredTickets' => $answeredTickets,
            'closedTickets' => $closedTickets,
        ]);
    }

    #[Route('/tickets/{id}', name: 'app_admin_ticket_details')]
    public function ticketDetails(Request $request, Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        // Créer un nouveau TicketResponse
        $response = new TicketResponse();

        // Créer et gérer le formulaire
        $form = $this->createForm(TicketResponseType::class, $response);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Associer la réponse au ticket et à l'utilisateur (admin)
            $response->setTicket($ticket);
            $response->setUser($this->getUser()); // Assurez-vous que l'utilisateur est bien un admin
            $response->setResponseDate(new \DateTime());

            // Mettre à jour le statut du ticket à "En attente"
            $ticket->setStatus('Réponse de l\'équipe');

            // Persister la réponse et les changements de statut
            $entityManager->persist($response);
            $entityManager->persist($ticket); // Assurez-vous de persister le ticket après avoir modifié son statut
            $entityManager->flush();

            // Rediriger vers la même page pour voir la nouvelle réponse
            return $this->redirectToRoute('app_admin_ticket_details', ['id' => $ticket->getId()]);
        }

        return $this->render('admin/ticket_detail.html.twig', [
            'ticket' => $ticket,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/tickets/{id}/close', name: 'app_admin_ticket_close', methods: ['POST'])]
    public function closeTicket(Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        // Vérification si le ticket est déjà clôturé
        if ($ticket->getStatus() !== 'Clôturé') {
            // Logique pour clôturer le ticket
            $ticket->setStatus('Clôturé');
            $ticket->setClosingDate(new \DateTime()); // Optionnel : définir la date de clôture

            // Persister les changements dans la base de données
            $entityManager->flush();

            // Message flash pour informer l'administrateur
            $this->addFlash('success', 'Le ticket a été clôturé avec succès.');
        } else {
            // Message flash si le ticket est déjà clôturé
            $this->addFlash('warning', 'Ce ticket est déjà clôturé.');
        }

        return $this->redirectToRoute('app_admin_tickets');
    }

    // ----------------- ORDER -------------------
    #[Route('/order', name: 'app_admin_orders')]
    public function orders(EntityManagerInterface $entityManager): Response
    {
        // Récupérer toutes les commandes par statut
        $pendingOrders = $entityManager->getRepository(Order::class)->findBy(['status' => 'En attente de paiement']);
        $paidOrders = $entityManager->getRepository(Order::class)->findBy(['status' => 'Payé']);
        $deliveredOrders = $entityManager->getRepository(Order::class)->findBy(['status' => 'Livré']);

        return $this->render('admin/order.html.twig', [
            'pendingOrders' => $pendingOrders,
            'paidOrders' => $paidOrders,
            'deliveredOrders' => $deliveredOrders,
        ]);
    }

    #[Route('/order/delete/{id}', name: 'app_admin_order_delete', methods: ['POST'])]
    public function deleteOrder(Order $order, EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les tickets associés à cette commande
        $tickets = $entityManager->getRepository(Ticket::class)->findBy(['purchase' => $order]);

        // Supprimer chaque ticket et ses réponses associées
        foreach ($tickets as $ticket) {
            // Récupérer les réponses associées au ticket
            $responses = $ticket->getTicketResponses();

            // Supprimer les réponses
            foreach ($responses as $response) {
                $entityManager->remove($response);
            }

            // Supprimer le ticket
            $entityManager->remove($ticket);
        }

        // Supprimer la commande
        $entityManager->remove($order);
        $entityManager->flush();

        // Message flash pour informer l'utilisateur
        $this->addFlash('success', 'La commande et ses tickets ont été supprimés avec succès.');

        return $this->redirectToRoute('app_admin_orders');
    }

    #[Route('/order/deliver/{id}', name: 'app_admin_order_deliver', methods: ['POST'])]
    public function deliverOrder(Order $order, EntityManagerInterface $entityManager): Response
    {
        // Mettre à jour le statut de la commande à "Livré"
        if ($order->getStatus() === 'Payé') {
            $order->setStatus('Livré');
            $entityManager->flush();

            // Message flash pour informer l'utilisateur
            $this->addFlash('success', 'La commande a été marquée comme livrée.');
        }

        return $this->redirectToRoute('app_admin_orders');
    }
}
