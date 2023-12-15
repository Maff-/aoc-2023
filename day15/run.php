<?php

declare(strict_types=1);

$input = file_exists('input.txt') ? file_get_contents('input.txt') : null;

$input ??= <<<EXAMPLE
rn=1,cm-,qp=3,cm=2,qp-,pc=4,ot=9,ab=5,pc-,pc=6,ot=7
EXAMPLE;

$input = explode(',', trim($input));

// Part 1

function _hash(string $input): int
{
    $len = strlen($input);
    $current = 0;
    for ($i = 0; $i < $len; $i++) {
        $current += ord($input[$i]);
        $current *= 17;
        $current %= 256;
    }
    return $current;
}

assert(_hash('HASH') === 52);

$result = array_sum(array_map('_hash', $input));

echo 'Part 1: ', $result, \PHP_EOL;

// Part 2

$boxes = array_fill(0, 256, [[], []]);

foreach ($input as $instruction) {
    [, $label, $operation, $focalLength] = preg_match('/^(\w+)([-=])(\d*)$/', $instruction, $match) ? $match : throw new \RuntimeException("Regex did not match '{$instruction}'");
    $n = _hash($label);
    switch ($operation) {
        case '-':
            // remove lens
            if (isset($boxes[$n][0][$label])) {
                $pos = array_search($label, $boxes[$n][1], true);
                array_splice($boxes[$n][1], $pos, 1);
                unset($boxes[$n][0][$label]);
            }
            break;
        case '=':
            // add/replace lens
            if (!isset($boxes[$n][0][$label])) {
                $boxes[$n][1][] = $label;
            }
            $boxes[$n][0][$label] = (int)$focalLength;
            break;
    }
}

$result = null;
foreach ($boxes as $n => $box) {
    foreach ($box[1] as $slot => $label) {
        $focalLength = $box[0][$label];
        $result += ($n + 1) * ($slot + 1) * $focalLength;
    }
}

echo 'Part 2: ', $result, \PHP_EOL;
