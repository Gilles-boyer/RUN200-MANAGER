<?php

use App\Domain\Car\ValueObjects\RaceNumber;
use App\Domain\Pilot\ValueObjects\LicenseNumber;

describe('LicenseNumber ValueObject', function () {
    test('accepte un numéro de licence valide', function () {
        expect(LicenseNumber::fromString('123456'))->toBeInstanceOf(LicenseNumber::class)
            ->and(LicenseNumber::fromString('1')->toString())->toBe('1')
            ->and(LicenseNumber::fromString('123')->toString())->toBe('123');
    });

    test('rejette un numéro de licence vide', function () {
        expect(fn () => LicenseNumber::fromString(''))->toThrow(InvalidArgumentException::class);
    });

    test('rejette un numéro de licence avec plus de 6 chiffres', function () {
        expect(fn () => LicenseNumber::fromString('1234567'))->toThrow(InvalidArgumentException::class);
    });

    test('rejette un numéro de licence avec des caractères non numériques', function () {
        expect(fn () => LicenseNumber::fromString('ABC123'))->toThrow(InvalidArgumentException::class);
        expect(fn () => LicenseNumber::fromString('12-34'))->toThrow(InvalidArgumentException::class);
    });

    test('peut être converti en string', function () {
        $license = LicenseNumber::fromString('42');
        expect($license->toString())->toBe('42');
    });
});

describe('RaceNumber ValueObject', function () {
    test('accepte un numéro de course valide', function () {
        $raceNumber0 = RaceNumber::fromInt(0);
        $raceNumber42 = RaceNumber::fromInt(42);
        $raceNumber999 = RaceNumber::fromInt(999);

        expect($raceNumber0)->toBeInstanceOf(RaceNumber::class)
            ->and($raceNumber42->toInt())->toBe(42)
            ->and($raceNumber999->toInt())->toBe(999);
    });

    test('rejette un numéro de course négatif', function () {
        expect(fn () => RaceNumber::fromInt(-1))->toThrow(InvalidArgumentException::class);
    });

    test('rejette un numéro de course supérieur à 999', function () {
        expect(fn () => RaceNumber::fromInt(1000))->toThrow(InvalidArgumentException::class);
    });

    test('peut être converti en entier', function () {
        $raceNumber = RaceNumber::fromInt(42);
        expect($raceNumber->toInt())->toBe(42);
    });

    test('peut être converti en string', function () {
        $raceNumber = RaceNumber::fromInt(7);
        expect($raceNumber->toString())->toBe('7');
    });
});
