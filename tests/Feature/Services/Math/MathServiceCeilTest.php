<?php

use App\Services\Math\MathService;
use Brick\Math\Exception\NumberFormatException;



test('Redondeo a valor mas alto', function ($number,$assertEquals) {
    
    $math = app(MathService::class);
    $potencia = $math->ceil($number);
    expect($potencia)->toBe($assertEquals);
    
})->with([
    [3.0123,'4'],
    ['3.0123','4'],
    [-3.0123,'-3'],
    ['-3.0123','-3']
]);

/**
 * Errores
 */
test('Redondeo a parámetro no numérico', function () {
    
    $math = app(MathService::class);
    $math->ceil('x');
})->throws(NumberFormatException::class);

test('Redondeo a parámetro no numérico después del punto decimal ', function () {
    
    $math = app(MathService::class);
    $math->ceil('0.x');
})->throws(NumberFormatException::class);