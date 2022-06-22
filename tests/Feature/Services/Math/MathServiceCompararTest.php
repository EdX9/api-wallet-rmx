<?php

use App\Services\Math\MathService;
use Brick\Math\Exception\NumberFormatException;



test('Comparar Números ', function ($number1,$number2,$assertEquals) {
    /**
     * -1 Menor que 
     * 0 Iguales
     * 1 Mayores que 
     */
    $math = app(MathService::class);
    $compracion = $math->compare($number1,$number2);
    expect($compracion)->toBe($assertEquals);

})->with([

    [ 10.001 , 10.1 ,-1],
    ['10.001','10.1',-1],

    [ -10.1 , 10 , -1],
    ['-10.1','10', -1],

    [ 10,  10, 0],
    ['10','10',0],
    [ 10.123,  10.123,0],
    ['10.123','10.123',0],
    [ -10,  -10, 0],
    ['-10','-10',0],
    [ -10.123 , -10.123 ,0],
    ['-10.123','-10.123',0],

    [ 20 , 10 ,1],
    ['20','10',1],
    [ 10.1 , 10.001,1],
    ['10.1','10.001',1],

    

]);


/**
 * Errores
 */
test('Comparar Números paramento no numérico', function ($number1,$number2) {
    
    $math = app(MathService::class);
    $math->compare($number1,$number2);
})
->with([
    ['x',10],
    ['x','10'],
    [10,'x'],
    ['10','x'],
    
])
->throws(NumberFormatException::class);

test('Comparar Números paramento no numérico después del punto decimal', function () {
    
    $math = app(MathService::class);
    $math->abs('0.x');
})
->with([
    ['0.x',10],
    ['0.x','10'],
    [10,'0.x'],
    ['10','0.x',],
])->throws(NumberFormatException::class);

