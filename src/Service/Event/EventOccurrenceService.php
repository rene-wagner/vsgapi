<?php

namespace App\Service\Event;

use App\Entity\Event;
use App\Enum\EventRecurrence;
use App\Repository\EventRepository;

class EventOccurrenceService
{
    public function __construct(
        private EventRepository $eventRepository,
    ) {
    }

    /**
     * Returns FullCalendar-compatible event data for the given date range.
     *
     * @return list<array{id: int, title: string, start: string, end: string, url?: string}>
     */
    public function getCalendarEvents(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        $events = $this->eventRepository->findOverlapping($from, $to);
        $occurrences = [];

        foreach ($events as $event) {
            foreach ($this->expandOccurrences($event, $from, $to) as $occurrence) {
                $occurrences[] = [
                    'id' => $event->getId(),
                    'title' => $event->getTitle(),
                    'start' => $occurrence['startsAt']->format(\DateTimeInterface::ATOM),
                    'end' => $occurrence['endsAt']->format(\DateTimeInterface::ATOM),
                    'url' => '/admin/events/' . $event->getId() . '/edit',
                ];
            }
        }

        return $occurrences;
    }

    /**
     * @return list<array{startsAt: \DateTimeImmutable, endsAt: \DateTimeImmutable}>
     */
    public function expandOccurrences(Event $event, \DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        if ($event->getRecurrence() === null) {
            if ($event->getStartsAt() < $to && $event->getEndsAt() > $from) {
                return [['startsAt' => $event->getStartsAt(), 'endsAt' => $event->getEndsAt()]];
            }

            return [];
        }

        $occurrences = [];
        $duration = $event->getStartsAt()->diff($event->getEndsAt());
        $recurrenceEnd = $event->getRecurrenceUntil() ?? $to;
        $limit = min($recurrenceEnd, $to);
        $maxIterations = 400;

        $current = $this->firstOccurrenceOnOrAfter($event, $from);

        for ($i = 0; $current !== null && $current <= $limit && $i < $maxIterations; $i++) {
            $occEnd = $current->add($duration);
            $occurrences[] = ['startsAt' => $current, 'endsAt' => $occEnd];
            $current = $this->nextOccurrence($event, $current);
        }

        return $occurrences;
    }

    private function firstOccurrenceOnOrAfter(Event $event, \DateTimeImmutable $from): ?\DateTimeImmutable
    {
        $base = $event->getStartsAt();

        if ($base >= $from) {
            return $base;
        }

        return $this->jumpToOrAfter($event, $from);
    }

    private function jumpToOrAfter(Event $event, \DateTimeImmutable $target): ?\DateTimeImmutable
    {
        $base = $event->getStartsAt();
        $recurrence = $event->getRecurrence();

        if ($recurrence === null) {
            return null;
        }

        $monthDiff = ($target->format('Y') - $base->format('Y')) * 12 + ($target->format('n') - $base->format('n'));
        $yearDiff = (int) $target->format('Y') - (int) $base->format('Y');

        $candidate = match ($recurrence) {
            EventRecurrence::Daily => $this->advanceDays($base, $target->diff($base)->days),
            EventRecurrence::Weekly => $this->advanceDays($base, (int) (floor($target->diff($base)->days / 7) * 7)),
            EventRecurrence::Monthly => $this->addMonths($event, $base, $monthDiff),
            EventRecurrence::Yearly => $this->addYears($event, $base, $yearDiff),
        };

        if ($candidate < $target) {
            $candidate = $this->nextOccurrence($event, $candidate);
        }

        return $candidate;
    }

    private function nextOccurrence(Event $event, \DateTimeImmutable $current): ?\DateTimeImmutable
    {
        return match ($event->getRecurrence()) {
            EventRecurrence::Daily => $this->advanceDays($current, 1),
            EventRecurrence::Weekly => $this->advanceDays($current, 7),
            EventRecurrence::Monthly => $this->addMonths($event, $current, 1),
            EventRecurrence::Yearly => $this->addYears($event, $current, 1),
            null => null,
        };
    }

    private function advanceDays(\DateTimeImmutable $date, int $days): \DateTimeImmutable
    {
        return $date->modify('+' . $days . ' days');
    }

    private function addMonths(Event $event, \DateTimeImmutable $date, int $months): \DateTimeImmutable
    {
        $base = $event->getStartsAt();
        $targetDay = (int) $base->format('j');
        $time = $base->format('H:i:s');

        $year = (int) $date->format('Y');
        $month = (int) $date->format('n') + $months;

        // Normalize month overflow/underflow
        while ($month > 12) {
            $month -= 12;
            $year++;
        }
        while ($month < 1) {
            $month += 12;
            $year--;
        }

        $maxDay = (int) (new \DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month)))->format('t');
        $day = min($targetDay, $maxDay);

        return new \DateTimeImmutable(sprintf('%04d-%02d-%02d %s', $year, $month, $day, $time));
    }

    private function addYears(Event $event, \DateTimeImmutable $date, int $years): \DateTimeImmutable
    {
        $base = $event->getStartsAt();
        $targetDay = (int) $base->format('j');
        $targetMonth = (int) $base->format('n');
        $time = $base->format('H:i:s');

        $year = (int) $date->format('Y') + $years;

        $maxDay = (int) (new \DateTimeImmutable(sprintf('%04d-%02d-01', $year, $targetMonth)))->format('t');
        $day = min($targetDay, $maxDay);

        return new \DateTimeImmutable(sprintf('%04d-%02d-%02d %s', $year, $targetMonth, $day, $time));
    }
}