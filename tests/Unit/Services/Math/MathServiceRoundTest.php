<?php

use App\Services\Math\MathService;
use Brick\Math\Exception\NumberFormatException;

uses(Tests\TestCase::class);

test('Redondeo de numero entero', function () {
    
    $math = app(MathService::class);
    $potencia = $math->round(3,4);
    $this->assertEquals('3.0000',$potencia);
});

test('Redondeo de numero con 5 decimales a 4 ultimo decimal 0', function () {
    
    $math = app(MathService::class);
    $potencia = $math->round(3.01230,4);
    $this->assertEquals('3.0123',$potencia);
});

test('Redondeo de numero con 5 decimales a 4 ultimo decimal 1', function () {
    
    $math = app(MathService::class);
    $potencia = $math->round(3.01231,4);
    $this->assertEquals('3.0123',$potencia);
});

test('Redondeo de numero con 5 decimales a 4 ultimo decimal 2', function () {
    
    $math = app(MathService::class);
    $potencia = $math->round(3.01232,4);
    $this->assertEquals('3.0123',$potencia);
});
test('Redondeo de numero con 5 decimales a 4 ultimo decimal 3', function () {
    
    $math = app(MathService::class);
    $potencia = $math->round(3.01233,4);
    $this->assertEquals('3.0123',$potencia);
});
test('Redondeo de numero con 5 decimales a 4 ultimo decimal 4', function () {
    
    $math = app(MathService::class);
    $potencia = $math->round(3.01234,4);
    $this->assertEquals('3.0123',$potencia);
});

test('Redondeo de numero con 5 decimales a 4 ultimo decimal 5 Sube', function () {
    
    $math = app(MathService::class);
    $potencia = $math->round(3.01235,4);
    $this->assertEquals('3.0124',$potencia);
});

test('Redondeo de numero con 5 decimales a 4 ultimo decimal 6 Sube', function () {
    
    $math = app(MathService::class);
    $potencia = $math->round(3.01236,4);
    $this->assertEquals('3.0124',$potencia);
});

test('Redondeo de numero con 5 decimales a 4 ultimo decimal 7 Sube', function () {
    
    $math = app(MathService::class);
    $potencia = $math->round(3.01237,4);
    $this->assertEquals('3.0124',$potencia);
});

test('Redondeo de numero con 5 decimales a 4 ultimo decimal 8 Sube', function () {
    
    $math = app(MathService::class);
    $potencia = $math->round(3.01238,4);
    $this->assertEquals('3.0124',$potencia);
});

test('Redondeo de numero con 5 decimales a 4 ultimo decimal 9 Sube', function () {
    
    $math = app(MathService::class);
    $potencia = $math->round(3.01239,4);
    $this->assertEquals('3.0124',$potencia);
});


/**
 * Errores
 */
test('Redondeo a parámetro no numérico', function () {
    
    $math = app(MathService::class);
    $math->round('x',4);
})->throws(NumberFormatException::class);

test('Redondeo a parámetro no numérico después del punto decimal ', function () {
    
    $math = app(MathService::class);
    $math->round('0.x',4);
})->throws(NumberFormatException::class);


test('Redondeo a parámetro precision no numérico ', function () {
    
    $math = app(MathService::class);
    $math->round('10.123','x');
})->throws(TypeError::class);