<?php

use App\Services\Math\MathService;
use Brick\Math\Exception\NumberFormatException;

/**
 * Refactorizar
 * 
 */

test('Redondeo de numero ', function ($numero,$precision,$assertEquals) {
    
    $math = app(MathService::class);
    $potencia = $math->round($numero,$precision);
    expect($potencia)->toBe($assertEquals);
})
->with([
    [3,4,'3.0000'],
    [3.01230,4,'3.0123'],
    [3.01231,4,'3.0123'],
    [3.01232,4,'3.0123'],
    [3.01233,4,'3.0123'],
    [3.01234,4,'3.0123'],
    [3.01235,4,'3.0124'],// después de 5 sube al proximo decimal
    [3.01236,4,'3.0124'],// después de 5 sube al proximo decimal
    [3.01237,4,'3.0124'],// después de 5 sube al proximo decimal
    [3.01238,4,'3.0124'],// después de 5 sube al proximo decimal
    [3.01239,4,'3.0124'],// después de 5 sube al proximo decimal
    [-3.01239,4,'-3.0124'],
])
;



/**
 * Errores
 */
test('Redondeo a parámetro no numérico', function ($parametro) {
    
    $math = app(MathService::class);
    $math->round($parametro,4);
})
->with([
    'X',
    'A1',
    '0.00x',
    'x.00',
    '$'
])
->throws(NumberFormatException::class);



test('Redondeo a parámetro precision no numérico ', function () {
    
    $math = app(MathService::class);
    $math->round('10.123','x');
})->throws(TypeError::class);

test('Redondeo con precision negativa ', function () {
    
    $math = app(MathService::class);
    $math->round('10.123','-4');
})->throws(InvalidArgumentException::class);