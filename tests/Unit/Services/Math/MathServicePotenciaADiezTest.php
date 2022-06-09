<?php

use App\Services\Math\MathService;
use Brick\Math\Exception\NumberFormatException;

uses(Tests\TestCase::class);

test('Diez a la Potencia de 1', function () {
    $math = app(MathService::class);
    $potencia = $math->round($math->powTen(1));
    $this->assertEquals('10',$potencia);
});

test('Potencia Simple', function () {

    $math = app(MathService::class);
    $potencia = $math->round($math->powTen(2));
    $this->assertEquals('100',$potencia);
});

test('Potencia amplia', function () {

    $math = app(MathService::class);
    $potencia = $math->round($math->powTen(50));
    $this->assertEquals('100000000000000000000000000000000000000000000000000',$potencia);
});


test('Potencia amplia con string', function () {

    $math = app(MathService::class);
    $potencia = $math->round($math->powTen('50','2'));
    $this->assertEquals('100000000000000000000000000000000000000000000000000',$potencia);
});

test('Potencia con 4 decimales', function () {
    
    $math = app(MathService::class);
    // se omitirá el 1.5000 y se convertira en 1
    $potencia = $math->round($math->powTen(1.5000));
    $this->assertEquals('10',$potencia);
});
test('Potencia con 4 decimales con string', function () {
    
    $math = app(MathService::class);
    // se omitirá el 1.5000 y se convertira en 1
    $potencia = $math->round($math->powTen('1.5000'));
    $this->assertEquals('10',$potencia);
});
test('Potencia de 0 ', function () {

    $math = app(MathService::class);
    $potencia = $math->round($math->powTen('0'));
    $this->assertEquals('1',$potencia);
});


/**
 * Errores 
 */
test('Potencia de valor no numérico se tranforma en 0', function () {
    $math = app(MathService::class);
    $math->powTen('x');
})->throws(TypeError::class);

test('Parametro Exponente negativo', function () {
    $math = app(MathService::class);
    $math->powTen('-5',);
})->throws(InvalidArgumentException::class);


test('Parametro Exponente no debe ser mayor a 1000000', function () {
    $math = app(MathService::class);
    $math->powTen('1000001');
})->throws(InvalidArgumentException::class);

