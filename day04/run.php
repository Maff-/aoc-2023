<?php

declare(strict_types=1);

$input = file_exists('input.txt') ? file_get_contents('input.txt') : null;

$input ??= <<<EXAMPLE
Card 1: 41 48 83 86 17 | 83 86  6 31 17  9 48 53
Card 2: 13 32 20 16 61 | 61 30 68 82 17 32 24 19
Card 3:  1 21 53 59 44 | 69 82 63 72 16 21 14  1
Card 4: 41 92 73 84 69 | 59 84 76 51 58  5 54 83
Card 5: 87 83 26 28 32 | 88 30 70 12 93 22 82 36
Card 6: 31 18 13 56 72 | 74 77 10 23 35 67 36 11
EXAMPLE;

$input = array_map(static function (string $line): array {
    if (!preg_match('/^Card +(\d+): +([\d ]+?) +\| +([\d ]+)$/', $line, $match)) {
        throw new \RuntimeException(sprintf('Regex did not match "%s"', $line));
    }
    return [$game, $winning, $card] = [
        (int)$match[1],
        array_map('intval', preg_split('/\s+/', $match[2])),
        array_map('intval', preg_split('/\s+/', $match[3])),
    ];
}, explode("\n", trim($input)));

// Part 1

$sum = 0;
foreach ($input as [$game, $winning, $card]) {
    $count = count(array_intersect($winning, $card));
    $score = $count ? 1 << $count - 1 : 0;
    $sum += $score;
}

echo 'Part 1: ', $sum, \PHP_EOL;

// Part 2

$cardWinningCount = array_map(static fn($arg) => count(array_intersect($arg[1], $arg[2])), $input);
$cardCount = count($input);

for ($i = $cardCount - 1; $i >= 0; $i--) {
    for ($j = min($i + $cardWinningCount[$i], $cardCount - 1); $j > $i; $j--) {
        $cardWinningCount[$i] += $cardWinningCount[$j];
    }
}

echo 'Part 2: ', (array_sum($cardWinningCount) + $cardCount), \PHP_EOL;
