<?php

namespace App\Service\MembershipApplication;

/**
 * @phpstan-type MembershipApplicationData array{
 *     department: string,
 *     firstName: string,
 *     lastName: string,
 *     birthDate: string,
 *     phone: string,
 *     email: string,
 *     street: string,
 *     postalCode: string,
 *     city: string,
 *     otherClub: string,
 *     bankName: string,
 *     iban: string,
 *     bic: string,
 *     accountHolder: string,
 *     place: string,
 *     applicationDate: string,
 *     legalGuardianName: string,
 *     acceptsStatutes: bool,
 *     acceptsEmailInvitation: bool,
 *     acceptsPrivacyPolicy: bool,
 *     confirmsMinorAttachment: bool,
 *     isChild: bool,
 *     guardianOneName: string,
 *     guardianOneAddress: string,
 *     guardianOnePhone: string,
 *     guardianTwoName: string,
 *     guardianTwoAddress: string,
 *     guardianTwoPhone: string,
 *     underTwelveMayWalkHomeAlone: bool,
 *     overTwelveMayWalkHomeAlone: bool
 * }
 */
final class MembershipApplicationPayloadMapper
{
    /**
     * @param array<string, mixed> $payload
     *
     * @return MembershipApplicationData
     */
    public function map(array $payload): array
    {
        $department = $this->requireDepartment($payload, 'department');
        $firstName = $this->requireString($payload, 'firstName', 'Bitte geben Sie den Vornamen an.');
        $lastName = $this->requireString($payload, 'lastName', 'Bitte geben Sie den Nachnamen an.');
        $birthDate = $this->requireDate($payload, 'birthDate', 'Bitte geben Sie ein gueltiges Geburtsdatum an.');
        $phone = $this->requireString($payload, 'phone', 'Bitte geben Sie die Telefonnummer an.');
        $email = $this->requireEmail($payload, 'email');
        $street = $this->requireString($payload, 'street', 'Bitte geben Sie die Strasse an.');
        $postalCode = $this->requirePostalCode($payload, 'postalCode');
        $city = $this->requireString($payload, 'city', 'Bitte geben Sie den Wohnort an.');
        $otherClub = $this->optionalString($payload, 'otherClub');
        $bankName = $this->requireString($payload, 'bankName', 'Bitte geben Sie das Kreditinstitut an.');
        $iban = $this->requireIban($payload, 'iban');
        $bic = $this->optionalString($payload, 'bic');
        $accountHolder = $this->requireString($payload, 'accountHolder', 'Bitte geben Sie die Kontoinhaberin oder den Kontoinhaber an.');
        $place = $this->requireString($payload, 'place', 'Bitte geben Sie den Ort an.');
        $applicationDate = $this->requireDate($payload, 'applicationDate', 'Bitte geben Sie ein gueltiges Antragsdatum an.');
        $legalGuardianName = $this->optionalString($payload, 'legalGuardianName');
        $acceptsStatutes = $this->requireBool($payload, 'acceptsStatutes', 'Bitte geben Sie an, ob die Satzung anerkannt wird.');
        $acceptsEmailInvitation = $this->requireBool($payload, 'acceptsEmailInvitation', 'Bitte geben Sie an, ob die Einladung per E-Mail erfolgen darf.');
        $acceptsPrivacyPolicy = $this->requireBool($payload, 'acceptsPrivacyPolicy', 'Bitte geben Sie die DSGVO-Einwilligung an.');
        $confirmsMinorAttachment = $this->requireBool($payload, 'confirmsMinorAttachment', 'Bitte geben Sie an, ob die Anlage zur Aufsichtspflicht beigefuegt ist.');
        $isChild = $this->requireBool($payload, 'isChild', 'Bitte geben Sie an, ob es sich um ein Kind handelt.');
        $guardianOneName = $this->optionalString($payload, 'guardianOneName');
        $guardianOneAddress = $this->optionalString($payload, 'guardianOneAddress');
        $guardianOnePhone = $this->optionalString($payload, 'guardianOnePhone');
        $guardianTwoName = $this->optionalString($payload, 'guardianTwoName');
        $guardianTwoAddress = $this->optionalString($payload, 'guardianTwoAddress');
        $guardianTwoPhone = $this->optionalString($payload, 'guardianTwoPhone');
        $underTwelveMayWalkHomeAlone = $this->requireBool($payload, 'underTwelveMayWalkHomeAlone', 'Bitte geben Sie die Regelung fuer Kinder unter zwoelf Jahren an.');
        $overTwelveMayWalkHomeAlone = $this->requireBool($payload, 'overTwelveMayWalkHomeAlone', 'Bitte geben Sie die Regelung fuer Kinder ab zwoelf Jahren an.');

        return [
            'department' => $department,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'birthDate' => $birthDate,
            'phone' => $phone,
            'email' => $email,
            'street' => $street,
            'postalCode' => $postalCode,
            'city' => $city,
            'otherClub' => $otherClub,
            'bankName' => $bankName,
            'iban' => $iban,
            'bic' => $bic,
            'accountHolder' => $accountHolder,
            'place' => $place,
            'applicationDate' => $applicationDate,
            'legalGuardianName' => $legalGuardianName,
            'acceptsStatutes' => $acceptsStatutes,
            'acceptsEmailInvitation' => $acceptsEmailInvitation,
            'acceptsPrivacyPolicy' => $acceptsPrivacyPolicy,
            'confirmsMinorAttachment' => $confirmsMinorAttachment,
            'isChild' => $isChild,
            'guardianOneName' => $guardianOneName,
            'guardianOneAddress' => $guardianOneAddress,
            'guardianOnePhone' => $guardianOnePhone,
            'guardianTwoName' => $guardianTwoName,
            'guardianTwoAddress' => $guardianTwoAddress,
            'guardianTwoPhone' => $guardianTwoPhone,
            'underTwelveMayWalkHomeAlone' => $underTwelveMayWalkHomeAlone,
            'overTwelveMayWalkHomeAlone' => $overTwelveMayWalkHomeAlone,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function requireString(array $payload, string $key, string $message): string
    {
        $value = $this->optionalString($payload, $key);
        if ($value === '') {
            throw new \InvalidArgumentException($message);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function optionalString(array $payload, string $key): string
    {
        $value = $payload[$key] ?? null;

        return is_string($value) ? trim($value) : '';
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function requireBool(array $payload, string $key, string $message): bool
    {
        $value = $payload[$key] ?? null;
        if (!is_bool($value)) {
            throw new \InvalidArgumentException($message);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function requireEmail(array $payload, string $key): string
    {
        $value = $this->requireString($payload, $key, 'Bitte geben Sie eine E-Mail-Adresse an.');
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException('Bitte geben Sie eine gueltige E-Mail-Adresse an.');
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function requireDate(array $payload, string $key, string $message): string
    {
        $value = $this->requireString($payload, $key, $message);
        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $value);
        $errors = \DateTimeImmutable::getLastErrors();

        if ($date === false) {
            throw new \InvalidArgumentException($message);
        }

        if (is_array($errors) && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
            throw new \InvalidArgumentException($message);
        }

        return $date->format('d.m.Y');
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function requirePostalCode(array $payload, string $key): string
    {
        $value = $this->requireString($payload, $key, 'Bitte geben Sie die Postleitzahl an.');
        if (!preg_match('/^\d{5}$/', $value)) {
            throw new \InvalidArgumentException('Bitte geben Sie eine gueltige Postleitzahl an.');
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function requireIban(array $payload, string $key): string
    {
        $value = strtoupper(str_replace(' ', '', $this->requireString($payload, $key, 'Bitte geben Sie die IBAN an.')));
        if (!preg_match('/^[A-Z]{2}[0-9A-Z]{13,32}$/', $value)) {
            throw new \InvalidArgumentException('Bitte geben Sie eine gueltige IBAN an.');
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function requireDepartment(array $payload, string $key): string
    {
        $value = strtolower($this->requireString($payload, $key, 'Bitte geben Sie eine gueltige Abteilung an.'));
        $allowedValues = ['volleyball', 'gymnastik', 'tischtennis', 'badminton'];

        if (!in_array($value, $allowedValues, true)) {
            throw new \InvalidArgumentException('Bitte geben Sie eine gueltige Abteilung an.');
        }

        return $value;
    }
}
