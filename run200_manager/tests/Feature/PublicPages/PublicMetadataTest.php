<?php

test('unknown public URL keeps the 404 status and offers useful navigation', function () {
    $this->get('/adresse-inexistante')
        ->assertNotFound()
        ->assertSee('Page introuvable')
        ->assertSee('Retour à l’accueil')
        ->assertSee(route('public.calendar'), false)
        ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
});

test('main public pages expose distinct descriptions and social previews', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Inscrivez-vous aux courses RUN200', false)
        ->assertSee('<meta property="og:image"', false);

    $this->get(route('public.calendar'))
        ->assertOk()
        ->assertSee('Consultez le calendrier des courses RUN200', false)
        ->assertSee('<meta property="og:title" content="Calendrier des courses - RUN200">', false);

    $this->get(route('public.standings'))
        ->assertOk()
        ->assertSee('Retrouvez le classement du championnat RUN200', false);
});

test('registration has a specific browser title', function () {
    $this->get(route('register'))
        ->assertOk()
        ->assertSee('<title>Créer un compte pilote - RUN200</title>', false);
});
