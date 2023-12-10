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

$path = [$start];
$pathMap = [$start[1] => [$start[0] => $tiles[$start[1]][$start[0]]]];
$prev = $start;
$length = 1;
while ($pos !== $start) {
    $path[] = $pos;
    [$tileX, $tileY] = $pos;
    $tile = $tiles[$tileY][$tileX] ?? null;
    $pathMap[$tileY][$tileX] = (string)$tile;
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

// Part 2 (the 'holy-fckn-sht-what-a-mess' solution)

// Fill in start tile
[$pos2x, $pos2y] = $path[1];
[$posLx, $posLy] = end($path);
[$startX, $startY] = $start;
$a = [$pos2x - $startX, $pos2y - $startY];
$b = [$posLx - $startX, $posLy - $startY];
$startTile = null;
foreach ($connections as $tile => [$d1, $d2]) {
    if (($d1 === $a && $d2 === $b) || ($d1 === $b && $d2 === $a)) {
        $startTile = (string)$tile;
        break;
    }
}
//$tiles[$startY][$startX] = $startTile;
$pathMap[$startY][$startX] = $startTile;

[$height, $width] = [count($tiles), count($tiles[0])];
$prev = end($path);
$blah = [];
$queue = [[], []];
foreach ($path as $n => $pos) {
    [$posX, $posY] = $pos;
    [$prevX, $prevY] = $prev;

    $tile = $pathMap[$posY][$posX];
    $prevTile = $pathMap[$prevY][$prevX];

    $direction = [$dX, $dY] = [$posX - $prevX, $posY - $prevY];

    [$turn, $left, $right] = match(true) {
        $tile === '|' && $direction === $north => ['S', [ $west ], [ $east ]], // strait up
        $tile === '|' && $direction === $south => ['S', [ $east ], [ $west ]], // strait down
        $tile === '-' && $direction === $west => ['S', [ $south ], [ $north ]], // straight left
        $tile === '-' && $direction === $east => ['S', [ $north ], [ $south ]], // straight right
        $tile === 'L' && $direction === $south => ['L', [], [ $west, $south ]], // left turn
        $tile === 'L' && $direction === $west => ['R', [ $south, $west ], []], // right turn
        $tile === 'J' && $direction === $east => ['L', [], [ $south, $east ]], // left turn
        $tile === 'J' && $direction === $south => ['R', [ $east, $south ], []], // right turn

        $tile === '7' && $direction === $east => ['R', [ $north, $east ], []], // right turn

        $tile === '7' && $direction === $north => ['L', [], [ $east, $north ]], // left turn
        $tile === 'F' && $direction === $north => ['R', [ $west, $north ], []], // right turn
        $tile === 'F' && $direction === $west => ['L', [], [ $north, $west ]], // left turn
        default => throw new \UnhandledMatchError(sprintf('Unhandled `$tile === \'%s\' && $direction === [%s] => [],` case', $tile, implode(', ', $direction))),
    };
    foreach ([$left, $right] as $lr => $lrItems) {
        foreach ($lrItems as [$dX, $dY]) {
            $foo = [$fooX, $fooY] = [$posX + $dX, $posY + $dY];
            if ($fooX >= 0 && $fooY >= 0 && $fooX < $width && $fooY < $height && !isset($pathMap[$fooY][$fooX]) && !in_array($foo, $queue[$lr], true)) {
                $queue[$lr][] = $foo;
            }
        }
    }

    $blah[$n] = $direction;

//    printf("%3d: [%d, %d]: %s ([%d, %d] / [%d, %d])\n", $n, $posX, $posY, $pathMap[$posY][$posX], $dX, $dY);
    $prev = $pos;
}

$count = [0, 0];
$checked = [[], []];
$leftRight = [[], []];
foreach ($queue as $lr => $queueItems) {
    while (count($queueItems)) {
        $pos = [$posX, $posY] = array_shift($queueItems);
        $count[$lr]++;
        $checked[$lr][$posY][$posX] = true;
        $leftRight[$lr][$posY][$posX] = true;
        $addedCount = 0;
        foreach ($directions as [$dX, $dY]) {
            $foo = [$fooX, $fooY] = [$posX + $dX, $posY + $dY];
            if ($fooX >= 0 && $fooY >= 0 && $fooX < $width && $fooY < $height && !isset($pathMap[$fooY][$fooX]) && !isset($leftRight[$lr][$fooY][$fooX]) && !isset($checked[$lr][$fooY][$fooX]) && !in_array($foo, $queue[$lr], true)) {
                $queueItems[] = $foo;
                $addedCount++;
            }
            $checked[$lr][$fooY][$fooX] = true;
        }
    }
}


// Draw map for good measure...
echo '    ';
for ($x = 0; $x < $width; $x++) {
    echo ($x % 10) === 0 ? sprintf('%-10s', floor($x / 10) . 'x') : '';
}
echo "\n";
echo '    ';
for ($x = 0; $x < $width; $x++) {
    echo $x % 10;
}
echo "\n";
foreach ($tiles as $y => $row) {
    printf('%3d ', $y);
    foreach ($row as $x => $tile) {
        if (isset($leftRight[0][$y][$x])) {
            echo 'A';
        } elseif (isset($leftRight[1][$y][$x])) {
            echo 'B';
        } elseif (isset($pathMap[$y][$x])) {
            echo match ($tile) {
                '|' => '║',
                '-' => '═',
                'L' => '╚',
                'J' => '╝',
                '7' => '╗',
                'F' => '╔',
                default => $tile,
            };
        } else {
            echo match ($tile) {
                '|' => '│',
                '-' => '─',
                'L' => '└',
                'J' => '┘',
                '7' => '┐',
                'F' => '┌',
                default => $tile,
            };
        }
    }
    echo "\n";
}

echo \PHP_EOL, 'Part 2: A: ', $count[0], ', B: ', $count[1], '... check above which one is _inside_. (Probably ', min($count), ')', \PHP_EOL;
