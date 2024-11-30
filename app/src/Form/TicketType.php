<?php

namespace App\Form;

use App\Entity\Ticket;
use App\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('subject', TextType::class, [
                'label' => 'Sujet',
                'attr' => ['placeholder' => 'Entrez le sujet de votre ticket'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['placeholder' => 'Décrivez votre problème en détail'],
            ])
            ->add('purchase', EntityType::class, [ // Changement ici
                'class' => Order::class,
                'choice_label' => function ($order) {
                    return sprintf('Commande #%d - %s', $order->getId(), $order->getOffer()->getName());
                },
                'required' => false,
                'placeholder' => 'Sélectionnez une commande (optionnel)',
                'label' => 'Commande associée',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
        ]);
    }
}