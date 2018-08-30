<?php

namespace craftnet\partners;


use craft\base\Model;
use craft\elements\Asset;

class PartnerProjectModel extends Model
{
    const SCENARIO_LIVE = 'live';

    public $id;
    public $partnerId;
    public $url;
    public $private;
    public $dateCreated;
    public $dateUpdated;
    public $uid;

    /**
     * Not a column in the table, this is a property for
     * api and templates. During a POST request this will
     * be an array of Asset ids. When a GET request, this
     * will be an array of Asset instances.
     * @var array
     */
    public $screenshots;

    /**
     * @inheritdoc
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = ['url', 'required'];
        $rules[] = ['url', 'url'];

        $rules[] = [
            ['screenshots'],
            'required',
            'on' => self::SCENARIO_LIVE,
        ];

        $rules[] = ['private', 'default', 'value' => false];

        return $rules;
    }

    /**
     * Depending on the request method, `screenshots` will be either
     * an array of Asset ids or an array of Asset instances.
     * @return array
     */
    public function getScreenshotIds()
    {
        if (!isset($this->screenshots)) {
            return [];
        }

        $ids = array_map(function($val) {
           return ($val instanceof Asset) ? $val->id : $val;
        }, $this->screenshots);

        return $ids;
    }
}
