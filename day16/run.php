<?php

declare(strict_types=1);

$input = file_exists('input.txt') ? file_get_contents('input.txt') : null;

$input ??= <<<'EXAMPLE'
.|...\....
|.-.\.....
.....|-...
........|.
..........
.........\
..../.\\..
.-.-/..|..
.|....-|.\
..//.|....
EXAMPLE;

$input = explode("\n", trim($input));
$input = array_map('str_split', $input);

$height = count($input);
$width = count($input[0]);

// Part 1

function energize(array $input, array $start): int
{
    global $height, $width;
    $directions = [$north, $east, $south, $west] = [[0, -1], [1, 0], [0, 1], [-1, 0]];

    $energized = [];
    $processed = [];
    $queue = [$start];

    while ($queue) {
        [$pos, $direction] = array_shift($queue);
        [$x, $y] = $pos;
        [$dX, $dY] = $direction;
        if ($x < 0 || $x >= $width || $y < 0 || $y >= $height) {
            // out of bounds
            continue;
        }
        if (isset($processed[$y][$x]) && in_array($direction, $processed[$y][$x], true)) {
            // already done
            continue;
        }
        $processed[$y][$x][] = $direction;
        $cell = $input[$y][$x];
        $energized[$y][$x] = true;
        switch ($cell) {
            case '.':
                $queue[] = [[$x + $dX, $y + $dY], $direction];
                break;
            case '|':
                if ($dX === 0) {
                    // pointy end; continue
                    $queue[] = [[$x + $dX, $y + $dY], $direction];
                } else {
                    // split
                    $queue[] = [[$x, $y - 1], $north];
                    $queue[] = [[$x, $y + 1], $south];
                }
                break;
            case '-':
                if ($dY === 0) {
                    // pointy end; continue
                    $queue[] = [[$x + $dX, $y + $dY], $direction];
                } else {
                    // split
                    $queue[] = [[$x - 1, $y], $west];
                    $queue[] = [[$x + 1, $y], $east];
                }
                break;
            case '/':
            case '\\':
                $directionIndex = array_search($direction, $directions, true);
                $indexMod = ($cell === '/' && $dY === 0) || ($cell === '\\' && $dX === 0) ? -1 : 1;
                $turnIndex = (4 + $directionIndex + $indexMod) % 4;
                $turn = [$dX, $dY] = $directions[$turnIndex];
                $queue[] = [[$x + $dX, $y + $dY], $turn];
                break;
            default:
                throw new \RuntimeException("Unhandled cell type '{$cell}'");
        }
    }

    return array_sum(array_map('array_sum', $energized));
}

$result = energize($input, [[0, 0], [1, 0]]);

echo 'Part 1: ', $result, \PHP_EOL;

// Part 2

$max = 0;
for ($y = 0; $y < $height; $y++) {
    $left = energize($input, [[0, $y], [1, 0]]);
    $right = energize($input, [[$width - 1, $y], [-1, 0]]);
    $max = max($max, $left, $right);
}
for ($x = 1; $x < $width - 1; $x++) {
    $down = energize($input, [[$x, 0], [0, 1]]);
    $up = energize($input, [[$y, $height - 1], [0, -1]]);
    $max = max($max, $down, $up);
}

echo 'Part 2: ', $max, \PHP_EOL;
