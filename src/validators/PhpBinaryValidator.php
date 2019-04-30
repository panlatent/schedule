<?php
/**
 * Schedule plugin for CraftCMS 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2019 panlatent@gmail.com
 */

namespace panlatent\schedule\validators;

use Symfony\Component\Process\Process;
use yii\validators\Validator;

/**
 * Class PhpBinaryValidator
 *
 * @package panlatent\schedule\validators
 * @author Panlatent <panlatent@gmail.com>
 */
class PhpBinaryValidator extends Validator
{
    /**
     * @var string|null php max version
     */
    public $maxVersion;

    /**
     * @var string|null php min version
     */
    public $minVersion;

    /**
     * @inheritdoc
     */
    public function validateValue($value)
    {
        $process = new Process([$value, '-v']);
        $process->run();

        if (!$process->isSuccessful()) {
            return ['Not found PHP binary in {path}', ['path' => $value]];
        }

        if (empty($this->maxVersion) && empty($this->minVersion)) {
            return null;
        }

        $output = $process->getOutput();
        if (!preg_match('#^PHP\s+(\d+(?:\.[\S])+)#', $output, $match)) {
            return ['Unknown PHP version'];
        }

        if ($this->maxVersion && version_compare($this->maxVersion, $match[1], '<')) {
            return ['PHP version {version} is larger than the required {maxVersion}', [
                'version' => $match[1],
                'maxVersion' => $this->maxVersion,
            ]];
        }

        if ($this->minVersion && version_compare($this->minVersion, $match[1], '>')) {
            return ['PHP version {version} is less than the required {minVersion}', [
                'version' => $match[1],
                'minVersion' => $this->minVersion,
            ]];
        }

        return null;
    }
}