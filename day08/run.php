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
