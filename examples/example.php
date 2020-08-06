<?php

require_once '../src/App/ParallelExecutor.php';

$executor = new \App\ParallelExecutor(3);

$i = 0;
$startTime = microtime(true);
while ($i < 10) {
    $i++;
    printf("[%.4f]  execute" . PHP_EOL, microtime(true) - $startTime, $i);
    $executor->execute(
        function ($i) use ($startTime) {
            $sleep = random_int(1, 5);
            printf("[%.4f] %2d: Start sleeping {$sleep} s..." . PHP_EOL, microtime(true) - $startTime, $i);
            sleep($sleep);
            printf("[%.4f] %2d: DONE" . PHP_EOL, microtime(true) - $startTime, $i);
        },
        [ $i ]
    );
}
