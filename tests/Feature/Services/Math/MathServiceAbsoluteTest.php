<?php
use App\Services\Math\MathService;
use Brick\Math\Exception\NumberFormatException;



test('Numero Absoluto', function ($numero,$assertEquals,$precision) {
    
    $math = app(MathService::class);
    $potencia = $math->round($math->abs($numero),$precision);
    expect($potencia)->toBe($assertEquals);
})->with([
    [-3.1416,'3.1416',4],
    ['-3.1416','3.1416',4],
    [3.1416,'3.1416',4],
    ['3.1416','3.1416',4],
    [3,'3',0],
    [-3,'3',0],
    ['3','3',0],
    ['-3','3',0],
]);

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