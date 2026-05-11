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
    public function store(
        array $application,
        string $membershipApplicationPdfFilename,
        string $membershipApplicationPdfPath,
        ?string $supervisionDutyPdfFilename = null,
        ?string $supervisionDutyPdfPath = null,
    ): void {
        $membershipApplication = new MembershipApplication();
        $membershipApplication
            ->setFirstName($application['firstName'])
            ->setLastName($application['lastName'])
            ->setMembershipApplicationPdfFilename($membershipApplicationPdfFilename)
            ->setMembershipApplicationPdfPath($membershipApplicationPdfPath)
            ->setSupervisionDutyPdfFilename($supervisionDutyPdfFilename)
            ->setSupervisionDutyPdfPath($supervisionDutyPdfPath)
        ;

        $this->entityManager->persist($membershipApplication);
        $this->entityManager->flush();
    }
}
