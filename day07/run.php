<?php

declare(strict_types=1);

$input = file_exists('input.txt') ? file_get_contents('input.txt') : null;

$input ??= <<<EXAMPLE
32T3K 765
T55J5 684
KK677 28
KTJJT 220
QQQJA 483
EXAMPLE;

$input = array_map(
    static fn($line) => [str_split(explode(' ', $line)[0]), (int)explode(' ', $line)[1]],
    explode("\n", trim($input)),
);

$strength = array_flip(array_reverse(str_split('AKQJT98765432')));

// Part 1

function getHandType(array $hand): int
{
    $foo = array_count_values($hand);
    arsort($foo);
    $bar = array_values($foo);

    return match (true) {
        $bar[0] === 5 => 6, // Five of a kind
        $bar[0] === 4 => 5, // Four of a kind
        $bar[0] === 3 && $bar[1] === 2 => 4, // Full house
        $bar[0] === 3 => 3, // Three of a kind
        $bar[0] === 2 && $bar[1] === 2 => 2, // Two pair
        $bar[0] === 2 => 1, // One pair
        default => 0, // High card
    };
}

assert(getHandType(str_split('32T3K')) === 1);
assert(getHandType(str_split('KK677')) === 2);
assert(getHandType(str_split('T55J5')) === 3);
assert(getHandType(str_split('KAKAK')) === 4);
assert(getHandType(str_split('44441')) === 5);
assert(getHandType(str_split('55555')) === 6);

function compareHands(array $a, array $b): int
{
    global $strength;
    $comparison = getHandType($a) <=> getHandType($b);
    $pos = 0;
    while ($comparison === 0 && $pos < 5) {
        $comparison = $strength[$a[$pos]] <=> $strength[$b[$pos]];
        $pos++;
    }
    return $comparison;
}

$hands = $input;
usort($hands, static fn ($a, $b) => compareHands($a[0], $b[0]));

$total = 0;
foreach ($hands as $n => [$hand, $bid]) {
    $total += ($n+1) * $bid;
}

echo 'Part 1: ', $total, \PHP_EOL;

// Part 2

$strength = array_flip(array_reverse(str_split('AKQT98765432J')));

function getHandType2(array $hand): int
{
    $count = array_count_values($hand);
    arsort($count);
    $jokerCount = $count['J'] ?? 0;
    $countValues = array_values(array_diff_key($count, ['J' => true]));
    $countValues[0] ??= 0;

    return match (true) {
        $countValues[0] + $jokerCount === 5 => 6, // Five of a kind
        $countValues[0] + $jokerCount === 4 => 5, // Four of a kind
        $countValues[0] + $jokerCount === 3 && $countValues[1] === 2 => 4, // Full house
        $countValues[0] + $jokerCount === 3 => 3, // Three of a kind
        $countValues[0] + $jokerCount === 2 && $countValues[1] === 2 => 2, // Two pair
        $countValues[0] + $jokerCount === 2 => 1, // One pair
        default => 0, // High card
    };
}

assert(getHandType2(str_split('JJJJJ')) === 6);
assert(getHandType2(str_split('J4441')) === 5);
assert(getHandType2(str_split('J2233')) === 4);
assert(getHandType2(str_split('J3321')) === 3);
assert(getHandType2(str_split('JJ123')) === 3);

function compareHands2(array $a, array $b): int
{
    global $strength;
    $comparison = getHandType2($a) <=> getHandType2($b);
    $pos = 0;
    while ($comparison === 0 && $pos < 5) {
        $comparison = $strength[$a[$pos]] <=> $strength[$b[$pos]];
        $pos++;
    }
    return $comparison;
}

$hands = $input;
usort($hands, static fn ($a, $b) => compareHands2($a[0], $b[0]));

$total = 0;
foreach ($hands as $n => [$hand, $bid]) {
    $total += ($n+1) * $bid;
}

echo 'Part 2: ', $total, \PHP_EOL;
