<?php

use App\Casts\CarBrandCast;
use App\Casts\LicenseNumberCast;
use App\Casts\LowercaseCast;
use App\Casts\PhoneNumberCast;
use App\Casts\PostalCodeCast;
use App\Casts\TitleCaseCast;
use App\Casts\UppercaseCast;

describe('UppercaseCast', function () {
    beforeEach(function () {
        $this->cast = new UppercaseCast;
        $this->model = new class extends \Illuminate\Database\Eloquent\Model {};
    });

    it('convertit en majuscules', function () {
        expect($this->cast->set($this->model, 'name', 'boyer', []))->toBe('BOYER');
        expect($this->cast->set($this->model, 'name', 'jean-pierre', []))->toBe('JEAN-PIERRE');
        expect($this->cast->set($this->model, 'name', 'Paris', []))->toBe('PARIS');
    });

    it('supprime les espaces superflus', function () {
        expect($this->cast->set($this->model, 'name', '  boyer  ', []))->toBe('BOYER');
    });

    it('gère les valeurs null', function () {
        expect($this->cast->set($this->model, 'name', null, []))->toBeNull();
    });
});

describe('TitleCaseCast', function () {
    beforeEach(function () {
        $this->cast = new TitleCaseCast;
        $this->model = new class extends \Illuminate\Database\Eloquent\Model {};
    });

    it('convertit en Title Case', function () {
        expect($this->cast->set($this->model, 'name', 'jean', []))->toBe('Jean');
        expect($this->cast->set($this->model, 'name', 'MARIE', []))->toBe('Marie');
    });

    it('gère les prénoms composés avec tiret', function () {
        expect($this->cast->set($this->model, 'name', 'jean-pierre', []))->toBe('Jean-Pierre');
        expect($this->cast->set($this->model, 'name', 'MARIE-CLAIRE', []))->toBe('Marie-Claire');
    });

    it('gère les prénoms avec espaces', function () {
        expect($this->cast->set($this->model, 'name', 'jean claude', []))->toBe('Jean Claude');
    });

    it('supprime les espaces superflus', function () {
        expect($this->cast->set($this->model, 'name', '  jean  ', []))->toBe('Jean');
    });

    it('gère les valeurs null', function () {
        expect($this->cast->set($this->model, 'name', null, []))->toBeNull();
    });
});

describe('LowercaseCast', function () {
    beforeEach(function () {
        $this->cast = new LowercaseCast;
        $this->model = new class extends \Illuminate\Database\Eloquent\Model {};
    });

    it('convertit en minuscules', function () {
        expect($this->cast->set($this->model, 'email', 'TEST@EXAMPLE.COM', []))->toBe('test@example.com');
        expect($this->cast->set($this->model, 'email', 'Test@Example.Com', []))->toBe('test@example.com');
    });

    it('supprime les espaces superflus', function () {
        expect($this->cast->set($this->model, 'email', '  test@example.com  ', []))->toBe('test@example.com');
    });

    it('gère les valeurs null', function () {
        expect($this->cast->set($this->model, 'email', null, []))->toBeNull();
    });
});

describe('PhoneNumberCast', function () {
    beforeEach(function () {
        $this->cast = new PhoneNumberCast;
        $this->model = new class extends \Illuminate\Database\Eloquent\Model {};
    });

    it('normalise les numéros français', function () {
        expect($this->cast->set($this->model, 'phone', '06 12 34 56 78', []))->toBe('0612345678');
        expect($this->cast->set($this->model, 'phone', '06.12.34.56.78', []))->toBe('0612345678');
        expect($this->cast->set($this->model, 'phone', '06-12-34-56-78', []))->toBe('0612345678');
    });

    it('convertit le format international français', function () {
        expect($this->cast->set($this->model, 'phone', '+33612345678', []))->toBe('0612345678');
        expect($this->cast->set($this->model, 'phone', '+33 6 12 34 56 78', []))->toBe('0612345678');
        expect($this->cast->set($this->model, 'phone', '0033612345678', []))->toBe('0612345678');
    });

    it('formate pour affichage', function () {
        expect($this->cast->get($this->model, 'phone', '0612345678', []))->toBe('06 12 34 56 78');
    });

    it('gère les valeurs null', function () {
        expect($this->cast->set($this->model, 'phone', null, []))->toBeNull();
        expect($this->cast->get($this->model, 'phone', null, []))->toBeNull();
    });
});

describe('PostalCodeCast', function () {
    beforeEach(function () {
        $this->cast = new PostalCodeCast;
        $this->model = new class extends \Illuminate\Database\Eloquent\Model {};
    });

    it('normalise les codes postaux français', function () {
        expect($this->cast->set($this->model, 'postal_code', '75001', []))->toBe('75001');
        expect($this->cast->set($this->model, 'postal_code', '1000', []))->toBe('01000');
        expect($this->cast->set($this->model, 'postal_code', '100', []))->toBe('00100');
    });

    it('supprime les espaces', function () {
        expect($this->cast->set($this->model, 'postal_code', '75 001', []))->toBe('75001');
        expect($this->cast->set($this->model, 'postal_code', ' 75001 ', []))->toBe('75001');
    });

    it('gère les valeurs null', function () {
        expect($this->cast->set($this->model, 'postal_code', null, []))->toBeNull();
    });
});

describe('LicenseNumberCast', function () {
    beforeEach(function () {
        $this->cast = new LicenseNumberCast;
        $this->model = new class extends \Illuminate\Database\Eloquent\Model {};
    });

    it('garde uniquement les chiffres', function () {
        expect($this->cast->set($this->model, 'license', '123456', []))->toBe('123456');
        expect($this->cast->set($this->model, 'license', '12 34 56', []))->toBe('123456');
        expect($this->cast->set($this->model, 'license', '12-34-56', []))->toBe('123456');
    });

    it('supprime les espaces superflus', function () {
        expect($this->cast->set($this->model, 'license', '  123456  ', []))->toBe('123456');
    });

    it('gère les valeurs null', function () {
        expect($this->cast->set($this->model, 'license', null, []))->toBeNull();
    });
});

describe('CarBrandCast', function () {
    beforeEach(function () {
        $this->cast = new CarBrandCast;
        $this->model = new class extends \Illuminate\Database\Eloquent\Model {};
    });

    it('formate les marques connues correctement', function () {
        expect($this->cast->set($this->model, 'make', 'bmw', []))->toBe('BMW');
        expect($this->cast->set($this->model, 'make', 'BMW', []))->toBe('BMW');
        expect($this->cast->set($this->model, 'make', 'mercedes-benz', []))->toBe('Mercedes-Benz');
        expect($this->cast->set($this->model, 'make', 'MERCEDES-BENZ', []))->toBe('Mercedes-Benz');
        expect($this->cast->set($this->model, 'make', 'alfa romeo', []))->toBe('Alfa Romeo');
        expect($this->cast->set($this->model, 'make', 'citroën', []))->toBe('Citroën');
        expect($this->cast->set($this->model, 'make', 'citroen', []))->toBe('Citroën');
    });

    it('applique Title Case aux marques inconnues', function () {
        expect($this->cast->set($this->model, 'make', 'marque inconnue', []))->toBe('Marque Inconnue');
        expect($this->cast->set($this->model, 'make', 'AUTRE MARQUE', []))->toBe('Autre Marque');
    });

    it('supprime les espaces superflus', function () {
        expect($this->cast->set($this->model, 'make', '  bmw  ', []))->toBe('BMW');
    });

    it('gère les valeurs null', function () {
        expect($this->cast->set($this->model, 'make', null, []))->toBeNull();
    });
});
