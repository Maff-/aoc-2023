<?php

declare(strict_types=1);

$input = file_exists('input.txt') ? file_get_contents('input.txt') : null;

$input ??= <<<EXAMPLE
LLR

AAA = (BBB, BBB)
BBB = (AAA, ZZZ)
ZZZ = (ZZZ, ZZZ)
EXAMPLE;

$input = explode("\n\n", trim($input));
$directions = str_split($input[0]);
$map = [];
foreach (explode("\n", $input[1]) as $line) {
    preg_match('/(\w+) = \((\w+), (\w+)\)/', $line, $match) ?: throw new \RuntimeException(sprintf('Regex did not match "%s"', $line));
    $map[$match[1]] = ['L' => $match[2], 'R' => $match[3]];
}
unset($match, $line);

// Part 1

$target = 'ZZZ';
$current = 'AAA';
$step = 0;
while ($current !== $target) {
    $direction = $directions[$step % count($directions)];
    $next = $map[$current][$direction];
    $step++;
    $current = $next;
}

echo 'Part 1: ', $step, \PHP_EOL;

// Part 2

function gcd(int $a, int $b): int
{
    return $b === 0 ? $a : gcd($b, $a % $b);
}

function lcm(...$values): int
{
    $result = array_shift($values);
    foreach ($values as $val) {
        $result = $val / gcd($val, $result) * $result;
    }

    return $result;
}

$nodes = array_filter(array_keys($map), static fn($node) => str_ends_with($node, 'A'));
$directionsCount = count($directions);
$steps = [];
foreach ($nodes as $n => $current) {
    $step = 0;
    while (true) {
        $direction = $directions[$step % $directionsCount];
        $next = $map[$current][$direction];
        $step++;
        if ($next[2] === 'Z') {
            break;
        }
        $current = $next;
    }
    $steps[$n] = $step;
}

$result = lcm(...$steps);

echo 'Part 2: ', $result, \PHP_EOL;
