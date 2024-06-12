<?php

namespace panlatent\schedule\models;

use craft\base\Model;

class ScheduleTask extends Model
{
    public ?int $id = null;
    public ?int $scheduleId = null;
    public ?string $status = null;
    public ?string $reason = null;
    public ?int $startTime = null;
    public ?int $endTime = null;


}