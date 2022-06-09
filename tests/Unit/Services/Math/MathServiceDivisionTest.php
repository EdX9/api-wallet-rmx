<?php

use App\Services\Math\MathService;
use Brick\Math\Exception\NumberFormatException;
use Brick\Math\Exception\DivisionByZeroException;

uses(Tests\TestCase::class);

test('Division Simple', function () {

    $math = app(MathService::class);
    $division = $math->round($math->div(2,2));
    $this->assertEquals('1',$division);
});


test('Division Simple con string', function () {

    $math = app(MathService::class);
    $division = $math->round($math->div('2','2'));
    $this->assertEquals('1',$division);
});

test('Division con 4 decimales', function () {

        $math = app(MathService::class);
        $division = $math->div(1,0.0001,4);
        $this->assertEquals('10000.0000',$division);
});

test('Division con 4 decimales con string', function () {

    $math = app(MathService::class);
    $division = $math->round($math->div('1','0.0001',4));
    
    $this->assertEquals('10000',$division);
});

test('Division con 4 decimales de 1 entero y un float con string', function () {

    $math = app(MathService::class);
    $division = $math->div('1','0.0001',4);
    $this->assertEquals('10000.0000',$division);
});

test('Division con 4 decimales de 1 entero y un float', function () {

    $math = app(MathService::class);
    $division = $math->div(1,0.0001,4);
    $this->assertEquals('10000.0000',$division);
});

test('Division de 0 ', function () {

    $math = app(MathService::class);
    $division = $math->div('0','0',4);
    $this->assertEquals('',$division);
})->throws(DivisionByZeroException::class);;

/**
 * Errores 
 */

test('Division de valor no numérico primer parámetro envía una excepción', function () {
    $math = app(MathService::class);
    $math->div('0.a','0',4);
})->throws(NumberFormatException::class);

test('Division de valor no numérico segundo parámetro envía una excepción', function () {
    $math = app(MathService::class);
    $math->div('0','0.a',4);
})->throws(NumberFormatException::class);

test('Parámetro scala de tipo string envía una excepción al enviar', function () {
    $math = app(MathService::class);
    $math->div('0','0','X');
})->throws(TypeError::class);
