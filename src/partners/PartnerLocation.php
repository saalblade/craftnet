<?php

namespace craftnet\partners;


use craft\base\Model;

class PartnerLocation extends Model
{
    const SCENARIO_LIVE = 'live';

    public $id;
    public $partnerId;
    public $title;
    public $addressLine1;
    public $addressLine2;
    public $city;
    public $state;
    public $zip;
    public $country;
    public $phone;
    public $email;
    public $dateCreated;
    public $dateUpdated;
    public $uid;

    /**
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = ['title', 'required'];

        $rules[] = [
            [
                'addressLine1',
                'city',
                'state',
                'zip',
                'country',
            ],
            'required',
            'on' => self::SCENARIO_LIVE,
        ];

        $rules[] = ['email', 'email'];

        return $rules;
    }
}
