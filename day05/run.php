<?php

declare(strict_types=1);

$input = file_exists('input.txt') ? file_get_contents('input.txt') : null;

$input ??= <<<EXAMPLE
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
EXAMPLE;

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

// Part 1

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

// Part 2

$location = null;
$seedRanges = array_chunk($seeds, 2);
$seedRanges = array_map(static fn($a) => [$a[0], $a[0] + $a[1] - 1], $seedRanges);

foreach ($seedRanges as [$start, $end]) {
    $next = [[$start, $end]];
    foreach ($input as $map) {
        $queue = $next;
        $next = [];
        while ($queue) {
            [$start, $end] = array_shift($queue);
            $found = false;
            foreach ($map as [$destStart, $srcStart, $length]) {
                $srcEnd = $srcStart + $length - 1;
                $offset = $destStart - $srcStart;

                if ($end < $srcStart || $start > $srcEnd) {
                    // completely before/after
                    continue;
                }
                if ($start >= $srcStart && $end <= $srcEnd) {
                    // completely included
                    $next[] = [$start + $offset, $end + $offset];
                    $found = true;
                    break;
                }
                if ($start < $srcStart && $end >= $srcStart) {
                    // split range before start
                    $queue[] = [$start, $srcStart - 1];
                    $start = $srcStart;
                }
                if ($end > $srcEnd && $start <= $srcEnd) {
                    // split range after end
                    $queue[] = [$srcEnd + 1, $end];
                    $end = $srcEnd;
                }
                $start += $offset;
                $end += $offset;
                $next[] = [$start, $end];
                $found = true;
                break;
            }
            if (!$found) {
                $next[] = [$start, $end];
            }
            continue; // no-op for breakpoint :)
        }
        continue; // no-op for breakpoint :)
    }

    foreach ($next as [$locationStart, $_]) {
        if ($location === null || $location > $locationStart) {
            $location = $locationStart;
        }
    }
}

echo 'Part 2: ', $location, \PHP_EOL;
