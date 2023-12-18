<?php

declare(strict_types=1);

$input = file_exists('input.txt') ? file_get_contents('input.txt') : null;

$input ??= <<<'EXAMPLE'
R 6 (#70c710)
D 5 (#0dc571)
L 2 (#5713f0)
D 2 (#d2c081)
R 2 (#59c680)
D 2 (#411b91)
L 5 (#8ceee2)
U 2 (#caa173)
L 1 (#1b58a2)
U 2 (#caa171)
R 2 (#7807d2)
U 3 (#a77fa3)
L 2 (#015232)
U 2 (#7a21e3)
EXAMPLE;

$input = explode("\n", trim($input));
$input = array_map(static function (string $line): array {
    [, $direction, $distance, $color] = preg_match('/^([UDLR]) (\d+) \(#([a-f0-9]{6})\)/', $line, $match) ? $match : throw new \RuntimeException("Regex did not match line '{$line}'");
    return [$direction, (int)$distance, $color];
}, $input);

// Part 1

$instructions = $input;
$directions = ['U' => [0, -1], 'D' => [0, 1], 'L' => [-1, 0], 'R' => [1, 0]];
$map = [[true]];
$pos = [$x, $y] = [0, 0];
[$minX, $maxX, $minY, $maxY] = [$x, $x, $y, $y];
$count = 0;
foreach ($instructions as [$direction, $distance, $color]) {
    [$dX, $dY] = $directions[$direction];
    for ($i = $distance; $i > 0; $i--) {
        $x += $dX;
        $y += $dY;
        [$minX, $maxX, $minY, $maxY] = [min($minX, $x), max($maxX, $x), min($minY, $y), max($maxY, $y)];
        $map[$y][$x] = true;
        $count++;
    }
}

$fill = [];
$queue = [[1, 1]];
$queued = [];
while ($queue) {
    [$x, $y] = $current = array_shift($queue);
    $index = ($x * 1000) + $y;
    unset($index);
    $fill[$y][$x] = true;
    $count++;
    foreach ($directions as [$dX, $dY]) {
        $neighbour = [$nX, $nY] = [$x + $dX, $y + $dY];
        $nIndex = ($nX * 1000) + $nY;
        if (isset($queued[$nIndex]) || ($map[$nY][$nX] ?? false) || ($fill[$nY][$nX] ?? false)) {
            continue;
        }
        $queue[] = $neighbour;
        $queued[$nIndex] = true;
    }
}

$result = $count;

echo 'Part 1: ', $result, \PHP_EOL;

// Part 2

// Transform 'colors' into instructions
$instructions = array_map(static function (array $args): array {
    [, , $color] = $args;
    $direction = ['R', 'D', 'L', 'U'][(int)$color[-1]];
    $distance = hexdec(substr($color, 0, -1));
    return [$direction, $distance, $color];
}, $input);

$from = [0, 0];
$points = [$from];
$totalDistance = 0;
foreach ($instructions as [$direction, $distance, $color]) {
    $totalDistance += $distance;
    [$x, $y] = $from;
    [$dX, $dY] = $directions[$direction];
    $to = [$x + ($dX * $distance), $y + ($dY * $distance)];
    $points[] = $to;
    $from = $to;
}

function shoelace(array $points): int|float
{
    $area = 0;
    for ($i = 0, $num = count($points); $i < $num; $i++) {
        $current = $points[$i];
        $next = $points[($i + 1) % $num];
        $area += $current[0] * $next[1] - $current[1] * $next[0];
    }
    return $area / 2;
}

$result = shoelace($points) + ($totalDistance / 2) + 1;

echo 'Part 2: ', $result, \PHP_EOL;
