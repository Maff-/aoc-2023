<?php

declare(strict_types=1);

$input = file_exists('input.txt') ? file_get_contents('input.txt') : null;

$input ??= <<<'EXAMPLE'
broadcaster -> a, b, c
%a -> b
%b -> c
%c -> inv
&inv -> a
EXAMPLE;

$input = explode("\n", trim($input));
$input = array_map(static function (string $line): array {
    [, $type, $name, $outputStr] = preg_match('/^([&%])?(\w+) -> ([\w, ]+)$/', $line, $match)
        ? $match
        : throw new \RuntimeException("Regex did not match line '{$line}'");
    $outputs = explode(', ', $outputStr);
    return [$name, $type ?: null, $outputs];
}, $input);

$initialModuleData = [null, [], [], null];
$modules = array_fill_keys(array_column($input, 0), $initialModuleData);
foreach ($input as [$name, $type, $outputs]) {
    $modules[$name][0] = $type;
    $modules[$name][1] = $outputs;
    foreach ($outputs as $output) {
        $modules[$output] ??= $initialModuleData;
        $modules[$output][2][] = $name;
    }
}
foreach ($modules as $name => [$type, $outputs, $inputs, &$state]) {
    switch ($type) {
        case '%':
            $state = 0;
            break;
        case '&':
            $state = array_fill_keys($inputs, 0);
            break;
    }
}
unset($state);

// Part 1

function hashState($modules): string
{
    return serialize(array_column($modules, 3));
}

function process(array &$modules, array &$counts = null): void
{
    $counts ??= [0 => 0, 1 => 0];

    $queue = [['broadcaster', 0, 'button']];
    while ($queue) {
        [$target, $pulse, $source] = array_shift($queue);
//        printf("%s -%s-> %s\n", $source, $pulse ? 'high' : 'low', $target);
        $counts[$pulse]++;
        [$type, $outputs, $inputs, $state] = $modules[$target];

        $out = null;
        if ($target === 'broadcaster') {
            $out = $pulse;
        } elseif ($type === '%') {
            if ($pulse === 0) {
                $state = (int)!$state;
                $out = $state;
            }
        } elseif ($type === '&') {
            $state[$source] = $pulse;
            $allHigh = array_sum($state) === count($state);
            $out = (int)!$allHigh;
        }

        $modules[$target][3] = $state;

        if ($out !== null && $outputs) {
            foreach ($outputs as $output) {
                $queue[] = [$output, $out, $target];
            }
        }
    }
}

$modulesAndState = $modules; // copy
$stateHashes = [];
$counts = null;
for ($i = 0; $i < 1000; $i++) {
//    echo "cycle {$i}:\n";
//    $hash = hashState($modulesAndState);
//    $prevI = $stateHashes[$hash] ?? null;
//    if ($prevI !== null) {
//        echo "loop detected; {$prevI} -> {$i}!\n";
//        break;
//    }
//    $stateHashes[$hash] = $i;
    process($modulesAndState, $counts);
}

$result = $counts[0] * $counts[1];

echo 'Part 1: ', $result, \PHP_EOL;
