<?php

namespace App\Controller;

use App\Entity\Offer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Récupérer toutes les offres depuis la base de données
        $offers = $entityManager->getRepository(Offer::class)->findAll();

        return $this->render('home/index.html.twig', [
            'offers' => $offers,
        ]);
    }
}