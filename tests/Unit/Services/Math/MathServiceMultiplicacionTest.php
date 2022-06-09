<?php

use App\Services\Math\MathService;
use Brick\Math\Exception\NumberFormatException;

uses(Tests\TestCase::class);

test('Multiplicación Simple', function () {

    $math = app(MathService::class);
    $multiplicacion = $math->round($math->mul(100,2));
    $this->assertEquals('200',$multiplicacion);
});


test('Multiplicación Simple con string', function () {

    $math = app(MathService::class);
    $multiplicacion = $math->round($math->mul('100','2'));
    $this->assertEquals('200',$multiplicacion);
});


test('Multiplicación con 4 decimales y escala de 4', function () {
    
    $math = app(MathService::class);
    $multiplicacion = $math->mul(150.15,0.256,4);
    $this->assertEquals('38.4384',$multiplicacion);
});

test('Multiplicación con 4 decimales con string y escala de 4', function () {

    $math = app(MathService::class);
    $multiplicacion = $math->mul('150.15','0.256',4);
    $this->assertEquals('38.4384',$multiplicacion);
});

/** Precaución con operaciones de mayores decimales */
test('[Warning] Multiplicación con 4 decimales y escala de 4', function () {
    
    $math = app(MathService::class);
    $multiplicacion = $math->mul(0.0001,0.0001,4);
    $this->assertEquals('0.0000',$multiplicacion);
});
/** Precaución con operaciones de mayores decimales */
test('[Warning] Multiplicación con 4 decimales con string y escala de 4', function () {

    $math = app(MathService::class);
    $multiplicacion = $math->mul('0.0001','0.0001',4);
    $this->assertEquals('0.0000',$multiplicacion);
});

test('Multiplicación con 4 decimales y escala de 8', function () {
    
    $math = app(MathService::class);
    $multiplicacion = $math->mul(0.0001,0.0001,8);
    $this->assertEquals('0.00000001',$multiplicacion);
});

test('Multiplicación con 4 decimales con string y escala de 8', function () {

    $math = app(MathService::class);
    $multiplicacion = $math->mul('0.0001','0.0001',8);
    $this->assertEquals('0.00000001',$multiplicacion);
});

test('Multiplicación con 4 decimales de 1 entero y un float con string', function () {

    $math = app(MathService::class);
    $multiplicacion = $math->mul('1','0.0001',4);
    $this->assertEquals('0.0001',$multiplicacion);
});

test('Multiplicación con 4 decimales de 1 entero y un float', function () {

    $math = app(MathService::class);
    $multiplicacion = $math->mul(1,0.0001,4);
    $this->assertEquals('0.0001',$multiplicacion);
});

test('Multiplicación de 0 ', function () {

    $math = app(MathService::class);
    $multiplicacion = $math->mul('0','123.123',4);
    $this->assertEquals('0.0000',$multiplicacion);
});

/**
 * Errores 
 */

test('Multiplicación de valor no numérico primer parámetro envía una excepción', function () {
    $math = app(MathService::class);
    $math->mul('0.a','0',4);
})->throws(NumberFormatException::class);

test('Multiplicación de valor no numérico segundo parámetro envía una excepción', function () {
    $math = app(MathService::class);
    $math->mul('0','0.a',4);
})->throws(NumberFormatException::class);

test('Parámetro scala de tipo string envía una excepción al enviar', function () {
    $math = app(MathService::class);
    $math->mul('0','0','X');
})->throws(TypeError::class);

