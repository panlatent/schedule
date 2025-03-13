# Upgrade 1.x

Congratulations! Welcome to version 1.0 of Schedule. The new version is simpler and more powerful.

This is a 0.x - 1.0 update note. This article assumes that you have already used the 0.x version of the plugin.

## Documentation Promise

The new version will promise a relatively complete documentation

## Schedule

The new **Schedule** class is no longer a subclass of *SavableComponent* but a *Model*. We provide a new *Action* class abstraction to simplify development.

Schedule consists of timers, conditions and actions, and you can set actions to be triggered after running. 
We have added some new actions, such as *SendEmail*. This means that in addition to making a Schedule to send an email, 
we can also make a Schedule to send a HTTP request and send an email after failure.

## Action

Action is an ambitious part. It is easy to extend and has simple responsibilities. It can handle any code logic.

## Programmability

We strive to provide everything in CP, but it is not enough. 

Sometimes people want to create new schedules in code, and now there is a new way:

```php
<?php // config/schedule.php

use panlatent\schedule\builder\Buidler;

return [
    'schedules' => [
        Buidler::command('clear-caches/all')->hourly(),
    ]
];
```

No database and no Project config, only commit your code.

## Static Group

In an earlier version, we added Static Schedule, which enables migration with project configuration. Unfortunately,
in this implementation, static schedules could not be grouped.

Now, we have redesigned this feature. Static groups are available.

## Scheduler

Scheduler is a new component that is responsible for Schedule scheduling on console. 
The `listen` method based on ReactPHP Event Loop can implement functions such as second-level timer and heartbeat detection.
Unlike task queues, Schedule often has time-sensitive requirements. It has concurrency options and can be controlled in sequence or concurrency.

## Logger

Logging is decoupled in the new version. You can freely use any PSR logger (including Yii2 Log) to log to a database, file or anywhere else.

## Web Cron

The best way to trigger a Schedule is from the command line or the operating system Crontab, but some web hosts and Craft Cloud do not provide this setting.

In the new version, we support scheduling via Web Cron.

## Webhook Integration


## Future plans

### The Web Cron Job Service

There are plans to develop a website that supports Web Cron based on CraftCMS with the plugin, looking for pricing, integration and functional verification.


