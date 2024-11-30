<?php

namespace App\Entity;

use App\Repository\TicketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $subject = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $creationDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $closingDate = null;

    #[ORM\ManyToOne(inversedBy: 'tickets')]
    private ?User $user = null;

    /**
     * @var Collection<int, TicketResponse>
     */
    #[ORM\OneToMany(mappedBy: 'ticket', targetEntity: TicketResponse::class)]
    private Collection $ticketResponses;

    #[ORM\ManyToOne(inversedBy: 'tickets')]
    private ?Order $purchase = null;

    public function __construct()
    {
        $this->ticketResponses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): static
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getClosingDate(): ?\DateTimeInterface
    {
        return $this->closingDate;
    }

    public function setClosingDate(?\DateTimeInterface $closingDate): static
    {
        $this->closingDate = $closingDate;

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

    /**
     * @return Collection<int, TicketResponse>
     */
    public function getTicketResponses(): Collection
    {
        return $this->ticketResponses;
    }

    public function addTicketResponse(TicketResponse $ticketResponse): static
    {
        if (!$this->ticketResponses->contains($ticketResponse)) {
            $this->ticketResponses->add($ticketResponse);
            $ticketResponse->setTicket($this);
        }

        return $this;
    }

    public function removeTicketResponse(TicketResponse $ticketResponse): static
    {
        if ($this->ticketResponses->removeElement($ticketResponse)) {
            // set the owning side to null (unless already changed)
            if ($ticketResponse->getTicket() === $this) {
                $ticketResponse->setTicket(null);
            }
        }

        return $this;
    }

    public function getPurchase(): ?Order
    {
        return $this->purchase;
    }

    public function setPurchase(?Order $purchase): static
    {
        $this->purchase = $purchase;

        return $this;
    }

}
