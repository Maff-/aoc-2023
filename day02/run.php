<?php

declare(strict_types=1);

$input = file_exists('input.txt') ? file_get_contents('input.txt') : null;

$input ??= <<<EXMAPLE
Game 1: 3 blue, 4 red; 1 red, 2 green, 6 blue; 2 green
Game 2: 1 blue, 2 green; 3 green, 4 blue, 1 red; 1 green, 1 blue
Game 3: 8 green, 6 blue, 20 red; 5 blue, 4 red, 13 green; 5 green, 1 red
Game 4: 1 green, 3 red, 6 blue; 3 green, 6 red; 3 green, 15 blue, 14 red
Game 5: 6 red, 1 blue, 3 green; 2 blue, 1 red, 2 green
EXMAPLE;

$input = explode("\n", trim($input));
$inputData = [];
foreach ($input as $line) {
    [$game, $revealsStr] = preg_match('/Game (\d+): (.+)/', $line, $match)
        ? [(int)$match[1], $match[2]]
        : throw new \RuntimeException(sprintf('Regex did not match line "%s"', $line));
    $reveals = array_map(static function ($reveal) {
        $set = array_map(static function ($colorNum) {
            [$num, $color] = explode(' ', $colorNum);
            return [(int)$num, $color];
        }, explode(', ', $reveal));
        return array_column($set, 0, 1);
    }, explode('; ', $revealsStr));
    $inputData[(int)$game] = $reveals;
}

// Part 1
$limit = ['red' => 12, 'green' => 13, 'blue' => 14];

$sum = 0;
foreach ($inputData as $game => $reveals) {
    $possible = true;
    foreach ($reveals as $set) {
        foreach ($limit as $color => $colorLimit) {
            if (($set[$color] ?? 0) > $colorLimit) {
                $possible = false;
                continue 2;
            }
        }
    }
    if ($possible) {
        $sum += $game;
    }
}

echo 'Part 1: ', $sum, \PHP_EOL;
