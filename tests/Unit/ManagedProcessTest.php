<?php

use App\Support\ManagedProcess\Facades\ManagedProcess;

test('can start a managed process', function () {
    expect(ManagedProcess::get('alias'))
        ->running()->not->toBeTrue();

    expect(ManagedProcess::start('alias', 'sleep 20s'))
        ->running()->toBeTrue();

    expect(ManagedProcess::get('alias'))
        ->running()->toBeTrue();
});

test('can retrieve a managed process after it was started', function () {
    // Expects the previous test ran first

    expect(ManagedProcess::get('alias'))
        ->running()->toBeTrue();
});

test('can stop a managed process', function () {
    expect(ManagedProcess::get('alias'))
        ->stop()
        ->running()->not->toBeTrue();

    expect(ManagedProcess::start('alias', 'sleep 5s'))
        ->running()->toBeTrue()
        ->stop()
        ->running()->not->toBeTrue();
});

test('can restart a managed process', function () {
    expect(ManagedProcess::get('alias'))
        ->stop()
        ->running()->not->toBeTrue();

    expect(ManagedProcess::start('alias', 'sleep 5s'))
        ->running()->toBeTrue()
        ->stop()
        ->running()->not->toBeTrue()
        ->restart()
        ->running()->toBeTrue();
});

test('cant restart a process that is not managed');
