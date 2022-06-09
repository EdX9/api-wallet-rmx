<?php

use App\Services\Math\MathService;
use Brick\Math\Exception\NumberFormatException;

uses(Tests\TestCase::class);

test('Comparar Números iguales enteros', function () {
    
    $math = app(MathService::class);
    $compracion = $math->compare(10,10);
    $this->assertEquals('0',$compracion);
});

test('Comparar Números iguales enteros string', function () {
    
    $math = app(MathService::class);
    $compracion = $math->compare('10','10');
    $this->assertEquals('0',$compracion);
});

test('Comparar Números iguales decimales', function () {
    
    $math = app(MathService::class);
    $compracion = $math->compare(10.123,10.123);
    $this->assertEquals('0',$compracion);
});

test('Comparar Números iguales decimales string', function () {
    
    $math = app(MathService::class);
    $compracion = $math->compare('10.123','10.123');
    $this->assertEquals('0',$compracion);
});

test('Comparar Números iguales enteros negativos', function () {
    
    $math = app(MathService::class);
    $compracion = $math->compare(-10,-10);
    $this->assertEquals('0',$compracion);
});

test('Comparar Números iguales enteros negativos string', function () {
    
    $math = app(MathService::class);
    $compracion = $math->compare('-10','-10');
    $this->assertEquals('0',$compracion);
});

test('Comparar Números iguales decimales negativos', function () {
    
    $math = app(MathService::class);
    $compracion = $math->compare(-10.123,-10.123);
    $this->assertEquals('0',$compracion);
});

test('Comparar Números iguales decimales negativos string', function () {
    
    $math = app(MathService::class);
    $compracion = $math->compare('-10.123','-10.123');
    $this->assertEquals('0',$compracion);
});


test('Comparar Números (enteros) distintos x mayor que y ', function () {
    
    $math = app(MathService::class);
    $compracion = $math->compare(20,10);
    $this->assertEquals('1',$compracion);
});

test('Comparar Números (enteros) distintos x mayor que y string', function () {
    
    $math = app(MathService::class);
    $compracion = $math->compare('20','10');
    $this->assertEquals('1',$compracion);
});

test('Comparar Números (con decimales) distintos x mayor que y', function () {
    
    $math = app(MathService::class);
    $compracion = $math->compare(10.1,10.001);
    $this->assertEquals('1',$compracion);
});

test('Comparar Números (con decimales) distintos x mayor que y string ', function () {
    
    $math = app(MathService::class);
    $compracion = $math->compare('10.1','10.001');
    $this->assertEquals('1',$compracion);
});

test('Comparar Números (con decimales) distintos x menor que y ', function () {
    
    $math = app(MathService::class);
    $compracion = $math->compare(10.001,10.1);
    $this->assertEquals('-1',$compracion);
});

test('Comparar Números (con decimales) distintos x menor que y string', function () {
    
    $math = app(MathService::class);
    $compracion = $math->compare('10.001','10.1');
    $this->assertEquals('-1',$compracion);
});

test('Comparar Números distintos x (decimal) mayor que y (entero) ', function () {
    
    $math = app(MathService::class);
    $compracion = $math->compare(10.1,10);
    $this->assertEquals('1',$compracion);
});

test('Comparar Números distintos x (decimal) mayor que y (entero) string', function () {
    
    $math = app(MathService::class);
    $compracion = $math->compare('10.1','10');
    $this->assertEquals('1',$compracion);
});
