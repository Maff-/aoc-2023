<?php

declare(strict_types=1);

$input = file_exists('input.txt') ? file_get_contents('input.txt') : null;

$input ??= <<<EXAMPLE
#.##..##.
..#.##.#.
##......#
##......#
..#.##.#.
..##..##.
#.#.##.#.

#...##..#
#....#..#
..##..###
#####.##.
#####.##.
..##..###
#....#..#
EXAMPLE;

$input = explode("\n\n", trim($input));
$input = array_map(static function ($block) {
    return array_map(static function ($line) {
        return $line;
    }, explode("\n", $block));
}, $input);

$patterns = $input;

// Part 1

function summarize(array $pattern, ?int $ignore = null): int
{
    $width = strlen($pattern[0]);
    $height = count($pattern);

    for ($x = 1; $x < $width; $x++) {
        if ($x === $ignore) {
            continue;
        }
        $foo = min($x, $width - $x);
        $i = 0;
        $r = $x + $i;
        $l = $x - $i - 1;
        for (; $i <= $foo && $l >= 0 && $r < $width; $i++, $r++, $l--) {
            foreach ($pattern as $row) {
                if ($row[$l] !== $row[$r]) {
                    continue 3;
                }
            }
        }
        return $x;
    }

    for ($y = 1; $y < $height; $y++) {
        if ($y * 100 === $ignore) {
            continue;
        }
        $foo = min($y, $height - $y);
        $i = 0;
        $a = $y + $i;
        $b = $y - $i - 1;
        for (; $i <= $foo && $a >= 0 && $b < $height; $i++, $b++, $a--) {
            for ($x = 0; $x < $width; $x++) {
                if ($pattern[$a][$x] !== $pattern[$b][$x]) {
                    continue 3;
                }
            }
        }
        return $y * 100;
    }

    return -1;
}

$sum = null;
foreach ($patterns as $n => $pattern) {
    $result = summarize($pattern);
    if ($result === -1) {
        throw new \RuntimeException('No reflection???');
    }
    $sum += $result;
}

echo 'Part 1: ', $sum, \PHP_EOL;

// Part 2

$sum = null;
foreach ($patterns as $n => $pattern) {
    $width = strlen($pattern[0]);
    $height = count($pattern);

    $orgResult = summarize($pattern);
    for ($x = 0; $x < $width; $x++) {
        for ($y = 0; $y < $height; $y++) {
            $alt = $pattern;
            $alt[$y][$x] = $alt[$y][$x] === '#' ? '.' : '#';
            $altResult = summarize($alt, $orgResult);
            if ($altResult !== -1 && $altResult !== $orgResult) {
                $sum += $altResult;
                continue 3;
            }
        }
    }

    throw new \RuntimeException('No alternative reflection found!?');
}

echo 'Part 2: ', $sum, \PHP_EOL;

