<?php

declare(strict_types=1);

$input = file_exists('input.txt') ? file_get_contents('input.txt') : null;

$input ??= <<<'EXAMPLE'
#.#####################
#.......#########...###
#######.#########.#.###
###.....#.>.>.###.#.###
###v#####.#v#.###.#.###
###.>...#.#.#.....#...#
###v###.#.#.#########.#
###...#.#.#.......#...#
#####.#.#.#######.#.###
#.....#.#.#.......#...#
#.#####.#.#.#########v#
#.#...#...#...###...>.#
#.#.#v#######v###.###v#
#...#.>.#...>.>.#.###.#
#####v#.#.###v#.#.###.#
#.....#...#...#.#.#...#
#.#########.###.#.#.###
#...###...#...#...#.###
###.###.#.###v#####v###
#...#...#.#.>.>.#.>.###
#.###.###.#.###.#.#v###
#.....###...###...#...#
#####################.#
EXAMPLE;

/**
 * @return string[][]
 */
function parseInput(string $input): array
{
    $result = explode("\n", trim($input));
    $result = array_map('str_split', $result);
    return $result;
}

/**
 * @return array{0: int, 1: int}
 */
function getMapSize(array $map): array
{
    $width = count($map[0]);
    $height = count($map);

    return [$width, $height];
}

/**
 * @return array{0: array{0: int, 1: int}, 1: array{0: int, 1: int}}
 */
function getStartAndEnd(array $map): array
{
    $start = [array_search('.', $map[0], true), 0];
    $yEnd = array_key_last($map);
    $end = [array_search('.', $map[$yEnd], true), $yEnd];

    return [$start, $end];
}

$directions = ['^' => [0, -1], '>' => [1, 0], 'v' => [0, 1], '<' => [-1, 0]]; // N,E,S,W

$map = parseInput($input);

// Part 1
function findPath(array $map, array $path, array $current, array $target, ?array &$result = []): void
{
    global $directions;

    $result ??= [];

    while ($current !== $target) {
        $last = end($path);
        $path[] = $current;
        [$x, $y] = $current;

        $neighbours = [];
        foreach ($directions as $slope => [$dX, $dY]) {
            [$nX, $nY] = $neighbour = [$x + $dX, $y + $dY];
            $tile = $map[$nY][$nX] ?? null;
            if ($nX >= 0 && $nY >= 0 && $neighbour !== $last && ($tile === '.' || $tile === $slope)) {
                $neighbours[] = $neighbour;
            }
        }
        if (count($neighbours) === 1) {
            $current = $neighbours[0];
            continue;
        }
        if (count($neighbours) > 1) {
            foreach ($neighbours as $neighbour) {
                findPath($map, $path, $neighbour, $target, $result);
            }
        }

        // dead end
        return;
    }

    $result[] = $path;
}

function findPaths(array $map): array
{
    [$start, $end] = getStartAndEnd($map);

    findPath($map, [], $start, $end, $paths);

    return $paths;
}

function getLongestPathLength(array $map): int
{
    return max(array_map('count', findPaths($map)));
}

$result = getLongestPathLength($map);

echo 'Part 1: ', $result, \PHP_EOL;
