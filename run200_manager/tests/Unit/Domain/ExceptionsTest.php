<?php

use App\Domain\Exceptions\BusinessRuleViolationException;
use App\Domain\Exceptions\CarAlreadyRegisteredException;
use App\Domain\Exceptions\DuplicateLicenseNumberException;
use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\Exceptions\ImportException;
use App\Domain\Exceptions\InvalidQrCodeException;
use App\Domain\Exceptions\PaymentFailedException;
use App\Domain\Exceptions\PilotAlreadyRegisteredException;
use App\Domain\Exceptions\RaceNumberAlreadyTakenException;
use App\Domain\Exceptions\RegistrationClosedException;

describe('Domain Exceptions', function () {
    describe('DuplicateLicenseNumberException', function () {
        it('creates exception with license number', function () {
            $exception = DuplicateLicenseNumberException::forLicense('ABC123', 42);

            expect($exception->getErrorCode())->toBe('PILOT_LICENSE_DUPLICATE')
                ->and($exception->getContext())->toHaveKey('license_number', 'ABC123')
                ->and($exception->getContext())->toHaveKey('existing_pilot_id', 42)
                ->and($exception->getMessage())->toContain('ABC123');
        });

        it('supports toArray conversion', function () {
            $exception = DuplicateLicenseNumberException::forLicense('XYZ789');
            $array = $exception->toArray();

            expect($array)->toHaveKey('error_code', 'PILOT_LICENSE_DUPLICATE')
                ->and($array)->toHaveKey('message')
                ->and($array)->toHaveKey('context');
        });
    });

    describe('RaceNumberAlreadyTakenException', function () {
        it('creates exception with race number', function () {
            $exception = RaceNumberAlreadyTakenException::forNumber(42, 10);

            expect($exception->getErrorCode())->toBe('CAR_RACE_NUMBER_TAKEN')
                ->and($exception->getContext()['race_number'])->toBe(42)
                ->and($exception->getContext()['existing_car_id'])->toBe(10);
        });
    });

    describe('RegistrationClosedException', function () {
        it('creates exception for closed race', function () {
            $exception = RegistrationClosedException::forRace(1, 'Course Test', 'CLOSED');

            expect($exception->getErrorCode())->toBe('REGISTRATION_CLOSED')
                ->and($exception->getMessage())->toContain('Course Test')
                ->and($exception->getMessage())->toContain('CLOSED');
        });
    });

    describe('PilotAlreadyRegisteredException', function () {
        it('creates exception for duplicate pilot registration', function () {
            $exception = PilotAlreadyRegisteredException::forPilotAndRace(1, 2, 'John Doe', 'Grand Prix');

            expect($exception->getErrorCode())->toBe('REGISTRATION_PILOT_DUPLICATE')
                ->and($exception->getMessage())->toContain('John Doe')
                ->and($exception->getMessage())->toContain('Grand Prix');
        });
    });

    describe('CarAlreadyRegisteredException', function () {
        it('creates exception for duplicate car registration', function () {
            $exception = CarAlreadyRegisteredException::forCarAndRace(1, 2, 'Ferrari #42', 'Grand Prix');

            expect($exception->getErrorCode())->toBe('REGISTRATION_CAR_DUPLICATE')
                ->and($exception->getMessage())->toContain('Ferrari #42');
        });
    });

    describe('PaymentFailedException', function () {
        it('creates exception with reason', function () {
            $exception = PaymentFailedException::withReason('Carte refusée');

            expect($exception->getErrorCode())->toBe('PAYMENT_FAILED')
                ->and($exception->getMessage())->toContain('Carte refusée');
        });

        it('creates exception from gateway error', function () {
            $exception = PaymentFailedException::fromGateway('stripe', 'card_declined', 'txn_123');

            expect($exception->getContext()['gateway'])->toBe('stripe')
                ->and($exception->getContext()['gateway_error'])->toBe('card_declined')
                ->and($exception->getContext()['transaction_id'])->toBe('txn_123');
        });
    });

    describe('EntityNotFoundException', function () {
        it('creates pilot not found exception', function () {
            $exception = EntityNotFoundException::pilot(999);

            expect($exception->getErrorCode())->toBe('ENTITY_NOT_FOUND')
                ->and($exception->getMessage())->toContain('Pilote')
                ->and($exception->getMessage())->toContain('999');
        });

        it('creates car not found exception', function () {
            $exception = EntityNotFoundException::car(123);

            expect($exception->getMessage())->toContain('Voiture')
                ->and($exception->getMessage())->toContain('123');
        });

        it('creates race not found exception', function () {
            $exception = EntityNotFoundException::race(456);

            expect($exception->getMessage())->toContain('Course');
        });
    });

    describe('InvalidQrCodeException', function () {
        it('creates expired exception', function () {
            $exception = InvalidQrCodeException::expired();

            expect($exception->getErrorCode())->toBe('QR_CODE_INVALID')
                ->and($exception->getMessage())->toContain('expiré');
        });

        it('creates already used exception', function () {
            $exception = InvalidQrCodeException::alreadyUsed('Vérification Admin');

            expect($exception->getMessage())->toContain('Vérification Admin');
        });

        it('truncates token for security', function () {
            $longToken = 'abcdefghijklmnopqrstuvwxyz1234567890';
            $exception = InvalidQrCodeException::notFound($longToken);

            $context = $exception->getContext();
            expect($context['token'])->toBe('abcdefghij...');
        });
    });

    describe('ImportException', function () {
        it('creates exception for missing columns', function () {
            $exception = ImportException::missingColumns(['bib', 'position']);

            expect($exception->getErrors())->toBe(['bib', 'position'])
                ->and($exception->getMessage())->toContain('bib')
                ->and($exception->getMessage())->toContain('position');
        });

        it('creates exception for too many errors', function () {
            $exception = ImportException::tooManyErrors(60, 100, 50);

            expect($exception->getMessage())->toContain('60%')
                ->and($exception->getMessage())->toContain('50%');
        });

        it('creates exception for encoding error', function () {
            $exception = ImportException::encodingError('ISO-8859-1');

            expect($exception->getMessage())->toContain('ISO-8859-1')
                ->and($exception->getMessage())->toContain('UTF-8');
        });
    });

    describe('BusinessRuleViolationException', function () {
        it('creates max cars per pilot exception', function () {
            $exception = BusinessRuleViolationException::maxCarsPerPilot(5, 3);

            expect($exception->getErrorCode())->toBe('BUSINESS_RULE_VIOLATION')
                ->and($exception->getContext()['rule'])->toBe('max_cars_per_pilot')
                ->and($exception->getMessage())->toContain('3');
        });

        it('creates race capacity reached exception', function () {
            $exception = BusinessRuleViolationException::raceCapacityReached(100, 100);

            expect($exception->getMessage())->toContain('100/100');
        });

        it('creates invalid status transition exception', function () {
            $exception = BusinessRuleViolationException::invalidStatusTransition('PENDING', 'COMPLETED');

            expect($exception->getMessage())->toContain('PENDING')
                ->and($exception->getMessage())->toContain('COMPLETED');
        });
    });

    describe('Log context', function () {
        it('generates structured log context', function () {
            $exception = DuplicateLicenseNumberException::forLicense('TEST123');
            $logContext = $exception->toLogContext();

            expect($logContext)->toHaveKey('exception', DuplicateLicenseNumberException::class)
                ->and($logContext)->toHaveKey('error_code', 'PILOT_LICENSE_DUPLICATE')
                ->and($logContext)->toHaveKey('message')
                ->and($logContext)->toHaveKey('context')
                ->and($logContext)->toHaveKey('file')
                ->and($logContext)->toHaveKey('line');
        });
    });
});
