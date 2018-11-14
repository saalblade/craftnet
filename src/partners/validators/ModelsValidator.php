<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 11/13/18
 * Time: 4:05 PM
 */

namespace craftnet\partners\validators;


use Craft;
use craft\base\Element;
use craft\base\Model;
use yii\validators\Validator;

class ModelsValidator extends Validator
{
    public $message = '{attribute} errors found';

    public function validateAttribute($model, $attribute)
    {
        return parent::validateAttribute($model, $attribute);
    }

    /**
     * Triggers validation on attributes which are arrays of models.
     *
     * @param mixed $value
     * @return array|null Null if valid
     */
    public function validateValue($value)
    {
        if (empty($value)) {
            return null;
        }

        $modelErrorFound = false;

        /** @var Model $model */
        foreach ($value as $model) {
            $model->setScenario(Element::SCENARIO_LIVE);
            $modelErrorFound += !$model->validate();
        }

        if ($modelErrorFound) {
            return [Craft::t('yii', $this->message), []];
        }

        return null;
    }
}
