<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\db\Query;

/**
 * m180401_205201_address_migration migration.
 */
class m180401_205201_address_migration extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $countryChanges = [
            'The Netherlands' => 'Netherlands',
            'US' => 'United States',
            'USA' => 'United States',
            'Polska' => 'Poland',
            'Deutschland' => 'Germany',
        ];

        foreach ($countryChanges as $from => $to) {
            Craft::$app->getDb()->createCommand()->
            update('{{%content}}', ['field_businessCountry' => $to], ['field_businessCountry' => $from])->execute();
        }


        $rows = (new Query())->
        select([
            '[[users.id]] AS userId',
            '[[customers.id]] AS customerId',
            '[[users.firstName]] AS firstName',
            '[[users.lastName]] AS lastName',
            '[[content.field_businessName]] AS businessName',
            '[[content.field_businessAddressLine1]] AS address1',
            '[[content.field_businessAddressLine2]] AS address2',
            '[[content.field_businessCity]] AS city',
            '[[content.field_businessCountry]] AS countryName',
            '[[content.field_businessState]] AS stateName',
            '[[content.field_businessVatId]] AS vatID',
            '[[content.field_businessZipCode]] AS zipCode',
            '[[countries.id]] AS countryId',
            '[[states.id]] AS stateId'
        ])->
        from('{{%commerce_customers}} AS customers')->
        innerJoin('{{%users}} AS users', '[[users.id]] = [[customers.userId]]')->
        innerJoin('{{%content}} AS content', '[[users.id]] = [[content.elementId]]')->
        innerJoin('{{%commerce_countries}} AS countries', '[[countries.name]] = [[content.field_businessCountry]]')->
        leftJoin('{{%commerce_states}} AS states', '[[states.abbreviation]] = [[content.field_businessState]] AND [[states.countryId]] = [[countries.id]]')->
        where('[[userId]] IS NOT NULL')->
        all();

        echo "Loaded " . count($rows) . " addreses.\n";
        $counter = 0;
        foreach ($rows as $row) {
            $address = [
                'firstName' => $row['firstName'],
                'lastName' => $row['lastName'],
                'address1' => $row['address1'],
                'address2' => $row['address2'],
                'city' => $row['city'],
                'zipCode' => $row['zipCode'],
                'businessTaxId' => $row['vatID'],
                'businessTaxId' => $row['vatID'],
                'countryId' => $row['countryId'],
                'stateId' => $row['stateId'],
            ];

            Craft::$app->getDb()->createCommand()->insert('{{%commerce_addresses}}', $address)->execute();

            $id = (new Query())->select('MAX(id)')->from('{{%commerce_addresses}}')->scalar();
            Craft::$app->getDb()->createCommand()->update('{{%commerce_customers}}', ['primaryBillingAddressId' => $id], ['id' => $row['customerId']])->execute();
            echo "Processed " . ++$counter . "\n";
        }

        Craft::$app->getDb()->createCommand()->update('{{%commerce_addresses}}', ['address2' => null], ['address2' => 'null'])->execute();
        Craft::$app->getDb()->createCommand()->update('{{%commerce_addresses}}', ['businessTaxId' => null], ['businessTaxId' => 'null'])->execute();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180401_205201_address_migration cannot be reverted.\n";
        return false;
    }
}
