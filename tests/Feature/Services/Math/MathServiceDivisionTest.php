<?php

use App\Services\Math\MathService;
use Brick\Math\Exception\NumberFormatException;
use Brick\Math\Exception\DivisionByZeroException;




test('Division con 4 decimales', function ($number1,$number2,$precision,$assertEquals) {

        $math = app(MathService::class);
        $division = $math->div($number1,$number2,$precision);
        expect($division)->toBe($assertEquals);
})->with([
    [ 1 , 0.0001 , 4, '10000.0000'],
    ['1','0.0001', 4, '10000.0000'],
    ['2','2', 4, '1.0000'],
    [ 2 , 2 , 4, '1.0000'],
]);



test('Division de 0 ', function () {

    $math = app(MathService::class);
    $division = $math->div('0','0',4);
    expect($division)->toBe('');
})->throws(DivisionByZeroException::class);;

/**
 * Errores 
 */

test('Division de valor no numérico primer parámetro envía una excepción', function ($parametro1,$parametro2) {
    $math = app(MathService::class);
    $math->div($parametro1,$parametro2,4);
})
->with([
    ['0.a','0'],
    ['0','0.a'],
])
->throws(NumberFormatException::class);

test('Parámetro scala de tipo string envía una excepción al enviar', function ($precision) {
    $math = app(MathService::class);
    $math->div('0','0',$precision);
})
->with([
    'X',
    '',
    '$'
])
->throws(TypeError::class);
