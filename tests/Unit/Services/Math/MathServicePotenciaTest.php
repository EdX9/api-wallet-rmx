<?php

use App\Services\Math\MathService;
use Brick\Math\Exception\NumberFormatException;

uses(Tests\TestCase::class);

test('Potencia de 1', function () {
    // Cualquier número elevado a la unidad da por resultado el mismo número
    $math = app(MathService::class);
    $potencia = $math->round($math->pow(1,1));
    $this->assertEquals('1',$potencia);
});

test('Potencia Simple', function () {

    $math = app(MathService::class);
    $potencia = $math->round($math->pow(50,2));
    $this->assertEquals('2500',$potencia);
});


test('Potencia Simple con string', function () {

    $math = app(MathService::class);
    $potencia = $math->round($math->pow('50','2'));
    $this->assertEquals('2500',$potencia);
});

test('Potencia con 4 decimales y escala de 4, potencia con decimales', function () {
    
    $math = app(MathService::class);
    // se omitirá el 2.2560 y se convertira en 2
    $potencia = $math->pow(150.1500,2.2560,4);
    $this->assertEquals('22545.0225',$potencia);
});
test('Potencia con 4 decimales y escala de 4', function () {
    
    $math = app(MathService::class);
    $potencia = $math->pow(150.1500,2,4);
    $this->assertEquals('22545.0225',$potencia);
});

test('Potencia con 4 decimales y escala de 4, potencia con decimales con string', function () {
    
    $math = app(MathService::class);
    // se omitirá el 2.2560 y se convertira en 2
    $potencia = $math->pow('150.1500','2.2560',4);
    $this->assertEquals('22545.0225',$potencia);
});
test('Potencia con 4 decimales y escala de 4 con string', function () {
    
    $math = app(MathService::class);
    $potencia = $math->pow('150.1500','2',4);
    $this->assertEquals('22545.0225',$potencia);
});

test('Potencia de 0 ', function () {

    $math = app(MathService::class);
    $potencia = $math->pow('0','123.123',4);
    $this->assertEquals('0.0000',$potencia);
});
test('Numero a la 0 potencia ', function () {
    // Cualquier número elevado a cero es igual a la unidad
    $math = app(MathService::class);
    $potencia = $math->pow('10','0',4);
    $this->assertEquals('1.0000',$potencia);
});

test('Parametro Exponente no debe ser menor a 0', function () {
    $math = app(MathService::class);
    // 0.1 sera convertido a 0
    // Cualquier número elevado a cero es igual a la unidad
    $potencia = $math->pow('2','0.1',4);
    $this->assertEquals('1.0000',$potencia);
});


/**
 * Errores 
 */

test('Numero a la potencia negativa', function () {
    // Cualquier número elevado a cero es igual a la unidad
    $math = app(MathService::class);
    $math->pow('10','-3',4);
})->throws(InvalidArgumentException::class);

test('Potencia de valor no numérico primer parámetro envía una excepción', function () {
    $math = app(MathService::class);
    $math->pow('0.a','2',4);
})->throws(NumberFormatException::class);

test('Potencia de valor no numérico', function () {
    // Cualquier número elevado a cero es igual a la unidad
    $math = app(MathService::class);
    $math->pow('5','x','4');
})->throws(TypeError::class);

test('Parámetro scala de tipo string envía una excepción al enviar', function () {
    $math = app(MathService::class);
    $math->pow('0','0','X');
})->throws(TypeError::class);

test('Parametro Exponente no debe ser mayor a 1000000', function () {
    $math = app(MathService::class);
    $math->pow('2','1000001',4);
})->throws(InvalidArgumentException::class);

