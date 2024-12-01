<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Repository\OfferRepository;
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

    //----------- PRODUCTS ----------
    #[Route('/products', name: 'app_offers')]
    public function offers(OfferRepository $offerRepository): Response
    {
        $offers = $offerRepository->findAll(); // Récupérer toutes les offres
        return $this->render('home/offer.html.twig', [
            'offers' => $offers,
        ]);
    }

    //---------- SERVICE --------------
    #[Route('/services', name: 'app_services')]
    public function services(): Response
    {
        return $this->render('home/service.html.twig');
    }

    //--------- CONTACT -----------------
    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('home/contact.html.twig');
    }
}