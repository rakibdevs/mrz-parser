<?php

declare(strict_types=1);

namespace Rakibdevs\MrzParser;

use DateTimeImmutable;

/**
 * Typed, read-only view over a parsed MRZ.
 *
 * The canonical associative array returned by {@see MrzParser::parse()} is
 * preserved verbatim (see {@see self::toArray()}); this object adds typed
 * accessors and date helpers on top of it.
 */
final class MrzResult
{
    /**
     * @param array<string, bool> $validation
     * @param array<string, mixed> $attributes the original parse() array
     */
    public function __construct(
        public readonly string $type,
        public readonly ?string $cardNo,
        public readonly ?string $issuer,
        public readonly ?string $issuerCode,
        public readonly ?string $dateOfExpiry,
        public readonly ?string $firstName,
        public readonly ?string $lastName,
        public readonly ?string $dateOfBirth,
        public readonly ?string $gender,
        public readonly ?string $sex,
        public readonly ?string $personalNumber,
        public readonly ?string $optionalData,
        public readonly ?string $nationality,
        public readonly ?string $nationalityCode,
        public readonly bool $valid,
        public readonly array $validation,
        public readonly string $raw,
        private readonly array $attributes,
    ) {
    }

    /**
     * @param array<string, mixed> $data a MrzParser::parse() result
     */
    public static function fromArray(array $data): self
    {
        return new self(
            type: (string) ($data['type'] ?? ''),
            cardNo: $data['card_no'] ?? null,
            issuer: $data['issuer'] ?? null,
            issuerCode: $data['issuer_code'] ?? null,
            dateOfExpiry: $data['date_of_expiry'] ?? null,
            firstName: $data['first_name'] ?? null,
            lastName: $data['last_name'] ?? null,
            dateOfBirth: $data['date_of_birth'] ?? null,
            gender: $data['gender'] ?? null,
            sex: $data['sex'] ?? null,
            personalNumber: $data['personal_number'] ?? null,
            optionalData: $data['optional_data'] ?? null,
            nationality: $data['nationality'] ?? null,
            nationalityCode: $data['nationality_code'] ?? null,
            valid: (bool) ($data['valid'] ?? false),
            validation: $data['validation'] ?? [],
            raw: (string) ($data['raw'] ?? ''),
            attributes: $data,
        );
    }

    public function dateOfBirthAsDate(): ?DateTimeImmutable
    {
        return $this->toDate($this->dateOfBirth);
    }

    public function dateOfExpiryAsDate(): ?DateTimeImmutable
    {
        return $this->toDate($this->dateOfExpiry);
    }

    /**
     * Whether the document is expired at the given moment (defaults to now).
     * Returns null when the format carries no expiry date.
     */
    public function isExpired(?DateTimeImmutable $at = null): ?bool
    {
        $expiry = $this->dateOfExpiryAsDate();

        if ($expiry === null) {
            return null;
        }

        return $expiry < ($at ?? new DateTimeImmutable('today'));
    }

    /**
     * Combined "First Last" name, or null when neither part is present.
     */
    public function fullName(): ?string
    {
        $name = trim(implode(' ', array_filter([$this->firstName, $this->lastName])));

        return $name === '' ? null : $name;
    }

    /**
     * @return array<string, mixed> the original MrzParser::parse() array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    private function toDate(?string $value): ?DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date === false ? null : $date;
    }
}
