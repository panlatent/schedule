<?php

namespace panlatent\schedule;

use Carbon\Carbon;
use Craft;
use Cron\CronExpression;
use panlatent\schedule\base\TimerInterface;
use panlatent\schedule\models\Schedule;
use React\EventLoop\Loop;
use Symfony\Component\Process\Process;

class Scheduler
{
    /**
     * @var int The maximum number of concurrent schedules. `0` for not used concurrent and `-1` for unlimited
     */
    public int $maxConcurrent = 0;

    public int $heartbeatSeconds = 5;

    public ?\DateTimeZone $timezone = null;

    public bool $useTimestamp = false;

    public function listen(\Closure $callback = null): void
    {
        if (\PHP_SAPI !== 'cli') {
            throw new \RuntimeException('Scheduler can only be run from the CLI.');
        }

        if ($this->maxConcurrent > 0 && !$this->validateRunCommand()) {
            throw new \RuntimeException('Cannot concurrent run scheduler due to schedules/run-schedule command error.');
        }

        if ($callback === null) {
            $callback = fn($message) => Craft::info($callback, 'scheduler');
        }

        $loop = Loop::get();

        $dispatch = function($dispatch) use($loop, $callback) {
            $waitSeconds = $this->getSecondsToNext();
            $callback("Waiting $waitSeconds seconds for next dispatch of scheduler\n");
            $loop->addTimer($waitSeconds, function() use($dispatch) {
                $this->dispatch();
                $dispatch($dispatch);
            });
        };
        $dispatch($dispatch);

        $loop->addPeriodicTimer($this->heartbeatSeconds, function () {
            echo '05:' . (new \DateTime())->format('Y-m-d H:i:s') . "\n";
        });

        $loop->run();
    }

    public function dispatch(): void
    {
        $timers = $this->getTriggerTimers();

        foreach ($timers as $timer) {
            if ($this->maxConcurrent === 0) {
                $timer->trigger();
                continue;
            }

            $process = new Process([PHP_BINARY, 'craft', 'schedules/run-schedule', $timer->getSchedule()->id], dirname(Craft::$app->request->getScriptFile()));
            $process->start();
        }
    }

    public function runSchedule(Schedule $schedule): bool
    {
        $start = microtime(true);
        $schedule->run();
//            $duration = round((microtime(true) - $start), 2);
//            $duration = round((microtime(true) - $start), 2);


        return true;
    }

    /**
     * @return TimerInterface[]
     */
    public function getTriggerTimers(): array
    {
        $timers = Plugin::getInstance()->timers->getActiveTimers();
        $now = new \DateTime('now', $this->timezone);
        return array_filter($timers, static function (TimerInterface $timer) use($now) {
            return (new CronExpression($timer->getCronExpression()))->isDue($now);
        });
    }

    protected function getSecondsToNext(int $seconds = 60): int
    {
        return $seconds - ($this->useTimestamp ? time()%$seconds : Carbon::now()->second%$seconds);
    }

    private function createRunProcess(): Process
    {
        return new Process([PHP_BINARY, 'craft', 'schedules/run-schedule'], dirname(Craft::$app->request->getScriptFile()));
    }

    private function validateRunCommand(): bool
    {
        $process = new Process([PHP_BINARY, 'craft', 'schedules/run-schedule', '--check'], dirname(Craft::$app->request->getScriptFile()));
        try {
            $process->run();
        } catch (\Throwable $e) {
            return false;
        }
        if (!$process->isSuccessful()) {
            return false;
        }
        return true;
    }
}