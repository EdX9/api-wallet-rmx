<?php
use App\Services\Math\MathService;
use Brick\Math\Exception\NumberFormatException;

uses(Tests\TestCase::class);

test('Numero Absoluto de numero con punto decimal negativo', function () {
    
    $math = app(MathService::class);
    $potencia = $math->round($math->abs(-3.1416),4);
    $this->assertEquals('3.1416',$potencia);
});

test('Numero Absoluto de numero con punto decimal negativo con String', function () {
    
    $math = app(MathService::class);
    $potencia = $math->round($math->abs('-3.1416'),4);
    $this->assertEquals('3.1416',$potencia);
});

test('Numero Absoluto de numero con punto decimal', function () {
    
    $math = app(MathService::class);
    $potencia = $math->round($math->abs(3.1416),4);
    $this->assertEquals('3.1416',$potencia);
});

test('Numero Absoluto de numero con punto decimal con String', function () {
    
    $math = app(MathService::class);
    $potencia = $math->round($math->abs('3.1416'),4);
    $this->assertEquals('3.1416',$potencia);
});

test('Numero Absoluto de numero negativo', function () {
    
    $math = app(MathService::class);
    $potencia = $math->round($math->abs(-3));
    $this->assertEquals('3',$potencia);
});

test('Numero Absoluto de numero negativo con String', function () {
    
    $math = app(MathService::class);
    $potencia = $math->round($math->abs('-3'));
    $this->assertEquals('3',$potencia);
});

test('Numero Absoluto de numero positivo', function () {
    
    $math = app(MathService::class);
    $potencia = $math->round($math->abs(3));
    $this->assertEquals('3',$potencia);
});

test('Numero Absoluto de numero positivo con String', function () {
    
    $math = app(MathService::class);
    $potencia = $math->round($math->abs(3));
    $this->assertEquals('3',$potencia);
});
/**
 * Errores
 */
test('Numero Absoluto paramento no numérico', function () {
    
    $math = app(MathService::class);
    $math->abs('x');
})->throws(NumberFormatException::class);

test('Numero Absoluto paramento no numérico después del punto decimal', function () {
    
    $math = app(MathService::class);
    $math->abs('0.x');
})->throws(NumberFormatException::class);