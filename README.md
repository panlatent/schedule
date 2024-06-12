Schedule
========
[![Latest Stable Version](https://poser.pugx.org/panlatent/schedule/v/stable.svg)](https://packagist.org/packages/panlatent/schedule)
[![License](https://poser.pugx.org/panlatent/schedule/license.svg)](https://packagist.org/packages/panlatent/schedule)
[![Craft CMS](https://img.shields.io/badge/Powered_by-Craft_CMS-orange.svg?style=flat)](https://craftcms.com/)

Manage your CraftCMS application schedules in Cp. Run a command, trigger an event, 
push a queue task, or send HTTP requests at a specified time. You can flexibly customize
the trigger time and even design your schedule type.

Requirements
------------

This plugin requires Craft CMS 3.1 or later.

Installation
------------

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require panlatent/schedule

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Schedule.

4. Add a record to system crontab:

        * * * * * php /path/to/craft schedules/run 1>> /dev/null 2>&1

   Or use built-in `schedules/listen` command:

   ```shell
   $ ./craft schedules/listen
   ```

   If you use DDEV:

   ```shell
   $ ddev craft schedules/listen
   ```

Usage
------

For the command line we support some actions and options:

Delete logs 
```bash
craft schedules/clear-logs [interval]
```

Delete all logs
```bash
craft schedules/clear-logs --all
```

Run schedules asynchronously:
```bash
craft schedules/run --async
craft schedules/listen --async
```

Listen all active timer events with clear log:
```bash
craft schedules/listen --with-clear-log
```


Documentation
-------------
Read more about it, including install instructions, in the [official documentation](https://docs.panlatent.com/projects/schedule/).

License
-------
The Schedule is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).