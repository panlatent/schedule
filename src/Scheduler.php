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
    public ?int $maxConcurrent = null;

    public int $heartbeatSeconds = 5;

    public ?\DateTimeZone $timezone = null;

    public bool $useTimestamp = false;

    public function listen(\Closure $callback = null): void
    {
        if (\PHP_SAPI !== 'cli') {
            throw new \RuntimeException('Scheduler can only be run from the CLI.');
        }

        $loop = Loop::get();

        $dispatch = function($dispatch) use($loop) {
            $loop->addTimer($this->getSecondsToNext(10), function() use($dispatch) {
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
        echo '10:' . (new \DateTime())->format('Y-m-d H:i:s') . "\n";
        $timers = $this->getTriggerTimers();

        foreach ($timers as $timer) {
            if (!$this->maxConcurrent) {
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
        try {
            $schedule->run();
            $duration = round((microtime(true) - $start), 2);
        } catch (\Throwable $e) {
            $duration = round((microtime(true) - $start), 2);
            return false;
        }

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
}