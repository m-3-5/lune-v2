<?php

use App\Support\CheckfrontDates;
use Tests\TestCase;

uses(TestCase::class);

it('converts checkfront unix timestamp to rome calendar date', function () {
    config(['checkfront.timezone' => 'Europe/Rome']);

    // Mezzanotte 22 lug 2026 a Roma (22:00 UTC del 21)
    expect(CheckfrontDates::calendarDate(1784671200))->toBe('2026-07-22');
    expect(CheckfrontDates::toCheckInDatetime(1784671200))->toBe('2026-07-22 16:00:00');
});
