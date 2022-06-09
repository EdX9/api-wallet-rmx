<?php


use App\Services\Math\MathService;
use Brick\Math\Exception\NumberFormatException;

uses(Tests\TestCase::class);

test('Suma Simple', function () {

    $math = app(MathService::class);
    $sum = $math->round($math->add(1,2));
    $this->assertEquals('3',$sum);
});


test('Suma Simple con string', function () {

    $math = app(MathService::class);
    $sum = $math->round($math->add('1','2'));
    $this->assertEquals('3',$sum);
});

test('Suma con 4 decimales', function () {

        $math = app(MathService::class);
        $sum = $math->add(0.0001,0.0001,4);
        $this->assertEquals('0.0002',$sum);
});

test('Suma con 4 decimales con string', function () {

    $math = app(MathService::class);
    $sum = $math->add('0.0001','0.0001',4);
    $this->assertEquals('0.0002',$sum);
});

test('Suma con 4 decimales de 1 entero y un float con string', function () {

    $math = app(MathService::class);
    $sum = $math->add('1','0.0001',4);
    $this->assertEquals('1.0001',$sum);
});

test('Suma con 4 decimales de 1 entero y un float', function () {

    $math = app(MathService::class);
    $sum = $math->add(1,0.0001,4);
    $this->assertEquals('1.0001',$sum);
});

test('Suma de 0 ', function () {

    $math = app(MathService::class);
    $sum = $math->add('0','0',4);
    $this->assertEquals('0.0000',$sum);
});

/**
 * Errores 
 */

test('Suma de valor no numérico primer parámetro envía una excepción', function () {
    $math = app(MathService::class);
    $math->add('0.a','0',4);
})->throws(NumberFormatException::class);

test('Suma de valor no numérico segundo parámetro envía una excepción', function () {
    $math = app(MathService::class);
    $math->add('0','0.a',4);
})->throws(NumberFormatException::class);

test('Parámetro scala de tipo string envía una excepción al enviar', function () {

    $math = app(MathService::class);
    $math->add('0','0','X');
})->throws(TypeError::class);