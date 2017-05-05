<?php

namespace Bavix\Benchmark;

use Bavix\Foundation\Arrays\Collection;

class Benchmark
{

    /**
     * @var int
     */
    protected $iterations;

    /**
     * @var int
     */
    protected $iterate;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * Benchmark constructor.
     *
     * @param int   $iterations
     * @param array ...$arguments
     */
    public function __construct($iterations = 100, ...$arguments)
    {
        set_time_limit(0);
        $this->iterations = $iterations;
        $this->arguments  = $arguments;
    }

    /**
     * @param callable $callback
     * @param string   $name
     *
     * @return $this
     */
    public function task(callable $callback, $name = null)
    {
        if (is_scalar($callback) && null === $name)
        {
            $name = $callback;
        }

        if (null !== $name)
        {
            $this->data[$name] = $callback;
        }
        else
        {
            $this->data[] = $callback;
        }

        return $this;
    }

    /**
     * @return float
     */
    protected function time()
    {
        if (function_exists('\xdebug_time_index'))
        {
            return \xdebug_time_index();
        }

        return \microtime(true);
    }

    /**
     * @return float
     */
    protected function usage()
    {
        if (function_exists('xdebug_memory_usage'))
        {
            return \xdebug_memory_usage();
        }

        return \memory_get_usage();
    }

    /**
     * @return float
     */
    protected function peak()
    {
        if (function_exists('xdebug_peak_memory_usage'))
        {
            return \xdebug_peak_memory_usage();
        }

        return \memory_get_peak_usage();
    }

    public function iterate()
    {
        return $this->iterate-- > 0;
    }

    /**
     * @param callable $callback
     *
     * @return array
     */
    protected function callback(callable $callback)
    {
        $this->iterate = $this->iterations;

        $usageBegin = $this->usage();
        $begin      = $this->time();
        $timeAvg    = [];
        $memoryAvg  = [];

        while ($this->iterate())
        {
            $time   = $this->time();
            $memory = $this->usage();

            $callback(...$this->arguments);

            $timeAvg[]   = $this->time() - $time;
            $memoryAvg[] = $this->usage() - $memory;
        }

        $end      = $this->time();
        $usageEnd = $this->usage();
        $peak     = $this->peak();

        return [
            'time'       => [
                'usage' => $end - $begin,
                'sum'   => array_sum($timeAvg),
                'avg'   => array_sum($timeAvg) / count($timeAvg),
                'begin' => $begin,
                'end'   => $end,
            ],
            'memory'     => [
                'usage' => $usageEnd - $usageBegin,
                'sum'   => array_sum($memoryAvg),
                'avg'   => array_sum($memoryAvg) / count($memoryAvg),
                'begin' => $usageBegin,
                'end'   => $usageEnd,
                'peak' => $peak
            ]
        ];
    }

    /**
     * @return Collection
     */
    public function run()
    {
        $results = [];

        /**
         * @var callable $callback
         */
        foreach ($this->data as $name => $callback)
        {
            if (is_int($name))
            {
                $name = 'item_' . $name;
            }

            $results[$name] = $this->callback($callback);
        }

        return new Collection($results);
    }

}
