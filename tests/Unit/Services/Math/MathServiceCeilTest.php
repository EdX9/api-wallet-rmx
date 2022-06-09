<?php

use App\Services\Math\MathService;
use Brick\Math\Exception\NumberFormatException;

uses(Tests\TestCase::class);

test('Redondeo a valor mas alto', function () {
    
    $math = app(MathService::class);
    $potencia = $math->ceil(3.0123);
    $this->assertEquals('4',$potencia);
});
test('Redondeo a valor mas alto string', function () {
    
    $math = app(MathService::class);
    $potencia = $math->ceil('3.0123');
    $this->assertEquals('4',$potencia);
});

test('Redondeo a valor mas alto negativo', function () {
    
    $math = app(MathService::class);
    $potencia = $math->ceil(-3.0123);
    $this->assertEquals('-3',$potencia);
});

test('Redondeo a valor mas alto negativo string', function () {
    
    $math = app(MathService::class);
    $potencia = $math->ceil('-3.0123');
    $this->assertEquals('-3',$potencia);
});
/**
 * Errores
 */
test('Redondeo a parámetro no numérico', function () {
    
    $math = app(MathService::class);
    $potencia = $math->ceil('x');
    $this->assertEquals('-3',$potencia);
})->throws(NumberFormatException::class);

test('Redondeo a parámetro no numérico después del punto decimal ', function () {
    
    $math = app(MathService::class);
    $potencia = $math->ceil('0.x');
    $this->assertEquals('-3',$potencia);
})->throws(NumberFormatException::class);