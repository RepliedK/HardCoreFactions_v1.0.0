<?php

declare(strict_types=1);

namespace hcf\utils\logic\time;

use InvalidArgumentException;

/**
 * Class Timer
 * @package hcf\utils
 */
final class Timer
{
    
    /**
     * @param string $duration
     * @throws InvalidArgumentException
     * @return int
     */
    public static function time(string $duration): int
    {
        if (preg_match('/^(\d+)(d|h|m|s)$/', $duration, $matches)) {
            $valor = (int)$matches[1];
            $unidad = strtolower($matches[2]);
            switch ($unidad) {
                case 'd':
                    return $valor * 24 * 3600;
                case 'h':
                    return $valor * 3600; // 1 hora equivale a 3600 segundos
                case 'm':
                    return $valor * 60;   // 1 minuto equivale a 60 segundos
                case 's':
                    return $valor;        // Los segundos ya están en segundos
                default:
                    return 0;             // Unidad no válida, devolvemos 0
            }
        } else {
            // Si el formato del string no es válido, devolvemos un valor predeterminado (0)
            return 0;
        }
    }
    
    /**
     * @param int $time
     * @return string
     */
    public static function date(int $time): string
    {
        $weeks = $time / 604800 % 52;
        $hours = $time / 3600 % 24;
        $minutes = $time / 60 % 60;
        $seconds = $time % 60;
        
        return $weeks . ' week(s), ' . $hours . ' hour(s), ' . $minutes . ' minute(s) and ' . $seconds . ' second(s)';
    }
    
    /**
     * @param int $time
     * @return string
     */
    public static function format(int $time): string
    {
        if ($time >= 3600)
            return gmdate('H:i:s', $time);
        elseif ($time < 60)
            return $time . 's';
        return gmdate('i:s', $time);
    }
    
    public static function convert(int $time): string
    {
        if ($time < 60)
            return $time . 's';
        elseif ($time < 3600) {
            $minutes = intval($time / 60) % 60;
            return $minutes . 'm';
        } elseif ($time < 86400) {
            $hours = (int)($time / 3600) % 24;
            return (int)$hours . 'h';
        } else {
            $days = floor($time / 86400);
            return $days . 'd';
        }
    }
}