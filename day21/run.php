<?php

declare(strict_types=1);

$input = file_exists('input.txt') ? file_get_contents('input.txt') : null;
$useExample = ($input ?? null) === null;

$input ??= <<<'EXAMPLE'
...........
.....###.#.
.###.##..#.
..#.#...#..
....#.#....
.##..S####.
.##..#...#.
.......##..
.##.#.####.
.##..##.##.
...........
EXAMPLE;

$input = explode("\n", trim($input));
$input = array_map('str_split', $input);

/**
 * @return array{0: int, 1: int}
 */
function getMapSize(array $map): array
{
    $width = count($map[0]);
    $height = count($map);

    return [$width, $height];
}

function getStartCoords(array $map): array
{
    foreach ($map as $y => $row) {
        foreach ($row as $x => $tile) {
            if ($tile === 'S') {
                return [$x, $y];
            }
        }
    }

    throw new \RuntimeException('Could not find Start tile');
}

// Part 1

function countPlots(array $map, int $steps): int
{
    $start = getStartCoords($map);
    [$width, $height] = getMapSize($map);
    $queue = [$start];
    $directions = [$north, $east, $south, $west] = [[0, -1], [1, 0], [0, 1], [-1, 0]];

    for ($s = 0; $s < $steps; $s++) {
        $next = [];
        while ($queue) {
            [$x, $y] = $pos = array_shift($queue);
            foreach ($directions as [$dX, $dY]) {
                [$nX, $nY] = $neighbour = [$x + $dX, $y + $dY];
                if ($nX < 0 || $nY < 0 || $nX >= $width || $nY >= $height) {
                    continue;
                }
                if ($map[$nY][$nX] !== '#') {
                    $index = ($nY * 100) + $nX;
                    $next[$index] = $neighbour;
                }
            }
        }
        $queue = $next;
    }

    return count($next ?? []);
}

$map = $input;
$steps = $useExample ? 6 : 64;
$result = countPlots($map, $steps);

echo 'Part 1: ', $result, \PHP_EOL;
