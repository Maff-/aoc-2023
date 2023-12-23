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

// Part 2

function coord2index(array $coord): int
{
    [$x, $y] = $coord;
    return ($x * 1000) + $y;
}

function findVertices(array $map): array
{
    global $directions;
    $maxY = array_key_last($map);
    $vertices = [];

    foreach ($map as $y => $row) {
        foreach ($row as $x => $tile) {
            if ($tile === '#') {
                continue;
            }
            $neighbours = [];
            foreach ($directions as [$dX, $dY]) {
                [$nX, $nY] = $neighbour = [$x + $dX, $y + $dY];
                $nTile = $map[$nY][$nX] ?? null;
                if ($nTile !== null && $nTile !== '#') {
                    $neighbours[] = $neighbour;
                }
            }
            if ($y === 0 || $y === $maxY || count($neighbours) > 2) {
                $index = coord2index([$x, $y]);
                $vertices[$index] = [[$x, $y], $neighbours];
            }
        }
    }

    return $vertices;
}

function walkPath(array $map, array $vertices, array $start, array &$path): array
{
    global $directions;

    $current = $start;
    while ($current !== null) {
        [$x, $y] = $current;
        $index = coord2index([$x, $y]);
        $path[$index] = $current;

        if (isset($vertices[$index])) {
            return $path;
        }

        $neighbours = [];
        foreach ($directions as [$dX, $dY]) {
            [$nX, $nY] = $neighbour = [$x + $dX, $y + $dY];
            $tile = $map[$nY][$nX] ?? null;
            $nIndex = coord2index($neighbour);
            if ($nX >= 0 && $nY >= 0 && !isset($path[$nIndex]) && $tile !== null && $tile !== '#') {
                $neighbours[] = $neighbour;
            }
        }
        if (count($neighbours) === 1) {
            $current = $neighbours[0];
            continue;
        }
        if (count($neighbours) > 1) {
            throw new \RuntimeException("More than one neighbour at [{$x},{$y}]!");
        }
        // dead end
        return [];
    }

    throw new \RuntimeException('whut?');
}



function findVertexPaths(array $vertices, array $path, int $current, int $target, ?array &$result = []): void
{
    $result ??= [];

    $path[$current] = $current;

    if ($current === $target) {
        $result[] = $path;
        return;
    }
    $adjacent = $vertices[$current][2];
    foreach($adjacent as $v => $_) {
        if (!isset($path[$v])) {
            findVertexPaths($vertices, $path, $v, $target, $result);
        }
    }
}

function getLongestPathLength2(array $map): int
{
    $vertices = findVertices($map);
    $edges = [];
    foreach ($vertices as $v => [$coords, $pathStarts]) {
        $vertices[$v][2] = [];
        foreach ($pathStarts as $pathStart) {
            $path = [coord2index($coords) => $coords];
            $path = walkPath($map, $vertices, $pathStart, $path);
            $u = array_key_last($path);
            $uCoords = $path[$u];
            $length = count($path) - 1;
            $vertices[$v][2][$u] = $length;
            $edge = $v < $u ? (($v * 1_000_000) + $u) : (($u * 1_000_000) + $v);
            $edges[$edge] = [
                [$v => $coords, $u => $uCoords],
                $length,
                $path,
            ];
            echo "[{$coords[0]},{$coords[1]}] -> [{$uCoords[0]},{$uCoords[1]}] = {$length}\n";
        }
    }

    $maxLength = 0;
    [$start, $end] = getStartAndEnd($map);
    findVertexPaths($vertices, [], coord2index($start), coord2index($end), $paths);
    foreach ($paths as $path) {
        $prev = null;
        $length = 0;
        foreach ($path as $node) {
            if ($prev !== null) {
                $v = $prev;
                $u = $node;
                $edge = $v < $u ? (($v * 1_000_000) + $u) : (($u * 1_000_000) + $v);
                $length += $edges[$edge][1];
            }
            $prev = $node;
        }
        if ($maxLength < $length) {
            $maxLength = $length;
        }
    }

    return $maxLength;
}

$result = getLongestPathLength2($map);

echo 'Part 2: ', $result, \PHP_EOL;
