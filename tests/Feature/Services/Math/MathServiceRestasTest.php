<?php

use App\Services\Math\MathService;
use Brick\Math\Exception\NumberFormatException;



test('Resta ', function ($numero1,$numero2,$precision,$assertEquals) {

    $math = app(MathService::class);
    $resta = $math->sub($numero1,$numero2,$precision);
    expect($resta)->toBe($assertEquals);
})
->with([
    [1,2,0,'-1'],
    ['1','2',0,'-1'],
    [0.0001,0.0001,4,'0.0000'],
    ['0.0001','0.0001',4,'0.0000'],
    ['1','0.0001',4,'0.9999'],
    [1,0.0001,4,'0.9999'],
    ['0','0',4,'0.0000']
]);



/**
 * Errores 
 */

test('Resta de valor no numérico primer parámetro envía una excepción', function () {
    $math = app(MathService::class);
    $math->sub('0.a','0',4);
})->throws(NumberFormatException::class);

test('Resta de valor no numérico segundo parámetro envía una excepción', function () {
    $math = app(MathService::class);
    $math->sub('0','0.a',4);
})->throws(NumberFormatException::class);

test('Parámetro scala de tipo string envía una excepción al enviar', function () {
    $math = app(MathService::class);
    $math->sub('0','0','X');
})->throws(TypeError::class);