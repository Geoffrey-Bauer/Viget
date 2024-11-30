<?php

namespace App\DataFixtures;

use App\Entity\Offer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class OfferFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $offersData = [
            [
                'name' => 'Basic Plan',
                'processor' => 'Intel Xeon E3-1230 v6',
                'ram' => 8,
                'storage' => 250,
                'bandwidth' => 1000,
                'monthlyPrice' => 29.99,
            ],
            [
                'name' => 'Pro Plan',
                'processor' => 'Intel Xeon E5-1650 v4',
                'ram' => 16,
                'storage' => 500,
                'bandwidth' => 2000,
                'monthlyPrice' => 59.99,
            ],
            [
                'name' => 'Enterprise Plan',
                'processor' => 'Intel Xeon E7-8890 v4',
                'ram' => 32,
                'storage' => 1000,
                'bandwidth' => 5000,
                'monthlyPrice' => 119.99,
            ],
        ];

        foreach ($offersData as $data) {
            $offer = new Offer();
            $offer->setName($data['name']);
            $offer->setProcessor($data['processor']);
            $offer->setRam($data['ram']);
            $offer->setStorage($data['storage']);
            $offer->setBandwidth($data['bandwidth']);
            $offer->setMonthlyPrice($data['monthlyPrice']);

            $manager->persist($offer);
        }

        $manager->flush();
    }
}