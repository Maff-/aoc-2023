<?php

declare(strict_types=1);

$input = file_exists('input.txt') ? file_get_contents('input.txt') : null;

$input ??= <<<EXAMPLE
O....#....
O.OO#....#
.....##...
OO.#O....O
.O.....O#.
O.#..O.#.#
..O..#O..O
.......O..
#....###..
#OO..#....
EXAMPLE;

$input = explode("\n", trim($input));

$height = count($input);
$width = strlen($input[0]);

// Part 1

$result = null;
for ($x = 0; $x < $width; $x++) {
    $blockY = -1;
    for ($y = 0; $y < $height; $y++) {
        $cell = $input[$y][$x];
        switch ($cell) {
            case 'O':
                $blockY++;
                $load = $height - $blockY;
                $result += $load;
                break;
            case '#':
                $blockY = $y;
                break;
        }
    }
}

echo 'Part 1: ', $result, \PHP_EOL;

// Part 2

function getNorthLoad(array $platform, int $width, int $height): array
{
    $result = [];

    for ($x = 0; $x < $width; $x++) {
        $result[$x] = 0;
        for ($y = 0; $y < $height; $y++) {
            $cell = $platform[$y][$x];
            if ($cell === 'O') {
                $load = $height - $y;
                $result[$x] += $load;
            }
        }
    }

    return $result;
}

function cycle(array &$platform, int $width, int $height): void
{
    // Tilt north
    for ($x = 0; $x < $width; $x++) {
        $blockY = -1;
        for ($y = 0; $y < $height; $y++) {
            $cell = $platform[$y][$x];
            switch ($cell) {
                case 'O':
                    $blockY++;
                    if ($blockY !== $y) {
                        $platform[$blockY][$x] = 'O';
                        $platform[$y][$x] = '.';
                    }
                    break;
                case '#':
                    $blockY = $y;
                    break;
            }
        }
    }

    // Titt west
    for ($y = 0; $y < $height; $y++) {
        $blockX = -1;
        for ($x = 0; $x < $width; $x++) {
            $cell = $platform[$y][$x];
            switch ($cell) {
                case 'O':
                    $blockX++;
                    if ($blockX !== $x) {
                        $platform[$y][$blockX] = 'O';
                        $platform[$y][$x] = '.';
                    }
                    break;
                case '#':
                    $blockX = $x;
                    break;
            }
        }
    }

    // Tilt south
    for ($x = 0; $x < $width; $x++) {
        $blockY = $height;
        for ($y = $height - 1; $y >= 0; $y--) {
            $cell = $platform[$y][$x];
            switch ($cell) {
                case 'O':
                    $blockY--;
                    if ($blockY !== $y) {
                        $platform[$blockY][$x] = 'O';
                        $platform[$y][$x] = '.';
                    }
                    break;
                case '#':
                    $blockY = $y;
                    break;
            }
        }
    }

    // Tilt east
    for ($y = 0; $y < $height; $y++) {
        $blockX = $width;
        for ($x = $width - 1; $x >= 0; $x--) {
            $cell = $platform[$y][$x];
            switch ($cell) {
                case 'O':
                    $blockX--;
                    if ($blockX !== $x) {
                        $platform[$y][$blockX] = 'O';
                        $platform[$y][$x] = '.';
                    }
                    break;
                case '#':
                    $blockX = $x;
                    break;
            }
        }
    }
}

$cycles = 1000000000;
$result = null;
$platform = $input;
$results = [];
$loads = [];

for ($c = 1; $c <= $cycles; $c++) {
    cycle($platform, $width, $height);
    $load = getNorthLoad($platform, $width, $height);
    $hash = implode(';', $load);
    if (isset($results[$hash])) {
        $prevCycle = $results[$hash];
        $loopSize = $c - $prevCycle;
        echo "Stopped after {$c} cycles; same result as after cycle {$prevCycle}; loop size = {$loopSize}\n";
        $result = $loads[$prevCycle + (($cycles - $prevCycle) % $loopSize)];
        break;
    }
    $results[$hash] = $c;
    $loads[$c] = array_sum($load);
}

echo 'Part 2: ', $result, \PHP_EOL;
