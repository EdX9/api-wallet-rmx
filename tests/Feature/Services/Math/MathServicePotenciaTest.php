<?php

use App\Services\Math\MathService;
use Brick\Math\Exception\NumberFormatException;



test('Potencia de ', function ($numero,$pow,$precision,$assertEquals) {
    
    $math = app(MathService::class);
    $potencia = $math->round($math->pow($numero,$pow),$precision);
    expect($potencia)->toBe($assertEquals);
})
->with([
    [ 1 , 1 , 0 , '1'], // Cualquier número elevado a la unidad da por resultado el mismo número
    [50 , 2 , 0 , '2500'],
    ['50', '2' , 0 , '2500'],
    [ 150.1500 , 2.2560,4,'22545.0225'],// 2.2560 se convertirá en 2
    [ 150.1500 , 2     ,4,'22545.0225'],
    ['150.1500','2'    ,4,'22545.0225'],
    ['0', '123.123', 4 ,'0.0000'], // cero a cualquier potencia es 0
    ['10',0,4,'1.0000'], // Cualquier número elevado a cero es igual a la unidad
]);


/**
 * Errores 
 */

test('Numero a la potencia negativa', function () {
    $math = app(MathService::class);
    $math->pow('10','-3',4);
})->throws(InvalidArgumentException::class);



test('Potencia de valor no numérico primer parámetro envía una excepción', function () {
    $math = app(MathService::class);
    $math->pow('0.a','2',4);
})->throws(NumberFormatException::class);

test('Potencia de valor no numérico', function () {
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

