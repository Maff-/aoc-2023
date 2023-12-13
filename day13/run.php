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

function summarize(array $pattern): int
{
    $width = strlen($pattern[0]);
    $height = count($pattern);

    for ($x = 1; $x < $width; $x++) {
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

    throw new \RuntimeException('No reflection???');
}

$result = null;
foreach ($patterns as $n => $pattern) {
    $result += summarize($pattern);
}

echo 'Part 1: ', $result, \PHP_EOL;
