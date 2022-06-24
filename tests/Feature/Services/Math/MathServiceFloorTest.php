<?php
use App\Services\Math\MathService;
use Brick\Math\Exception\NumberFormatException;
use Brick\Math\Exception\DivisionByZeroException;



test('Redondeo a valor mas bajo', function ($numero,$assertEquals) {
    
    $math = app(MathService::class);
    $potencia = $math->floor($numero);
    expect($potencia)->toBe($assertEquals);
})
->with([
    [ 3.0001 , '3'],
    ['3.0001', '3'],
    [ 3.9990 , '3'],
    ['3.9990', '3'],
    ['-3.9990', '-4'],
    ['-3.0001', '-4']
]);



/**
 * Errores
 */
test('Redondeo a parámetro no numérico', function ($number) {
    
    $math = app(MathService::class);
    $math->floor($number);
})->with([
    'x',
    'A1',
    '0.00x',
    'x.00',
    '$'
])->throws(NumberFormatException::class);

