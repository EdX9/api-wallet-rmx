<?php

use App\Services\Math\MathService;
use Brick\Math\Exception\NumberFormatException;



test('Diez a la Potencia de ', function ($numero,$assertEqual) {
    $math = app(MathService::class);
    $potencia = $math->round($math->powTen($numero));
    expect($potencia)->toBe($assertEqual);
})
->with([
    [1,'10'],
    [2,'100'],
    [ 50 ,'100000000000000000000000000000000000000000000000000'],
    ['50','100000000000000000000000000000000000000000000000000'],
    [ 1.5000 ,'10'], // se omitirá el 1.5000 y se convertira en 1
    ['1.5000','10'], // se omitirá el 1.5000 y se convertira en 1
    ['0','1'] 
]);


/**
 * Errores 
 */
test('Potencia de valor no numérico', function ($parametro) {
    $math = app(MathService::class);
    $math->powTen($parametro);
})->with([
    'X',
    'A1',
    '0.00x',
    'x.00',
    '$'
])->throws(TypeError::class);

test('Parametro Exponente negativo', function () {
    $math = app(MathService::class);
    $math->powTen('-5',);
})->throws(InvalidArgumentException::class);


test('Parametro Exponente no debe ser mayor a 1000000', function () {
    $math = app(MathService::class);
    $math->powTen('1000001');
})->throws(InvalidArgumentException::class);

