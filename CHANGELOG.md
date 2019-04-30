# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added
- Add schedule logs 

### Updated
### Fixed

### [0.2.0]


### [0.1.6] - 2019-04-26
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