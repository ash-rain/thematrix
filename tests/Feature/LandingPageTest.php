<?php

test('landing page loads for guests', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('THE MATRIX')
        ->assertSee('TERMINAL')
        ->assertSee('ENTER THE MATRIX')
        ->assertSee('PLAY NOW')
        ->assertSee('JACK IN');
});

test('landing page shows all characters', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('NEO')
        ->assertSee('TRINITY')
        ->assertSee('MORPHEUS')
        ->assertSee('NIOBE')
        ->assertSee('GHOST');
});

test('landing page displays feature sections', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('AI NARRATOR')
        ->assertSee('SCENE GENERATION')
        ->assertSee('MEANINGFUL CHOICES')
        ->assertSee('CONVERSATION MEMORY')
        ->assertSee('COMBAT SYSTEM');
});
