<?php

namespace App\Service\MembershipApplication;

use App\Entity\ContactPerson;
use App\Entity\Department;
use App\Repository\ContactPersonRepository;
use App\Repository\DepartmentRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/** @phpstan-import-type MembershipApplicationData from MembershipApplicationPayloadMapper */
final class MembershipApplicationNotificationService
{
    public function __construct(
        private readonly ContactPersonRepository $contactPersonRepository,
        private readonly DepartmentRepository $departmentRepository,
        private readonly MailerInterface $mailer,
        private readonly string $mailerFrom,
    ) {
    }

    /**
     * @param MembershipApplicationData $application
     */
    public function send(array $application, string $pdfPath, ?string $supervisionPdfPath = null): void
    {
        $primaryRecipient = $this->getPrimaryRecipient();
        $department = $this->getDepartment($application['department']);
        $manager = $department->getManager();
        if (!$manager instanceof ContactPerson) {
            throw new \RuntimeException('Für die Abteilung des Aufnahmeantrags ist kein Abteilungsleiter hinterlegt.');
        }

        $recipients = $this->buildRecipients($primaryRecipient, $manager);

        $email = (new TemplatedEmail())
            ->from(new Address($this->mailerFrom, 'VSG Aufnahmeantrag'))
            ->subject('Neuer Aufnahmeantrag: ' . $application['firstName'] . ' ' . $application['lastName'])
            ->htmlTemplate('membership_application/email.html.twig')
            ->context([
                'application' => $application,
                'department' => $department,
            ])
            ->attachFromPath($pdfPath, 'Aufnahmeantrag.pdf', 'application/pdf')
        ;

        foreach ($recipients as $recipient) {
            $email->addTo($recipient);
        }

        if ($supervisionPdfPath !== null) {
            $email->attachFromPath($supervisionPdfPath, 'Aufsichtspflicht.pdf', 'application/pdf');
        }

        $this->mailer->send($email);
    }

    private function getPrimaryRecipient(): ContactPerson
    {
        $contactPerson = $this->contactPersonRepository->findOneBy([
            'slug' => 'christian-koehler',
        ]);

        if (!$contactPerson instanceof ContactPerson) {
            throw new \RuntimeException('Die Kontaktperson mit dem Slug "christian-koehler" wurde nicht gefunden.');
        }

        if ($contactPerson->getEmail() === null || trim($contactPerson->getEmail()) === '') {
            throw new \RuntimeException('Für die Kontaktperson "christian-koehler" ist keine E-Mail-Adresse hinterlegt.');
        }

        return $contactPerson;
    }

    private function getDepartment(string $slug): Department
    {
        $department = $this->departmentRepository->findOneBy([
            'slug' => $slug,
        ]);

        if (!$department instanceof Department) {
            throw new \RuntimeException('Die Abteilung für den Aufnahmeantrag wurde nicht gefunden.');
        }

        return $department;
    }

    /**
     * @return list<Address>
     */
    private function buildRecipients(ContactPerson $primaryRecipient, ContactPerson $manager): array
    {
        $recipients = [];
        $emails = [];

        foreach ([$primaryRecipient, $manager] as $contactPerson) {
            $email = $contactPerson->getEmail();
            if ($email === null || trim($email) === '') {
                throw new \RuntimeException(sprintf(
                    'Für die Kontaktperson "%s" ist keine E-Mail-Adresse hinterlegt.',
                    $contactPerson->getSlug() ?? 'unbekannt',
                ));
            }

            $normalizedEmail = strtolower(trim($email));
            if (isset($emails[$normalizedEmail])) {
                continue;
            }

            $emails[$normalizedEmail] = true;
            $name = trim(($contactPerson->getFirstName() ?? '') . ' ' . ($contactPerson->getLastName() ?? ''));
            $recipients[] = new Address($email, $name !== '' ? $name : $email);
        }

        return $recipients;
    }
}
