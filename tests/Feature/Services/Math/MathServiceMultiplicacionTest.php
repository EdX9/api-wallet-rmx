<?php

use App\Services\Math\MathService;
use Brick\Math\Exception\NumberFormatException;



test('Multiplicación ', function ($numero1,$numero2,$assertEquals,$precision) {

    $math = app(MathService::class);
    $multiplicacion = $math->mul($numero1,$numero2,$precision);
    expect($multiplicacion)->toBe($assertEquals);
})
->with([
    [ 100 , 2 ,'200',0],
    ['100','2','200',0],
    [ 150.15 , 0.256 ,'38.4384',4],
    ['150.15','0.256','38.4384',4],
    [ 0.0001 , 0.0001 ,'0.0000',4],/** Precaución con operaciones de mayores decimales */
    ['0.0001','0.0001','0.0000',4],/** Precaución con operaciones de mayores decimales */
    [ 0.0001 , 0.0001 ,'0.00000001',8],
    ['0.0001','0.0001','0.00000001',8],
    ['1','0.0001','0.0001',4],
    [ 1 , 0.0001 ,'0.0001',4],
    ['0','123.123','0.0000',4]
]);


/**
 * Errores 
 */

test('Multiplicación de valor no numérico primer parámetro envía una excepción', function ($parametro1,$parametro2) {
    $math = app(MathService::class);
    $math->mul($parametro1,$parametro2,4);
})->with([
    ['0.a','0'],
    ['0','0.a'],
])->throws(NumberFormatException::class);


test('Parámetro scala de tipo string envía una excepción al enviar', function ($precision) {
    $math = app(MathService::class);
    $math->mul('0','0',$precision);
})->with([
    'X',
    'A1',
    '0.00x',
    'x.00',
    '$'
])->throws(TypeError::class);

