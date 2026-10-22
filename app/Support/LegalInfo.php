<?php

namespace App\Support;

/**
 * Provider details for the legal pages, read from config/legal.php.
 *
 * Required fields that are not configured yet are reported by missing() and
 * shown as visible placeholders so the site is not published unfilled.
 */
final class LegalInfo
{
    /** Fields that must be filled before going online. */
    public const REQUIRED = ['name', 'street', 'city', 'email'];

    private const COUNTRIES = ['AT' => 'Österreich', 'DE' => 'Deutschland', 'CH' => 'Schweiz'];

    /**
     * @param  array<string, mixed>  $values
     */
    public function __construct(private readonly array $values) {}

    public static function fromConfig(): self
    {
        return new self((array) config('legal'));
    }

    public function filled(string $key): bool
    {
        return filled($this->values[$key] ?? null);
    }

    /** The configured value, or a visible placeholder such as «[LEGAL_NAME eintragen]». */
    public function get(string $key): string
    {
        return $this->filled($key)
            ? (string) $this->values[$key]
            : '['.'LEGAL_'.strtoupper($key).' eintragen]';
    }

    public function isComplete(): bool
    {
        return $this->missing() === [];
    }

    /**
     * @return list<string> required keys without a value
     */
    public function missing(): array
    {
        return array_values(array_filter(self::REQUIRED, fn (string $key) => ! $this->filled($key)));
    }

    /** AT, DE or CH – anything unknown falls back to AT. */
    public function country(): string
    {
        $country = strtoupper((string) ($this->values['country'] ?? 'AT'));

        return array_key_exists($country, self::COUNTRIES) ? $country : 'AT';
    }

    public function countryName(): string
    {
        return self::COUNTRIES[$this->country()];
    }
}
