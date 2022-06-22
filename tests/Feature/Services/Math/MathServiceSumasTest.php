<?php


use App\Services\Math\MathService;
use Brick\Math\Exception\NumberFormatException;



test('Suma ', function ($numero1,$numero2,$precision,$assertEquals) {

    $math = app(MathService::class);
    $sum = $math->add($numero1,$numero2,$precision);
    expect($sum)->toBe($assertEquals);
})
->with([
    [1,2,0,'3'],
    ['1','2',0,'3'],
    [0.0001,0.0001,4,'0.0002'],
    ['0.0001','0.0001',4,'0.0002'],
    ['1','0.0001',4,'1.0001'],
    [1,0.0001,4,'1.0001'],
    ['0','0',4,'0.0000']
]);




/**
 * Errores CORREGIR
 */

test('Suma de valor no numérico primer parámetro envía una excepción', function ($parametro1,$parametro2) {
    $math = app(MathService::class);
    $math->add($parametro1,$parametro2,4);
})
->with([
    ['0.a','0'],
    ['0','0.a'],
])
->throws(NumberFormatException::class);

test('Parámetro scala de tipo string envía una excepción al enviar', function () {

    $math = app(MathService::class);
    $math->add('0','0','X');
})->throws(TypeError::class);