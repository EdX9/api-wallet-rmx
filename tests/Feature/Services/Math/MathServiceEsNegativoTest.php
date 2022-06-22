<?php

use App\Services\Math\MathService;
use Brick\Math\Exception\NumberFormatException;

test('Es negativo', function ($number) {

    $math = app(MathService::class);
    $negativo = $math->isNegative($number);
    expect($negativo)->toBeTrue();
})->with([
    -1,
    '-1',
    '-0.001',
    -0.001
]);

test('No es negativo', function ($number) {

    $math = app(MathService::class);
    $negativo = $math->isNegative($number);
    expect($negativo)->toBeFalse();
})->with([
    1,
    '1',
    '0.001',
    0.001,
    '0'
]);

test('fallo negativo', function ($number) {

    $math = app(MathService::class);
    $negativo = $math->isNegative($number);
    expect($negativo)->toBeFalse();
})->with([
    'A1',
    '0.00x',
    'x.00',
    '$'
])->throws(NumberFormatException::class);