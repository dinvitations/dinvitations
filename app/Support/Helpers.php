<?php

if (!function_exists('percent')) {
    function percent(float|int $part, float|int $total, int $precision = 0): string
    {
        return $total > 0
            ? round(($part / $total) * 100, $precision) . '%'
            : '0%';
    }
}
