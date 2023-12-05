<?php

declare(strict_types=1);

$input = file_exists('input.txt') ? file_get_contents('input.txt') : null;

$example = <<<EXAMPLE
1abc2
pqr3stu8vwx
a1b2c3d4e5f
treb7uchet
EXAMPLE;

$input ??= $example;

$input = explode("\n", trim($input));

// Part 1

$numbers = array_map(static function (string $chars): int {
    $first = null;
    $last = null;
    for ($i = 0, $len = strlen($chars); $i < $len; $i++) {
        $char = $chars[$i];
        if (ctype_digit($chars[$i])) {
            $first ??= $char;
            if ($first !== null) {
                $last = $char;
            }
        }
    }
    return (int)($first . $last);
}, $input);

$sum = array_sum($numbers);
echo 'Part 1: ', $sum, PHP_EOL;

// Part 2

if (count($input) === 4) {
    $example = <<<EXAMPLE
        two1nine
        eightwothree
        abcone2threexyz
        xtwone3four
        4nineeightseven2
        zoneight234
        7pqrstsixteen
        EXAMPLE;
    $input = explode("\n", trim($example));
}

$numbers = array_map(static function (string $chars): int {
    // Solution based on some others at https://www.reddit.com/r/adventofcode/comments/1883ibu/2023_day_1_solutions/
    // 'Properly' translates overlapping number words, i.e. turns 'eightwo' into ..8..2.. (instead of only 8 or 2)
    $replace = [
        'one' => 'o1e',
        'two' => 't2o',
        'three' => 't3ree',
        'four' => 'f4ur',
        'five' => 'f5ve',
        'six' => 's6x',
        'seven' => 's7ven',
        'eight' => 'e8ght',
        'nine' => 'n9ne',
    ];
    $chars = str_replace(array_keys($replace), array_values($replace), $chars);

    $first = null;
    $last = null;
    for ($i = 0, $len = strlen($chars); $i < $len; $i++) {
        $char = $chars[$i];
        if (ctype_digit($chars[$i])) {
            $first ??= $char;
            if ($first !== null) {
                $last = $char;
            }
        }
    }
    return (int)($first . $last);
}, $input);

$sum = array_sum($numbers);
echo 'Part 2: ', $sum, PHP_EOL;
