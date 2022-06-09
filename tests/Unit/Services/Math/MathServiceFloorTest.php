<?php
use App\Services\Math\MathService;
use Brick\Math\Exception\NumberFormatException;
use Brick\Math\Exception\DivisionByZeroException;

uses(Tests\TestCase::class);

test('Redondeo a valor mas bajo', function () {
    
    $math = app(MathService::class);
    $potencia = $math->floor(3.0001);
    $this->assertEquals('3',$potencia);
});

test('Redondeo a valor mas bajo string', function () {
    
    $math = app(MathService::class);
    $potencia = $math->floor('3.0001');
    $this->assertEquals('3',$potencia);
});

test('Redondeo a valor mas bajo 2', function () {
    
    $math = app(MathService::class);
    $potencia = $math->floor(3.9990);
    $this->assertEquals('3',$potencia);
});

test('Redondeo a valor mas bajo 2 string', function () {
    
    $math = app(MathService::class);
    $potencia = $math->floor('3.9990');
    $this->assertEquals('3',$potencia);
});

/**
 * Errores
 */
test('Redondeo a parámetro no numérico', function () {
    
    $math = app(MathService::class);
    $math->floor('x');
})->throws(NumberFormatException::class);

test('Redondeo a parámetro no numérico después del punto decimal ', function () {
    
    $math = app(MathService::class);
    $math->floor('0.x');
})->throws(NumberFormatException::class);