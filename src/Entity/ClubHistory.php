<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\ClubHistoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClubHistoryRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/club-history'),
        new Get(uriTemplate: '/club-history/{id}'),
    ],
    normalizationContext: ['groups' => ['club_history:read']],
)]
class ClubHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['club_history:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotBlank]
    #[Groups(['club_history:read'])]
    private ?\DateTimeImmutable $foundingDate = null;

    /** @var Collection<int, ClubStatistics> */
    #[ORM\OneToMany(targetEntity: ClubStatistics::class, mappedBy: 'clubHistory', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['club_history:read'])]
    private Collection $clubStatistics;

    /** @var Collection<int, ClubHistoryMilestone> */
    #[ORM\OneToMany(targetEntity: ClubHistoryMilestone::class, mappedBy: 'clubHistory', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['year' => 'ASC', 'id' => 'ASC'])]
    #[Groups(['club_history:read'])]
    private Collection $milestones;

    /** @var Collection<int, ClubHistoryMembershipStat> */
    #[ORM\OneToMany(targetEntity: ClubHistoryMembershipStat::class, mappedBy: 'clubHistory', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['year' => 'ASC', 'id' => 'ASC'])]
    #[Groups(['club_history:read'])]
    private Collection $membershipStats;

    /** @var Collection<int, ClubHistorySpecialEvent> */
    #[ORM\OneToMany(targetEntity: ClubHistorySpecialEvent::class, mappedBy: 'clubHistory', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['date' => 'ASC', 'id' => 'ASC'])]
    #[Groups(['club_history:read'])]
    private Collection $specialEvents;

    /** @var Collection<int, ClubHistoryHallOfFameEntry> */
    #[ORM\OneToMany(targetEntity: ClubHistoryHallOfFameEntry::class, mappedBy: 'clubHistory', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['year' => 'ASC', 'id' => 'ASC'])]
    #[Groups(['club_history:read'])]
    private Collection $hallOfFameEntries;

    public function __construct()
    {
        $this->clubStatistics = new ArrayCollection();
        $this->milestones = new ArrayCollection();
        $this->membershipStats = new ArrayCollection();
        $this->specialEvents = new ArrayCollection();
        $this->hallOfFameEntries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFoundingDate(): ?\DateTimeImmutable
    {
        return $this->foundingDate;
    }

    public function setFoundingDate(\DateTimeImmutable $foundingDate): static
    {
        $this->foundingDate = $foundingDate;

        return $this;
    }

    /** @return Collection<int, ClubStatistics> */
    public function getClubStatistics(): Collection
    {
        return $this->clubStatistics;
    }

    /** @param iterable<ClubStatistics> $clubStatistics */
    public function setClubStatistics(iterable $clubStatistics): static
    {
        foreach ($this->clubStatistics->toArray() as $existing) {
            $this->removeClubStatistic($existing);
        }
        foreach ($clubStatistics as $clubStatistic) {
            $this->addClubStatistic($clubStatistic);
        }

        return $this;
    }

    public function addClubStatistic(ClubStatistics $clubStatistic): static
    {
        if (!$this->clubStatistics->contains($clubStatistic)) {
            $this->clubStatistics->add($clubStatistic);
            $clubStatistic->setClubHistory($this);
        }

        return $this;
    }

    public function removeClubStatistic(ClubStatistics $clubStatistic): static
    {
        $this->clubStatistics->removeElement($clubStatistic);

        return $this;
    }

    /** @return Collection<int, ClubHistoryMilestone> */
    public function getMilestones(): Collection
    {
        return $this->milestones;
    }

    /** @param iterable<ClubHistoryMilestone> $milestones */
    public function setMilestones(iterable $milestones): static
    {
        foreach ($this->milestones->toArray() as $existing) {
            $this->removeMilestone($existing);
        }
        foreach ($milestones as $milestone) {
            $this->addMilestone($milestone);
        }

        return $this;
    }

    public function addMilestone(ClubHistoryMilestone $milestone): static
    {
        if (!$this->milestones->contains($milestone)) {
            $this->milestones->add($milestone);
            $milestone->setClubHistory($this);
        }

        return $this;
    }

    public function removeMilestone(ClubHistoryMilestone $milestone): static
    {
        $this->milestones->removeElement($milestone);

        return $this;
    }

    /** @return Collection<int, ClubHistoryMembershipStat> */
    public function getMembershipStats(): Collection
    {
        return $this->membershipStats;
    }

    /** @param iterable<ClubHistoryMembershipStat> $membershipStats */
    public function setMembershipStats(iterable $membershipStats): static
    {
        foreach ($this->membershipStats->toArray() as $existing) {
            $this->removeMembershipStat($existing);
        }
        foreach ($membershipStats as $membershipStat) {
            $this->addMembershipStat($membershipStat);
        }

        return $this;
    }

    public function addMembershipStat(ClubHistoryMembershipStat $membershipStat): static
    {
        if (!$this->membershipStats->contains($membershipStat)) {
            $this->membershipStats->add($membershipStat);
            $membershipStat->setClubHistory($this);
        }

        return $this;
    }

    public function removeMembershipStat(ClubHistoryMembershipStat $membershipStat): static
    {
        $this->membershipStats->removeElement($membershipStat);

        return $this;
    }

    /** @return Collection<int, ClubHistorySpecialEvent> */
    public function getSpecialEvents(): Collection
    {
        return $this->specialEvents;
    }

    /** @param iterable<ClubHistorySpecialEvent> $specialEvents */
    public function setSpecialEvents(iterable $specialEvents): static
    {
        foreach ($this->specialEvents->toArray() as $existing) {
            $this->removeSpecialEvent($existing);
        }
        foreach ($specialEvents as $specialEvent) {
            $this->addSpecialEvent($specialEvent);
        }

        return $this;
    }

    public function addSpecialEvent(ClubHistorySpecialEvent $specialEvent): static
    {
        if (!$this->specialEvents->contains($specialEvent)) {
            $this->specialEvents->add($specialEvent);
            $specialEvent->setClubHistory($this);
        }

        return $this;
    }

    public function removeSpecialEvent(ClubHistorySpecialEvent $specialEvent): static
    {
        $this->specialEvents->removeElement($specialEvent);

        return $this;
    }

    /** @return Collection<int, ClubHistoryHallOfFameEntry> */
    public function getHallOfFameEntries(): Collection
    {
        return $this->hallOfFameEntries;
    }

    /** @param iterable<ClubHistoryHallOfFameEntry> $hallOfFameEntries */
    public function setHallOfFameEntries(iterable $hallOfFameEntries): static
    {
        foreach ($this->hallOfFameEntries->toArray() as $existing) {
            $this->removeHallOfFameEntry($existing);
        }
        foreach ($hallOfFameEntries as $hallOfFameEntry) {
            $this->addHallOfFameEntry($hallOfFameEntry);
        }

        return $this;
    }

    public function addHallOfFameEntry(ClubHistoryHallOfFameEntry $hallOfFameEntry): static
    {
        if (!$this->hallOfFameEntries->contains($hallOfFameEntry)) {
            $this->hallOfFameEntries->add($hallOfFameEntry);
            $hallOfFameEntry->setClubHistory($this);
        }

        return $this;
    }

    public function removeHallOfFameEntry(ClubHistoryHallOfFameEntry $hallOfFameEntry): static
    {
        $this->hallOfFameEntries->removeElement($hallOfFameEntry);

        return $this;
    }
}
