Schedule
========
[![Build Status](https://travis-ci.org/panlatent/schedule.svg)](https://travis-ci.org/panlatent/schedule)
[![Coverage Status](https://coveralls.io/repos/github/panlatent/schedule/badge.svg?branch=master)](https://coveralls.io/github/panlatent/schedule?branch=master)
[![Latest Stable Version](https://poser.pugx.org/panlatent/schedule/v/stable.svg)](https://packagist.org/packages/panlatent/schedule)
[![Total Downloads](https://poser.pugx.org/panlatent/schedule/downloads.svg)](https://packagist.org/packages/panlatent/schedule) 
[![Latest Unstable Version](https://poser.pugx.org/panlatent/schedule/v/unstable.svg)](https://packagist.org/packages/panlatent/schedule)
[![License](https://poser.pugx.org/panlatent/schedule/license.svg)](https://packagist.org/packages/panlatent/schedule)
[![Craft CMS](https://img.shields.io/badge/Powered_by-Craft_CMS-orange.svg?style=flat)](https://craftcms.com/)
[![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)

Manage your CraftCMS application schedules in Cp. Run a command, trigger an event, 
push a queue task, or access a URL at a specified time. You can flexibly customize
the trigger time and even design your schedule type.

Requirements
------------

This plugin requires Craft CMS 3.0 or later.

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

Usages
------

License
-------
The Schedule is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).