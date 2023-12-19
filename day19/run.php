<?php

declare(strict_types=1);

$input = file_exists('input.txt') ? file_get_contents('input.txt') : null;

$input ??= <<<'EXAMPLE'
px{a<2006:qkq,m>2090:A,rfg}
pv{a>1716:R,A}
lnx{m>1548:A,A}
rfg{s<537:gd,x>2440:R,A}
qs{s>3448:A,lnx}
qkq{x<1416:A,crn}
crn{x>2662:A,R}
in{s<1351:px,qqz}
qqz{s>2770:qs,m<1801:hdj,R}
gd{a>3333:R,R}
hdj{m>838:A,pv}

{x=787,m=2655,a=1222,s=2876}
{x=1679,m=44,a=2067,s=496}
{x=2036,m=264,a=79,s=2244}
{x=2461,m=1339,a=466,s=291}
{x=2127,m=1623,a=2188,s=1013}
EXAMPLE;

$input = explode("\n\n", trim($input));
$input = array_map(static fn ($lines) => explode("\n", $lines), $input);
$workflows = array_map(static function (string $line): array {
    [, $label, $rules] = preg_match('/^(\w+)\{(.+?)}$/', $line, $match)
        ? $match
        : throw new \RuntimeException("Regex did not match '{$line}'");
    $rules = array_map(static function (string $rule): array {
        if (preg_match('/^(\w+)([<>])(\d+):(\w+)$/', $rule, $ruleMatch)) {
            return ['I', $ruleMatch[1], $ruleMatch[2], (int)$ruleMatch[3], $ruleMatch[4]];
        }
        if (ctype_alpha($rule)) {
            return ['E', $rule];
        }
        throw new \RuntimeException("Regex did not rule '{$rule}'");
    }, explode(',', $rules));

    return [$label, $rules];
}, $input[0]);
$workflows = array_column($workflows, 1, 0);

$parts = array_map(static function (string $line): array {
    return preg_match_all('/([xmas])=(\d+)/', $line, $match, \PREG_SET_ORDER)
        ? array_map('intval', array_column($match, 2, 1))
        : throw new \RuntimeException("Regex did not match '{$line}'");
}, $input[1]);

// Part 1

function process(array $part, array $workflows, string $workflow = 'in'): string
{
    $rules = $workflows[$workflow];
    $next = null;
    foreach ($rules as $rule) {
        if ($rule[0] === 'E') {
            $next = $rule[1];
            break;
        }
        [, $attr, $operator, $value, $result] = $rule;
        $next = match ($operator) {
            '<' => $part[$attr] < $value ? $result : null,
            '>' => $part[$attr] > $value ? $result : null,
        };
        if ($next !== null) {
            break;
        }
    }
    return match ($next) {
        null => throw new \RuntimeException('failed....'),
        'A', 'R' => $next,
        default => process($part, $workflows, $next)
    };
}

function sumAccepted(array $workflows, array $parts): int
{
    $sum = 0;
    foreach ($parts as $part) {
        if (process($part, $workflows) === 'A') {
            $sum += array_sum($part);
        }
    }
    return $sum;
}

$result = sumAccepted($workflows, $parts);

echo 'Part 1: ', $result, \PHP_EOL;
