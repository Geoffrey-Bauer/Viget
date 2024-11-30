<?php

namespace App\Entity;

use App\Repository\TicketResponseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketResponseRepository::class)]
class TicketResponse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $responseDate = null;

    #[ORM\ManyToOne(inversedBy: 'ticketResponses')]
    private ?Ticket $ticket = null;

    #[ORM\ManyToOne(inversedBy: 'ticketResponses')]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getResponseDate(): ?\DateTimeInterface
    {
        return $this->responseDate;
    }

    public function setResponseDate(\DateTimeInterface $responseDate): static
    {
        $this->responseDate = $responseDate;

        return $this;
    }

    public function getTicket(): ?Ticket
    {
        return $this->ticket;
    }

    public function setTicket(?Ticket $ticket): static
    {
        $this->ticket = $ticket;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
