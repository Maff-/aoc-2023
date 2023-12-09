<?php

declare(strict_types=1);

$input = file_exists('input.txt') ? file_get_contents('input.txt') : null;

$input ??= <<<EXAMPLE
0 3 6 9 12 15
1 3 6 10 15 21
10 13 16 21 30 45
EXAMPLE;

$input = array_map(static fn($line) => array_map('intval', explode(' ', $line)), explode("\n", trim($input)));

// Part 1

function getNext(array $sequence): int
{
    $same = true;
    $prev = null;
    $foo = $sequence;
    $bar = [];
    for ($i = 0, $iMax = count($foo) - 2; $i <= $iMax; $i++) {
        $diff = $foo[$i + 1] - $foo[$i];
        if ($prev !== null && $diff !== $prev) {
            $same = false;
        }
        $prev = $diff;
        $bar[] = $diff;
    }
    if ($same) {
        return $sequence[array_key_last($sequence)] + $diff;
    }

    return $sequence[array_key_last($sequence)] + getNext($bar);
}

$sum = 0;
foreach ($input as $sequence) {
    $sum += getNext($sequence);
}

echo 'Part 1: ', $sum, \PHP_EOL;
