<?php

declare(strict_types=1);

$input = file_exists('input.txt') ? file_get_contents('input.txt') : null;

$input ??= <<<EXAMPLE
..F7.
.FJ|.
SJ.L7
|F--J
LJ...
EXAMPLE;

$input = array_map('str_split', explode("\n", trim($input)));

$tiles = $input;

$directions = [$north, $east, $south, $west] = [[0, -1], [1, 0], [0, 1], [-1, 0]];
$connections = [
    '|' => [$north, $south], // is a vertical pipe connecting north and south.
    '-' => [$east, $west], // is a horizontal pipe connecting east and west.
    'L' => [$north, $east], // is a 90-degree bend connecting north and east.
    'J' => [$north, $west], // is a 90-degree bend connecting north and west.
    '7' => [$south, $west], // is a 90-degree bend connecting south and west.
    'F' => [$south, $east], // is a 90-degree bend connecting south and east.
];

$start = null;
foreach ($tiles as $y => $row) {
    foreach ($row as $x => $tile) {
        if ($tile === 'S') {
            $start = [$x, $y];
            break 2;
        }
    }
}

// Part 1

// Find connected tile pointing back to start
$pos = null;
foreach ($directions as [$dX, $dY]) {
    [$tileX, $tileY] = [$start[0] + $dX, $start[1] + $dY];
    $tile = $tiles[$tileY][$tileX] ?? null;
    if (!isset($connections[$tile])) {
        continue;
    }
    foreach ($connections[$tile] as [$dXt, $dYt]) {
        if (($tileX + $dXt) === $start[0] && ($tileY + $dYt) === $start[1]) {
            $pos = [$tileX, $tileY];
            break 2;
        }
    }
}

$prev = $start;
$length = 1;
while ($pos !== $start) {
    [$tileX, $tileY] = $pos;
    $tile = $tiles[$tileY][$tileX] ?? null;
//    printf("[%d, %d]: %s\n", $tileX, $tileY, $tile);
    foreach ($connections[$tile] as [$dXt, $dYt]) {
        $next = [$nextX, $nextY] = [$tileX + $dXt, $tileY + $dYt];
        if ($nextX !== $prev[0] || $nextY !== $prev[1]) {
            $prev = $pos;
            $pos = $next;
            break;
        }
    }
    $length++;
}

echo 'Part 1: ', ($length / 2), \PHP_EOL;
