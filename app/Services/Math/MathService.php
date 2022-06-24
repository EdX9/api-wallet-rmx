<?php

namespace App\Services\Math;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Config\Repository as ConfigRepository;

/**
 * Clase para realizar operaciones con Decimales de Gran tamaño.
 * 
 * Dependencias:
 * @see https://github.com/brick/math
 */

final class MathService
{
    private int $scale;

    /**
     * Leer la configuración de escala de la wallet.
     */
    public function __construct(ConfigRepository $config)
    {
        $this->scale = (int) $config->get('walletRmx.math.scale', 64);
    }

    /**
     * Regresa la Suma de dos cantidades.
     */
    public function add(float|int|string $first, float|int|string $second, ?int $scale = null): string
    {
        $scale = $scale ?? $this->scale;
        $first = $this->toStringParam($first,$scale);
        $second = $this->toStringParam($second,$scale);
        return (string) BigDecimal::of($first)
            ->plus(BigDecimal::of($second))
            ->toScale($scale, RoundingMode::DOWN);
    }

    /**
     * Regresa la Resta de dos Cantidades.
     */
    public function sub(float|int|string $first, float|int|string $second, ?int $scale = null): string
    {
        $scale = $scale ?? $this->scale;
        $first = $this->toStringParam($first,$scale);
        $second = $this->toStringParam($second,$scale);
        return (string) BigDecimal::of($first)
            ->minus(BigDecimal::of($second))
            ->toScale($scale, RoundingMode::DOWN);
    }
    /**
     * Regresa la Division de dos Cantidades.
     */
    public function div(float|int|string $first, float|int|string $second, ?int $scale = null): string
    {
        $scale = $scale ?? $this->scale;
        $first = $this->toStringParam($first,$scale);
        $second = $this->toStringParam($second,$scale);
        return (string) BigDecimal::of($first)
            ->dividedBy(BigDecimal::of($second), $scale, RoundingMode::DOWN);
    }
    /**
     * Regresa la Multiplicación de dos cantidades.
     */
    public function mul(float|int|string $first, float|int|string $second, ?int $scale = null): string
    {
        $scale = $scale ?? $this->scale;
        $first = $this->toStringParam($first,$scale);
        $second = $this->toStringParam($second,$scale);
        return (string) BigDecimal::of($first)
            ->multipliedBy(BigDecimal::of($second))
            ->toScale($scale, RoundingMode::DOWN);
    }

    /**
     * Regresa la Potencia de una cantidad.
     * @param int $powVal solo acepta valores de range 0 to 1,000,000.
     */
    public function pow(float|int|string $first, int $powVal, ?int $scale = null): string
    {
        $first = $this->toStringParam($first,$scale);
        $powVal = $this->toStringParam($powVal,0);
        return (string) BigDecimal::of($first)
            ->power((int) $powVal)
            ->toScale($scale ?? $this->scale, RoundingMode::DOWN);
    }

    /**
     * Regresa 10 ala potencia establecida.
     * Para hacer la conversion de la wallet dependiendo de los decimales solicitados
     */
    public function powTen(float|int|string $number): string
    {
        $number = $this->toStringParam($number);
        return $this->pow(10, $number);
    }

    /**
     * Regresa el Redondeo al siguiente entero más alto.
     */
    public function ceil(float|int|string $number): string
    {
        $number = $this->toStringParam($number);
        return (string) BigDecimal::of($number)
            ->dividedBy(BigDecimal::one(), 0, RoundingMode::CEILING);
    }

    /**
     * Regresa el Redondeado al anterior entero más bajo del numero.
     */
    public function floor(float|int|string $number): string
    {
        $number = $this->toStringParam($number);
        return (string) BigDecimal::of($number)
            ->dividedBy(BigDecimal::one(), 0, RoundingMode::FLOOR);
    }

    /**
     * Regresa el Redondea hacia arriba a precision lugares decimales alejándose de cero.
     */
    public function round(float|int|string $number, int $precision = 0): string
    {
        $number = $this->toStringParam($number);
        return (string) BigDecimal::of($number)
            ->dividedBy(BigDecimal::one(), $precision, RoundingMode::HALF_UP);
    }

    /**
     * Regresa el valor Absoluto
     */
    public function abs(float|int|string $number): string
    {
        $number = $this->toStringParam($number);
        return (string) BigDecimal::of($number)
        ->abs();
    }

    /**
     * Regresa el valor Negativo
     */
    public function negative(float|int|string $number): string
    {
        $number = $this->toStringParam($number);
        return (string) BigDecimal::of($number)
        ->negated();
    }

    /**
     * Regresa comprar cantidades
     */
    public function compare(float|int|string $first, float|int|string $second): int
    {
        return BigDecimal::of($first)
        ->compareTo(BigDecimal::of($second));
    }

    /**
     * Revisa si el numero es negativo
     */
    public function isNegative(float|int|string $first):bool
    {
        return BigDecimal::of($first)
        ->isNegative();
    }

    private function toStringParam(string|float|int $number,?int $scale = null)
    {
        return is_string($number)?$number:number_format($number,$scale ?? $this->scale,'.','');
    }
}
