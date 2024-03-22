<?php
/*
 * Schedule plugin for CraftCMS
 *
 * https://github.com/panlatent/schedule
 */

namespace panlatent\schedule\validators;

use Carbon\CarbonInterval;
use Carbon\Exceptions\InvalidIntervalException;
use yii\validators\Validator;


/**
 * Class CarbonStringIntervalValidator
 *
 * @package panlatent\schedule\validators
 * @author Panlatent <panlatent@gmail.com>
 */
class CarbonStringIntervalValidator extends Validator
{
    // Properties
    // =========================================================================

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function validateValue($value): ?array
    {
        try {
            if(CarbonInterval::make($value) === null) {
                return ['Not a valid Carbon interval "{value}"', ['value' => $value]];
            }

            return null;
        } catch(InvalidIntervalException) {
            return ['Not a valid Carbon interval "{value}"', ['value' => $value]];
        }
    }
}
