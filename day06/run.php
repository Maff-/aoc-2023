<?php

declare(strict_types=1);

$input = file_exists('input.txt') ? file_get_contents('input.txt') : null;

$input ??= <<<EXMAPLE
Time:      7  15   30
Distance:  9  40  200
EXMAPLE;

$input = array_map(static function ($line) {
    return array_map('intval', preg_split('/\s+/', trim(explode(':', $line)[1])));
}, explode("\n", trim($input)));

// Part 1

$result = 1;
foreach ($input[0] as $n => $time) {
    $record = $input[1][$n];
    $prev = null;
    $beats = 0;
    for ($hold = 1; $hold < $time; $hold++) {
        $timeRemaining = $time - $hold;
        $distance = $timeRemaining * $hold;
        if ($distance > $record) {
            $beats++;
        } elseif ($beats) {
            break;
        }
    }
    $result *= $beats;
}

echo 'Part 1: ', $result, \PHP_EOL;
