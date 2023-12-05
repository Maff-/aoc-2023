<?php

declare(strict_types=1);

$input = file_exists('input.txt') ? file_get_contents('input.txt') : null;

$input ??= <<<EXMAPLE
seeds: 79 14 55 13

seed-to-soil map:
50 98 2
52 50 48

soil-to-fertilizer map:
0 15 37
37 52 2
39 0 15

fertilizer-to-water map:
49 53 8
0 11 42
42 0 7
57 7 4

water-to-light map:
88 18 7
18 25 70

light-to-temperature map:
45 77 23
81 45 19
68 64 13

temperature-to-humidity map:
0 69 1
1 0 69

humidity-to-location map:
60 56 37
56 93 4
EXMAPLE;

$input = explode("\n\n", trim($input));

$line1 = array_shift($input);
$seeds = array_map('intval', explode(' ', explode(': ', $line1, 2)[1]));

$input = array_map(static function ($data) {
    $lines = explode("\n", $data);
    $name = substr(array_shift($lines), 0, -1);
    $map = array_map(static fn($line) => array_map('intval', explode(' ', $line)), $lines);
    return [$name, $map];
}, $input);
$input = array_column($input, 1, 0);

$locations = [];
foreach ($seeds as $n => $seed) {
    $value = $seed;
    foreach ($input as $map) {
        foreach ($map as [$destStart, $srcStart, $length]) {
            if ($value >= $srcStart && $value <= $srcStart + $length) {
                $offset = $value - $srcStart;
                $value = $destStart + $offset;
                break;
            }
        }
    }
    $locations[$n] = $value;
}

echo 'Part 1: ', min($locations), \PHP_EOL;
