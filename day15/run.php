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
