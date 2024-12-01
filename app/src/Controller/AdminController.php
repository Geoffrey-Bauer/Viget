<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\OrderRepository;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[isGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'app_admin_dashboard')]
    public function index(UserRepository $userRepository, OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();

        return $this->render('admin/index.html.twig', [
            'user' => $user,
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
}
