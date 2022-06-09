<?php

use App\Services\Math\MathService;
use Brick\Math\Exception\NumberFormatException;

uses(Tests\TestCase::class);

test('Resta Simple', function () {

    $math = app(MathService::class);
    $resta = $math->round($math->sub(1,2));
    $this->assertEquals('-1',$resta);
});


test('Resta Simple con string', function () {

    $math = app(MathService::class);
    $resta = $math->round($math->sub('1','2'));
    $this->assertEquals('-1',$resta);
});

test('Resta con 4 decimales', function () {
    
    $math = app(MathService::class);
    $resta = $math->sub(0.0001,0.0001,4);
    $this->assertEquals('0.0000',$resta);
});

test('Resta con 4 decimales con string', function () {

    $math = app(MathService::class);
    $resta = $math->sub('0.0001','0.0001',4);
    $this->assertEquals('0.0000',$resta);
});

test('Resta con 4 decimales de 1 entero y un float con string', function () {

    $math = app(MathService::class);
    $resta = $math->sub('1','0.0001',4);
    $this->assertEquals('0.9999',$resta);
});

test('Resta con 4 decimales de 1 entero y un float', function () {

    $math = app(MathService::class);
    $resta = $math->sub(1,0.0001,4);
    $this->assertEquals('0.9999',$resta);
});

test('Resta de 0 ', function () {

    $math = app(MathService::class);
    $resta = $math->sub('0','0',4);
    $this->assertEquals('0.0000',$resta);
});

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