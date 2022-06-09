<?php

use App\Services\Math\MathService;
use Brick\Math\Exception\NumberFormatException;

uses(Tests\TestCase::class);

test('Numero positivo a Negativo', function () {
    
    $math = app(MathService::class);
    $negativo = $math->round($math->negative(10)); // si es numérico se debe usar round
    $this->assertEquals('-10',$negativo);
});

test('Numero positivo a Negativo string', function () {
    
    $math = app(MathService::class);
    $negativo = $math->negative('10');
    $this->assertEquals('-10',$negativo);
});

test('Numero positivo con decimal a Negativo', function () {
    
    $math = app(MathService::class);
    $negativo = $math->round($math->negative(10.234324),6);// si es numérico se debe usar round
    $this->assertEquals('-10.234324',$negativo);
});

test('Numero positivo con decimal a Negativo string', function () {
    
    $math = app(MathService::class);
    $negativo = $math->negative('10.234324');
    $this->assertEquals('-10.234324',$negativo);
});

test('Numero negativo a Positivo', function () {
    
    $math = app(MathService::class);
    $negativo = $math->round($math->negative(-10));// si es numérico se debe usar round
    $this->assertEquals('10',$negativo);
});

test('Numero negativo a Positivo string', function () {
    
    $math = app(MathService::class);
    $negativo = $math->negative('-10');
    $this->assertEquals('10',$negativo);
});

test('Numero negativo con decimal a Positivo', function () {
    
    $math = app(MathService::class);
    $negativo = $math->round($math->negative(-10.234324),6);// si es numérico se debe usar round
    $this->assertEquals('10.234324',$negativo);
});

test('Numero negativo con decimal a Positivo string', function () {
    
    $math = app(MathService::class);
    $negativo = $math->negative('-10.234324');
    $this->assertEquals('10.234324',$negativo);
});

/**
 * Errors
 */

test('Parámetro no numérico ', function () {
    
    $math = app(MathService::class);
    $math->abs('x');
})->throws(NumberFormatException::class);

test('Parámetro no numérico después del punto decimal ', function () {
    
    $math = app(MathService::class);
    $math->abs('0.x');
})->throws(NumberFormatException::class);