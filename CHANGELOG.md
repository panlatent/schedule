# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added
### Updated
### Fixed

## [0.2.2] - 2019-10-10
### Added
- Add clear all logs.
- Add enable/disable schedule on list page.
- Add DateTime timer.

### Updated
- Only allow admin show cp nav item and manage plugin settings.
- Advance schedule edit page.

### Fixed
- Fix schedule handle unique scope from group to global.
- Fix console schedule is not use php cli path settings.

## [0.2.1] - 2019-08-27
### Added
- Add schedule enabled setting.

### Fixed
- Fix #5 ClassHelper::findClasses() not call in `composer --no-dev`
- Fix #6 Command schedule type only exceeded timeout of 60 seconds

## [0.2.0] - 2019-08-08
### Added
- Add schedule logs.
- Add relay timer.

### Updated
- Now require new library process cron expression description.
- Advance queue schedule edit page.

### Fixed
- Fix #3 not normalize relay timer express.
- Fix #4 unable to run queue schedule.
- Fix plugin setting cliPath validator.

## [0.1.6] - 2019-04-26
### Added
### Updated
- Advance schedule classes: Add run method and events.
- Show last run date on CP.

### Fixed
- Fixed [#1](https://github.com/panlatent/schedule/issues/1) console schedule arguments property is invalid.

## [0.1.5] - 2019-03-12
### Added
- Added some new language translations.

### Updated
- Advance cron description format. e.g. `1st to 20th minutes, every 2 hour, every 3 day`

### Fixed
- Fixed CronHelper::toDescription can't convert standard timer.
- Fixed make ordinal numeral error when value is less than 1.

## [0.1.4] - 2019-03-05
### Added
- Add `CronHelper` class.
- Show schedule cron description `when` column in schedules list.

### Fixed
- Remove duplicate command.
- Fixed schedule::getCronExpression() returns error expression.

## [0.1.3] - 2019-03-04
### Fixed
- Fixed a Install migration error.