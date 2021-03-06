<?php

declare(strict_types=1);

namespace CNastasi\DDD\ValueObject\Primitive;

use CNastasi\DDD\Contract\CompositeValueObject;
use CNastasi\DDD\Contract\Serializable;
use CNastasi\DDD\Contract\Stringable;
use CNastasi\DDD\Error\InvalidDate;
use DateTimeImmutable;
use DateTimeInterface;

final class Date implements CompositeValueObject, Serializable, Stringable
{
    private int $days;

    private int $months;

    private int $years;

    public function __construct(int $days, int $months, int $years)
    {
        $this->assertDateIsValid($days, $months, $years);

        $this->days = $days;
        $this->months = $months;
        $this->years = $years;
    }

    public static function now(): Date
    {
        return static::fromDateTimeInterface(new DateTimeImmutable());
    }

    private static function toString(int $years, int $months, int $days): string
    {
        return \sprintf('%4d-%02d-%02d', $years, $months, $days);
    }

    public function getDays(): int
    {
        return $this->days;
    }

    public function getMonths(): int
    {
        return $this->months;
    }

    public function getYears(): int
    {
        return $this->years;
    }

    public function __toString(): string
    {
        return static::toString($this->years, $this->months, $this->days);
    }

    public function toDateTimeInterface(): DateTimeInterface
    {
        $dateAsString = $this->__toString() . 'T00:00:00';

        $result = DateTimeImmutable::createFromFormat(DateTimeImmutable::RFC3339, $dateAsString);

        if ($result === false) {
            throw new InvalidDate($dateAsString);
        }

        return $result;
    }

    public static function fromDateTimeInterface(DateTimeInterface $date): self
    {
        $days = (int)$date->format('d');
        $months = (int)$date->format('m');
        $years = (int)$date->format('Y');

        return new static($days, $months, $years);
    }

    public static function fromString(string $dateAsString, string $format = DateTimeInterface::RFC3339): Date
    {
        $date = DateTimeImmutable::createFromFormat($format, $dateAsString);

        if ($date === false) {
            throw new InvalidDate($dateAsString);
        }

        return static::fromDateTimeInterface($date);
    }

    private function assertDateIsValid(int $days, int $months, int $years): void
    {
        $dateAsString = self::toString($years, $months, $days);

        $date = DateTimeImmutable::createFromFormat('Y-m-d', $dateAsString);

        if ($date === false || $date->format('Y-m-d') !== $dateAsString) {
            throw new InvalidDate($dateAsString);
        }
    }

    public function serialize(): string
    {
        return $this->__toString();
    }
}
