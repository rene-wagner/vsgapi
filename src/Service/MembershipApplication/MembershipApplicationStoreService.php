<?php

namespace App\Service\MembershipApplication;

use App\Entity\MembershipApplication;
use Doctrine\ORM\EntityManagerInterface;

/** @phpstan-import-type MembershipApplicationData from MembershipApplicationPayloadMapper */
final class MembershipApplicationStoreService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /** @param MembershipApplicationData $application */
    public function store(array $application, string $pdfFilename, string $pdfPath): void
    {
        $membershipApplication = new MembershipApplication();
        $membershipApplication
            ->setFirstName($application['firstName'])
            ->setLastName($application['lastName'])
            ->setPdfFilename($pdfFilename)
            ->setPdfPath($pdfPath)
        ;

        $this->entityManager->persist($membershipApplication);
        $this->entityManager->flush();
    }
}
