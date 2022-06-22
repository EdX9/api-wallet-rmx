<?php

use App\Services\Math\MathService;
use Brick\Math\Exception\NumberFormatException;



test('Numero a Negativo', function ($numero,$precision,$assertEquals) {
    
    $math = app(MathService::class);
    $negativo = $math->round($math->negative($numero),$precision); // si es numérico se debe usar round
    expect($negativo)->toBe($assertEquals);
})
->with([
    [ 10 ,0, '-10' ],
    ['10',0, '-10' ],
    [10.234324,6,'-10.234324'],
    ['10.234324',6,'-10.234324'],
    ['-10',0,'10'],
    [ -10 ,0,'10'],
    [-10.234324,6,'10.234324'],
    ['-10.234324',6,'10.234324']
]);



/**
 * Errors
 */

test('Parámetro no numérico ', function ($parametro) {
    
    $math = app(MathService::class);
    $math->negative($parametro);
})->with([
    'X',
    'A1',
    '0.00x',
    'x.00',
    '$'
])->throws(NumberFormatException::class);

