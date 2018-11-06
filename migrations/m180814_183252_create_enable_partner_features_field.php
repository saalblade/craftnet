<?php

namespace craft\contentmigrations;

use Craft;
use craft\base\Field;
use craft\db\Migration;
use craft\elements\User;
use craft\fields\Lightswitch;
use craft\records\FieldLayoutField as FieldLayoutFieldRecord;

/**
 * m180814_183252_create_enable_partner_features_field migration.
 */
class m180814_183252_create_enable_partner_features_field extends Migration
{

    protected $fieldConfig;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->fieldConfig = [
            'type' => Lightswitch::class,
            'groupId' => 4, // Users
            'name' => 'Enable Partner Features',
            'handle' => 'enablePartnerFeatures',
            'instructions' => 'Enables partner fields in a user’s Craft ID account.',
            'translationMethod' => Field::TRANSLATION_METHOD_NONE,
            'translationKeyFormat' => null,
            'settings' => ['default' => null]
        ];
    }

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        try {
            $field = $this->saveField();
        }
        catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;
            return false;
        }

         $this->saveFieldToUsersLayout($field);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $id = $this->getExistingFieldId();

        if (!$id) {
            echo 'No field found to delete with handle: ' . $this->fieldConfig['handle'] . PHP_EOL;

            return true;
        }

        $fieldsService = Craft::$app->getFields();
        $field = $fieldsService->getFieldById($id);
        $success = $fieldsService->deleteField($field);

        if (!$success) {
            echo 'Unable to delete field with id: ' . $id . PHP_EOL;
        }

        return $success;
    }

    /**
     * @return false|null|string
     * @throws \yii\db\Exception
     */
    protected function getExistingFieldId()
    {
        $id = $this->db
            ->createCommand('SELECT id FROM fields WHERE handle=:handle')
            ->bindValue('handle', $this->fieldConfig['handle'])
            ->queryScalar();

        return $id;
    }

    /**
     * @return \craft\base\FieldInterface
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    protected function saveField()
    {
        // If the field exists, we'll just update it
        $fieldConfig = array_merge(
            $this->fieldConfig,
            ['id' => $this->getExistingFieldId() ?: null]
        );

        $fieldsService = Craft::$app->getFields();

        $field = $fieldsService->createField($fieldConfig);
        $result = $fieldsService->saveField($field);

        if (!$result) {
            throw new \Exception('Unable to save Lightswitch field');
        }

        return $field;
    }

    /**
     * Just saves a record to the pivot table.
     * @param \craft\base\FieldInterface $field
     * @return bool
     */
    protected function saveFieldToUsersLayout($field)
    {
        $fieldsService = Craft::$app->getFields();
        $layout = $fieldsService->getLayoutByType(User::class);

        if (in_array($field->id, $layout->getFieldIds())) {
            echo 'Field `' . $field->handle . '` already attached to Users Profile tab' . PHP_EOL;
            return true;
        }

        $tabs = $layout->getTabs();
        $sortOrder = count($layout->getFields()) + 1;

        $fieldRecord = new FieldLayoutFieldRecord();
        $fieldRecord->layoutId = $layout->id;
        $fieldRecord->tabId = $tabs[0]->id;
        $fieldRecord->fieldId = $field->id;
        $fieldRecord->required = false;
        $fieldRecord->sortOrder = $sortOrder;

        return $fieldRecord->save(false);
    }
}
