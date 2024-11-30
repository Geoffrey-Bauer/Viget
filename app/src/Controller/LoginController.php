<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UsersType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Vérifiez si l'utilisateur est déjà connecté
        if ($this->getUser()) {
            // Redirigez vers la page d'accueil si l'utilisateur est connecté
            return $this->redirectToRoute('app_home'); // Remplacez 'app_home' par le nom de votre route d'accueil
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        // Vérifiez si l'utilisateur est déjà connecté
        if ($this->getUser()) {
            // Redirigez vers la page d'accueil si l'utilisateur est connecté
            return $this->redirectToRoute('app_home'); // Remplacez 'app_home' par le nom de votre route d'accueil
        }

        $user = new User();
        $form = $this->createForm(UsersType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérez le mot de passe depuis le formulaire
            $plainPassword = $form->get('password')->getData();

            // Hachez le mot de passe et définissez-le sur l'utilisateur
            $user->setPassword(
                $userPasswordHasher->hashPassword($user, $plainPassword)
            );

            // Définir des valeurs par défaut
            $user->setRoles(['ROLE_USER']);
            $user->setBanned(false);

            // Persist and flush the user entity
            $entityManager->persist($user);
            $entityManager->flush();

            // Ajoutez un message flash si nécessaire
            $this->addFlash('success', 'Votre compte a été créé avec succès !');

            // Redirigez vers la page de connexion ou tableau de bord
            return $this->redirectToRoute('app_login');
        }

        return $this->render('login/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}