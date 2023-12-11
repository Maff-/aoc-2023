<?php

declare(strict_types=1);

$input = file_exists('input.txt') ? file_get_contents('input.txt') : null;

$input ??= <<<EXAMPLE
...#......
.......#..
#.........
..........
......#...
.#........
.........#
..........
.......#..
#...#.....
EXAMPLE;

$input = array_map('str_split', explode("\n", trim($input)));

// Part 1

$map = $input;
$height = count($map);
$originalWidth = count($map[0]);
$width = $originalWidth;
$emptyCols = [];
for ($x = 0, $nX = 0; $x < $originalWidth; $x++, $nX++) {
    $emptyCol = true;
    foreach ($map as $y => $row) {
        if ($row[$x] !== '.') {
            $emptyCol = false;
        }
    }
    $emptyCols[$nX] = $emptyCol;
    if ($emptyCol) {
        $nX++;
        $width++;
    }
}

$y = 0;
$galaxies = [];
foreach ($map as $row) {
    $emptyRow = true;
    $x = 0;
    foreach ($row as $col) {
        if ($col === '#') {
            $emptyRow = false;
            $galaxies[] = [$x, $y];
        }
        $x++;
        if ($emptyCols[$x] ?? false) {
            $x++;
        }
    }
    $y++;
    if ($emptyRow) {
        $y++;
        $height++;
    }
}

$sum = 0;
$n = 0;
for ($i = 0, $iMax = count($galaxies); $i < $iMax; $i++) {
    for ($j = $i + 1; $j < $iMax; $j++) {
        $a = $galaxies[$i];
        $b = $galaxies[$j];
        $dist = abs($b[0] - $a[0]) + abs($b[1] - $a[1]);
        $n++;
        $sum += $dist;
    }
}

echo 'Part 1: ', $sum, \PHP_EOL;

// Part 2 - Copy & Paste & Tweak

$foo = 1_000_000 - 1;

$map = $input;
$height = count($map);
$originalWidth = count($map[0]);
$width = $originalWidth;
$emptyCols = [];
for ($x = 0, $nX = 0; $x < $originalWidth; $x++, $nX++) {
    $emptyCol = true;
    foreach ($map as $y => $row) {
        if ($row[$x] !== '.') {
            $emptyCol = false;
        }
    }
    $emptyCols[$nX] = $emptyCol;
    if ($emptyCol) {
        $nX += $foo;
        $width += $foo;
    }
}

$y = 0;
$galaxies = [];
foreach ($map as $row) {
    $emptyRow = true;
    $x = 0;
    foreach ($row as $col) {
        if ($col === '#') {
            $emptyRow = false;
            $galaxies[] = [$x, $y];
        }
        $x++;
        if ($emptyCols[$x] ?? false) {
            $x += $foo;
        }
    }
    $y++;
    if ($emptyRow) {
        $y += $foo;
        $height += $foo;
    }
}

$sum = 0;
$n = 0;
for ($i = 0, $iMax = count($galaxies); $i < $iMax; $i++) {
    for ($j = $i + 1; $j < $iMax; $j++) {
        $a = $galaxies[$i];
        $b = $galaxies[$j];
        $dist = abs($b[0] - $a[0]) + abs($b[1] - $a[1]);
        $n++;
        $sum += $dist;
    }
}

echo 'Part 2: ', $sum, \PHP_EOL;
