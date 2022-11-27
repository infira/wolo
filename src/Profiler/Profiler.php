<?php

namespace Wolo\Profiler;

class Profiler
{
    private array $description;
    private array $startTime;
    private array $endTime;
    private float $initTime;
    private string $cur_timer;
    private array $stack;
    private string $trace;
    private array $count;
    private array $running;
    private bool $halted = false;

    public function __construct()
    {
        $this->description = [];
        $this->startTime = [];
        $this->endTime = [];
        $this->cur_timer = "";
        $this->stack = [];
        $this->trace = "";
        $this->count = [];
        $this->running = [];
        $this->initTime = microtime(true);
    }

    /**
     * Halt current Profiler instance
     *
     * @return $this
     */
    public function halt(): static
    {
        $this->halted = true;

        return $this;
    }

    /**
     * Continue current Profiler instance measuring
     *
     * @return $this
     */
    public function continue(): static
    {
        $this->halted = false;

        return $this;
    }

    /** Is measuring halted */
    public function isHalted(): bool
    {
        if (Prof::isHalted()) {
            return true;
        }

        return $this->halted;
    }

    /**
     * Start an individual timer
     * This will pause the running timer and place it on a stack.
     *
     * @param  string  $name  name of the timer
     * @param  string  $desc  optional $desc description of the timer
     * @return void
     */
    public function start(string $name, string $desc = ""): void
    {
        if ($this->isHalted()) {
            return;
        }
        $this->trace .= "start   $name\n";
        $n = array_push($this->stack, $this->cur_timer);
        $this->suspendTimer($this->stack[$n - 1]);
        $this->startTime[$name] = microtime(true);
        $this->cur_timer = $name;
        $this->description[$name] = $desc;
        if (!array_key_exists($name, $this->count)) {
            $this->count[$name] = 1;
        }
        else {
            $this->count[$name]++;
        }
    }

    /**
     * Stop an individual timer
     * Restart the timer that was running before this one
     *
     * @param  string  $name  name of the timer
     * @return void
     */
    public function stop(string $name): void
    {
        if ($this->isHalted()) {
            return;
        }
        $this->trace .= "stop    $name\n";
        $this->endTime[$name] = microtime(true);
        if (!array_key_exists($name, $this->running)) {
            $this->running[$name] = $this->elapsedTime($name);
        }
        else {
            $this->running[$name] += $this->elapsedTime($name);
        }
        $this->cur_timer = array_pop($this->stack);
        $this->resumeTimer($this->cur_timer);
    }

    /**
     * Measure callable and return callable result
     * When Is halted then just runs action
     * @param  string  $name
     * @param  callable  $action
     * @param  mixed  ...$actionArguments
     * @return mixed
     */
    public function measure(string $name, callable $action, mixed ...$actionArguments): mixed
    {
        if ($this->isHalted()) {
            return $action(...$actionArguments);
        }
        $this->start($name);
        $output = $action(...$actionArguments);
        $this->stop($name);

        return $output;
    }

    /**
     * measure the elapsed time of a timer without stopping the timer if
     *
     * @param  string  $name
     * @return float
     */
    public function elapsedTime(string $name): float
    {
        // This shouldn't happen, but it does once.
        if (!array_key_exists($name, $this->startTime)) {
            return 0;
        }

        if (array_key_exists($name, $this->endTime)) {
            return ($this->endTime[$name] - $this->startTime[$name]);
        }
        $now = microtime(true);

        return ($now - $this->startTime[$name]);
    }

    public function dump(): string
    {
        $totalTime = 0;
        $totalPercent = 0;
        ksort($this->description);
        $return = '<pre class="profiler">';
        $oaTime = microtime(true) - $this->initTime;

        $return .= '<div style="clear:both;height:1px;font-size:1px;border:none;background:transparent;"></div>';
        $return .= "============================================================================\n";
        $return .= "                              PROFILER OUTPUT\n";
        $return .= "============================================================================\n";
        $return .= "Nr.   Calls                    Time  Routine\n";
        $return .= "-----------------------------------------------------------------------------\n";
        $list = [];
        $nr = 0;
        foreach ($this->description as $key => $val) {
            $nr++;
            $total = $this->running[$key];
            $totalTime += $total;
            $count = $this->count[$key];
            $percent = ($total / $oaTime) * 100;
            $totalPercent += $percent;
            if (strpos($key, 'function')) {
                $key = '<strong style="color:#CC0000">'.$key.'</strong>';
            }
            $list[] = ['nr' => $nr, 'calls' => sprintf("%3d", $count), 'time' => $total, 'percent' => sprintf("%3.2f", $percent), 'name' => $key];
        }
        $this->orderByPercentage($list);
        $return .= '<style>
			table.profileTable th,table.profileTable td{padding:2px;background-color:#FFFFFF}
			</style>';
        $return .= '<table class="profileTable" cellpadding="1" cellspacing="1" style="background-color:black" border="1">';
        $return .= '<tr>';
        $return .= '<th> Nr </th>';
        $return .= '<th> calls </th>';
        $return .= '<th> time </th>';
        $return .= '<th> percent </th>';
        $return .= '<th> name </th>';
        $return .= '</tr>';
        $nr = 1;
        foreach ($list as $val) {
            $return .= '<tr style="background-color:#FFFFFF">';
            $return .= '<td> '.$nr.' </td>';
            $return .= '<td> '.$val['calls'].' </td>';
            $return .= '<td> '.sprintf("%3.6f", $val['time']).' </td>';
            $return .= '<td> '.$val['percent'].' </td>';
            $return .= '<td> '.$val['name'].' </td>';
            $return .= '</tr>';
            $nr++;
        }
        $return .= '</table>';

        $return .= "\n";

        $missed = $oaTime - $totalTime;
        $percent = ($missed / $oaTime) * 100;
        $totalPercent += $percent;
        $return .= sprintf("       %3.4fs (%3.2f%%)  %s\n", $missed, $percent, "Missed");

        $return .= "============================================================================\n";

        $return .= sprintf("       %3.4fs (%3.2f%%)  %s\n", $oaTime, $totalPercent, "OVERALL TIME");

        $return .= "============================================================================\n";
        $return .= "</pre>";

        return $return;
    }

    public function print(): void
    {
        echo $this->dump();
    }

    private function orderByPercentage(array &$data): void
    {
        $orderByKey = 'percent';

        $sortArr = array_map(static fn($row) => (float)$row[$orderByKey], $data);
        arsort($sortArr);
        $result = [];
        foreach (array_keys($sortArr) as $k) {
            $result[] = $data[$k];
        }
        $data = $result;
    }

    /**
     * resume  an individual timer
     *
     * @param  string  $name
     * @return void
     */
    private function resumeTimer(string $name): void
    {
        $this->trace .= "resume  $name\n";
        $this->startTime[$name] = microtime(true);
    }

    /**
     * suspend  an individual timer
     *
     * @param  string  $name
     * @return void
     */
    private function suspendTimer(string $name): void
    {
        $this->trace .= "suspend $name\n";
        $this->endTime[$name] = microtime(true);
        if (!array_key_exists($name, $this->running)) {
            $this->running[$name] = $this->elapsedTime($name);
        }
        else {
            $this->running[$name] += $this->elapsedTime($name);
        }
    }
}