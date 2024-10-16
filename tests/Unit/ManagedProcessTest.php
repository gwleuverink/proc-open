<?php

use App\Support\ManagedProcess\Facades\ManagedProcess;

test('can start a managed process', function () {
    expect(ManagedProcess::get('process-key'))
        ->running()->not->toBeTrue();

    expect(ManagedProcess::start('process-key', 'sleep 20s'))
        ->running()->toBeTrue();

    expect(ManagedProcess::get('process-key'))
        ->running()->toBeTrue();
});

test('can retrieve a managed process after it was started', function () {
    $this->wip(note: 'Assumes the previous test ran first');

    expect(ManagedProcess::get('process-key'))
        ->running()->toBeTrue();
});

test('can stop a managed process', function () {
    expect(ManagedProcess::get('process-key'))
        ->running()->toBeTrue()
        ->stop()
        ->running()->not->toBeTrue();
});

test('can restart a managed process');

test('cant restart a process that is not managed');
