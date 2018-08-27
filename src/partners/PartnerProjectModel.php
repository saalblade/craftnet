<?php

namespace craftnet\partners;


use craft\base\Model;

class PartnerProjectModel extends Model
{
    const SCENARIO_LIVE = 'live';

    public $id;
    public $partnerId;
    public $url;
    public $screenshotId;
    public $private;
    public $dateCreated;
    public $dateUpdated;
    public $uid;

    public function rules()
    {
        $rules = parent::rules();

        $rules[] = ['url', 'required'];
        $rules[] = ['url', 'url'];

        $rules[] = [
            ['screeshot'],
            'required',
            'on' => self::SCENARIO_LIVE,
        ];

        $rules[] = ['private', 'default', 'value' => false];

        return $rules;
    }
}
