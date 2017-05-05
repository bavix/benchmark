<?php

include_once dirname(__DIR__) . '/vendor/autoload.php';

$bm = new \Bavix\Benchmark\Benchmark(1000, 0, 10000);

$bm->task('pow');
$bm->task('random_int');

$bm->run()
    ->filter(function ($v, $k) {
        var_dump( [$k, $v] );
    });
