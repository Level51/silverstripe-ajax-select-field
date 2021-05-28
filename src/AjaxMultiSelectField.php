<?php

namespace Level51\AjaxSelectField;

use SilverStripe\Forms\FormField;
use SilverStripe\Security\Security;
use SilverStripe\View\Requirements;

class AjaxMultiSelectField extends FormField
{
    use AjaxSelectFieldTrait;

    private $displayFields = [];

    public function __construct($name, $title = null, $value = null)
    {
        parent::__construct($name, $title, $value);
    }

    public function Field($properties = array())
    {
        if (!$this->searchEndpoint && !$this->searchCallback) {
            throw new \Exception(_t(__CLASS__ . '.ERROR_SEARCH_CONFIG'));
        }

        Requirements::javascript('level51/silverstripe-ajax-select-field: client/dist/ajaxMultiSelectField.js');
        Requirements::css('level51/silverstripe-ajax-select-field: client/dist/ajaxMultiSelectField.css');

        return parent::Field($properties);
    }

    /**
     * Set the fields which should be shown for selected items.
     *
     * @param array $fields
     * @return $this
     */
    public function setDisplayFields($fields): AjaxMultiSelectField
    {
        $this->displayFields = $fields;

        return $this;
    }

    public function getDisplayFields(): array
    {
        if (!$this->displayFields) {
            return [
                'id'    => 'ID',
                'title' => 'Title'
            ];
        }

        return $this->displayFields;
    }

    /**
     * Get the payload/config passed to the vue component.
     *
     * @return string
     */
    public function getPayload(): string
    {
        return json_encode(
            [
                'id'     => $this->ID(),
                'name'   => $this->getName(),
                'value'  => $this->getValueForComponent(),
                'lang'   => substr(Security::getCurrentUser()->Locale, 0, 2),
                'config' => [
                    'minSearchChars' => $this->minSearchChars,
                    'searchEndpoint' => $this->searchEndpoint ?: $this->Link('search'),
                    'placeholder'    => $this->placeholder ?: _t(__CLASS__ . '.SEARCH_PLACEHOLDER'),
                    'getVars'        => $this->getVars,
                    'headers'        => $this->searchHeaders,
                    'displayFields'  => $this->getDisplayFields()
                ]
            ]
        );
    }

    /**
     * Get the current value prepared for the vue component.
     *
     * @return array|null
     */
    private function getValueForComponent(): ?array
    {
        if ($value = $this->Value()) {
            return json_decode($value, true);
        }

        return null;
    }
}
