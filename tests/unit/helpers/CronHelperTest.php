<?php namespace panlatent\schedule\tests\helpers;

use panlatent\schedule\helpers\CronHelper;

class CronHelperTest extends \Codeception\Test\Unit
{
    /**
     * @var \panlatent\schedule\tests\UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // Tests
    // =========================================================================

    public function testToCronExpression()
    {
        $this->assertEquals('* * * * * *', CronHelper::toCronExpression(''));
        $this->assertEquals('* * * * * *', CronHelper::toCronExpression('* * * * * '));
        $this->assertEquals('* * * * * *', CronHelper::toCronExpression('* * * * * *'));
        $this->assertEquals('1 * * * * *', CronHelper::toCronExpression(['1']));
        $this->assertEquals('0 * * * * *', CronHelper::toCronExpression(['0', '*', '*']));
        $this->assertEquals('1 * * */2 * *', CronHelper::toCronExpression(['minute' => 1, 'month' => '*/2']));
        $this->assertEquals('15 * * */2 * *', CronHelper::toCronExpression([15, 'month' => '*/2']));
    }

    public function testToDescription()
    {
        require dirname(dirname(dirname(__DIR__))) . '/vendor/yiisoft/yii2/Yii.php';
        require dirname(dirname(dirname(__DIR__))) . '/vendor/craftcms/cms/src/Craft.php';

        $this->assertEquals('At1MinutesPastTheHour', CronHelper::toDescription('1 * * * *'));
        $this->assertEquals('EveryMinute', CronHelper::toDescription('* * * * *'));
        $this->assertEquals('Every5Minutes', CronHelper::toDescription('*/5 * * * *'));
        $this->assertEquals('Every5MinutesCommaStartingAt20MinutesPastTheHour', CronHelper::toDescription('20/5 * * * *'));
        $this->assertEquals('Every10Minutes, At02:00 AMPeriod', CronHelper::toDescription('*/10 2 * * *'));
        $this->assertEquals('EveryMinute, Every5HoursCommaStartingAt08:00 PMPeriod', CronHelper::toDescription('* 20/5 * * * *'));
        $this->assertEquals('At3, 4,SpaceAndSpace5MinutesPastTheHour', CronHelper::toDescription('3,4,5 * * * *'));
        $this->assertEquals('At1SpaceAndSpace2MinutesPastTheHour, Every2HoursComaEvery2DaysCommaStartingComaOnDay3OfTheMonthComaAprilThroughMay', CronHelper::toDescription('1,2 */2 3/2 1-2 *'));
    }
}