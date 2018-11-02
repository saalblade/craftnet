<?php

namespace craftnet\partners;


use craft\base\Model;
use craft\elements\Asset;

class PartnerProject extends Model
{
    const SCENARIO_LIVE = 'live';

    public $id;
    public $partnerId;
    public $name;
    public $url;
    public $role;
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

        $rules[] = ['name', 'required'];
        $rules[] = ['url', 'url'];

        $rules[] = [
            ['url'],
            'required',
            'on' => self::SCENARIO_LIVE,
        ];

        return $rules;
    }

    /**
     * Depending on the request method, `screenshots` will be either
     * an array of Asset ids or an array of Asset instances.
     * @return array
     */
    public function getScreenshotIds()
    {
        if (!is_array($this->screenshots)) {
            return [];
        }

        $ids = array_map(function($val) {
           return ($val instanceof Asset) ? $val->id : $val;
        }, $this->screenshots);

        return $ids;
    }
}
