<?php

namespace Wolo\Profiler;

use Exception;

class Profiler
{
	private array  $description;
	private array  $startTime;
	private array  $endTime;
	private float  $initTime;
	private string $cur_timer;
	private array  $stack;
	private string $trail;
	private string $trace;
	private array  $count;
	private array  $running;
	
	public function __construct()
	{
		$this->description = [];
		$this->startTime   = [];
		$this->endTime     = [];
		$this->cur_timer   = "";
		$this->stack       = [];
		$this->trail       = "";
		$this->trace       = "";
		$this->count       = [];
		$this->running     = [];
		$this->initTime    = microtime(true);
	}
	
	/**
	 * U can set to void void usage of profiler
	 *
	 * @return void
	 */
	public function void(): void
	{
		if (!defined("VOID_PROFILER")) {
			define("VOID_PROFILER", true);
		}
	}
	
	/**
	 * Start an individual timer
	 * This will pause the running timer and place it on a stack.
	 *
	 * @param string $name name of the timer
	 * @param string optional $desc description of the timer
	 * @return void
	 */
	public function start(string $name, $desc = ""): void
	{
		if (!$this->isVoided()) {
			$this->trace .= "start   $name\n";
			$n           = array_push($this->stack, $this->cur_timer);
			$this->suspendTimer($this->stack[$n - 1]);
			$this->startTime[$name]   = microtime(true);
			$this->cur_timer          = $name;
			$this->description[$name] = $desc;
			if (!array_key_exists($name, $this->count)) {
				$this->count[$name] = 1;
			}
			else {
				$this->count[$name]++;
			}
		}
	}
	
	/**
	 * Stop an individual timer
	 * Restart the timer that was running before this one
	 *
	 * @param string $name name of the timer
	 * @return void
	 */
	public function stop(string $name): void
	{
		if (!$this->isVoided()) {
			$this->trace          .= "stop    $name\n";
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
	}
	
	public function measure(string $name, callable $measurable, mixed ...$measurableArguments)
	{
		$this->start($name);
		$measurable(...$measurableArguments);
		$this->start($name);
	}
	
	/**
	 * measure the elapsed time of a timer without stoping the timer if
	 *
	 * @param string $name
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
		else {
			$now = microtime(true);
			
			return ($now - $this->startTime[$name]);
		}
	}
	
	public function dump(): string
	{
		$allTotal = 0;
		$tot_perc = 0;
		ksort($this->description);
		$return = "";
		$return .= '<pre class="profiler">';
		$oaTime = microtime(true) - $this->initTime;
		
		$return .= '<div style="clear:both;height:1px;font-size:1px;border:none;background:transparent;"></div>';
		$return .= "============================================================================\n";
		$return .= "                              PROFILER OUTPUT\n";
		$return .= "============================================================================\n";
		$return .= "Nr.   Calls                    Time  Routine\n";
		$return .= "-----------------------------------------------------------------------------\n";
		$list   = [];
		$nr     = 0;
		foreach ($this->description as $key => $val) {
			$nr++;
			$total    = $this->running[$key];
			$allTotal += $total;
			$count    = $this->count[$key];
			$perc     = ($total / $oaTime) * 100;
			$tot_perc += $perc;
			if (strpos($key, 'function')) {
				$key = '<strong style="color:#CC0000">' . $key . '</strong>';
			}
			$list[] = ['nr' => $nr, 'calls' => sprintf("%3d", $count), 'time' => $total, 'percent' => sprintf("%3.2f", $perc), 'name' => $key];
		}
		$this->orderByField($list, 'percent', true);
		$return .= '<style>
			table.profileTable th,table.profileTable td{padding:2px;background-color:#FFFFFF}
			</style>';
		$return .= '<table class="profileTable" cellpadding="1" cellspacing="1" style="background-color:black" border="1">';
		$return .= '<tr>';
		$return .= '<th> Nr </th>';
		$return .= '<th> calls </th>';
		$return .= '<th> time </th>';
		$return .= '<th> perscent </th>';
		$return .= '<th> name </th>';
		$return .= '</tr>';
		$nr     = 1;
		foreach ($list as $val) {
			$return .= '<tr style="background-color:#FFFFFF">';
			$return .= '<td> ' . $nr . ' </td>';
			$return .= '<td> ' . $val['calls'] . ' </td>';
			$return .= '<td> ' . sprintf("%3.6f", $val['time']) . ' </td>';
			$return .= '<td> ' . $val['percent'] . ' </td>';
			$return .= '<td> ' . $val['name'] . ' </td>';
			$return .= '</tr>';
			$nr++;
		}
		$return .= '</table>';
		
		$return .= "\n";
		
		$missed   = $oaTime - $allTotal;
		$perc     = ($missed / $oaTime) * 100;
		$tot_perc += $perc;
		// $perc=sprintf("%3.2f", $perc );
		$return .= sprintf("       %3.4fs (%3.2f%%)  %s\n", $missed, $perc, "Missed");
		
		$return .= "============================================================================\n";
		
		$return .= sprintf("       %3.4fs (%3.2f%%)  %s\n", $oaTime, $tot_perc, "OVERALL TIME");
		
		$return .= "============================================================================\n";
		$return .= "</pre>";
		
		return $return;
	}
	
	public function print()
	{
		echo $this->dump();
	}
	
	/**
	 * @return bool
	 */
	private function isVoided(): bool
	{
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			return true;
		}
		if (defined("VOID_PROFILER")) {
			if (VOID_PROFILER === true) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Sort array by fields
	 *
	 * @param array  $data
	 * @param string $field
	 * @param bool   $descending
	 * @throws \Exception
	 */
	private function orderByField(array &$data, string $field, bool $descending)
	{
		$sortArr = [];
		
		foreach ($data as $key => $value) {
			if (!isset($value[$field])) {
				debug_print_backtrace();
				throw new Exception($field . ' is missing in the sortable array');
			}
			$va = $value[$field];
			if ($field == "percent") {
				$va = (float)$va;
			}
			if ($field == "time") {
				$va = (int)$va;
			}
			$sortArr[$key] = $va;
		}
		if ($descending) {
			arsort($sortArr);
		}
		else {
			asort($sortArr);
		}
		
		$resultArr = [];
		foreach ($sortArr as $key => $value) {
			$resultArr[$key] = $data[$key];
		}
		$data = $resultArr;
	}
	
	/**
	 * resume  an individual timer
	 *
	 * @param string $name
	 * @return void
	 */
	private function resumeTimer(string $name): void
	{
		$this->trace            .= "resume  $name\n";
		$this->startTime[$name] = microtime(true);
	}
	
	/**
	 * suspend  an individual timer
	 *
	 * @param string $name
	 * @return void
	 */
	private function suspendTimer(string $name): void
	{
		$this->trace          .= "suspend $name\n";
		$this->endTime[$name] = microtime(true);
		if (!array_key_exists($name, $this->running)) {
			$this->running[$name] = $this->elapsedTime($name);
		}
		else {
			$this->running[$name] += $this->elapsedTime($name);
		}
	}
}