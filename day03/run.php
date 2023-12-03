<?php

declare(strict_types=1);

$input = file_exists('input.txt') ? file_get_contents('input.txt') : null;

$input ??= <<<EXMAPLE
467..114..
...*......
..35..633.
......#...
617*......
.....+.58.
..592.....
......755.
...$.*....
.664.598..
EXMAPLE;

$input = array_map('str_split', explode("\n", trim($input)));

$height = count($input);
$width = count($input[0]);

$numbers = [];
$symbolMap = [];

for ($y = 0; $y < $height; $y++) {
    $number = null;
    $prev = null;
    for ($x = 0; $x < $width; $x++) {
        $char = $input[$y][$x];
        if (ctype_digit($char)) {
            $number ??= ['', [$x, $y], 0];
            $number[0] .= $char;
            $number[2]++;
        } else {
            if ($number) {
                $number[0] = (int)$number[0];
                $numbers[] = $number;
                $number = null;
            }
            if ($char !== '.') {
                $symbolMap[$y][$x] = true;
            }
        }
    }
    if ($number) {
        $number[0] = (int)$number[0];
        $numbers[] = $number;
    }
}

$sum = 0;
foreach ($numbers as [$value, $pos, $length]) {
    [$xMin, $xMax] = [max($pos[0] - 1, 0), min($pos[0] + $length, $width)];
    [$yMin, $yMax] = [max($pos[1] - 1, 0), min($pos[1] + 1, $height - 1)];
    for ($y = $yMin; $y <= $yMax; $y++) {
        // TODO: improve, so we dont check the positions of the number itself.
        for ($x = $xMin; $x <= $xMax; $x++) {
            $symbolAdjacent = $symbolMap[$y][$x] ?? false;
            if ($symbolAdjacent) {
                $sum += $value;
                continue 3;
            }
        }
    }
}

echo 'Part 1: ', $sum, \PHP_EOL;

// Part 2

$numberMap = [];
$stars = [];

function addNumberToMap(array $number, array &$map) {
    [$value, $pos, $length] = $number;
    for ($x = $pos[0]; $x < $pos[0] + $length; $x++) {
        $map[$pos[1]][$x] = $number;
    }
}

for ($y = 0; $y < $height; $y++) {
    $number = null;
    $prev = null;
    for ($x = 0; $x < $width; $x++) {
        $char = $input[$y][$x];
        if (ctype_digit($char)) {
            $number ??= ['', [$x, $y], 0];
            $number[0] .= $char;
            $number[2]++;
        } else {
            if ($number) {
                $number[0] = (int)$number[0];
                addNumberToMap($number, $numberMap);
                $number = null;
            }
            if ($char === '*') {
                $stars[] = [$x, $y];
            }
        }
    }
    if ($number) {
        $number[0] = (int)$number[0];
        addNumberToMap($number, $numberMap);
    }
}

$gearSum = 0;
$around = [
    [-1, -1], [ 0, -1], [+1, -1],
    [-1,  0],           [+1,  0],
    [-1, +1], [ 0, +1], [+1, +1],
];
foreach ($stars as $n => [$xStar, $yStar]) {
    $gearNumbers = [];
    foreach ($around as [$xD, $yD]) {
        $number = $numberMap[$yStar + $yD][$xStar + $xD] ?? null;
        if ($number && !in_array($number, $gearNumbers, true)) {
            $gearNumbers[] = $number;
        }
    }
    if (count($gearNumbers) === 2) {
        $ratio = $gearNumbers[0][0] * $gearNumbers[1][0];
        $gearSum += $ratio;
    }
}

echo 'Part 2: ', $gearSum, \PHP_EOL;
