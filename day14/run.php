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
