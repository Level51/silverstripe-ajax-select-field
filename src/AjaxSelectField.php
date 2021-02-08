<?php

namespace Level51\AjaxSelectField;

use SilverStripe\Control\HTTP;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Forms\FormField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\View\Requirements;

/**
 * Class AjaxSelectField
 * @package Level51\AjaxSelectField
 *
 * @todo cleanup
 * @todo localization
 * @todo documentation
 * @todo save handling
 */
class AjaxSelectField extends FormField
{
    private static $allowed_actions = ['search'];

    private int $minSearchChars = 3;

    private string $searchEndpoint = '';

    private $searchCallback = null;

    public function __construct($name, $title = null, $value = null)
    {
        parent::__construct($name, $title, $value);
    }

    public function Field($properties = array()): DBHTMLText
    {
        Requirements::javascript('level51/silverstripe-ajax-select-field: client/dist/ajaxSelectField.js');
        Requirements::css('level51/silverstripe-ajax-select-field: client/dist/ajaxSelectField.css');

        return parent::Field($properties);
    }

    public function getPayload(): string
    {
        return json_encode(
            [
                'id'     => $this->ID(),
                'name'   => $this->getName(),
                'value'  => $this->Value(),
                'config' => [
                    'minSearchChars' => $this->minSearchChars,
                    'searchEndpoint' => $this->searchEndpoint ?: $this->Link('search')
                ]
            ]
        );
    }

    public function search(HTTPRequest $request) {
        $searchResults = ($this->searchCallback)($request->getVar('query'), $request);
        $response = HTTPResponse::create();
        $response->addHeader('Access-Control-Allow-Origin', '*');
        $response->addHeader('Content-Type', 'application/json');

        return $response->setBody(json_encode($searchResults));
    }

    public function setEndpoint($endpoint)
    {
        $this->searchEndpoint = $endpoint;

        return $this;
    }

    public function setSearchCallback($callback) {
        if ($callback && is_callable($callback)) {
            $this->searchCallback = $callback;
        } else {
            throw new \Exception('Invalid callback function');
        }

        return $this;
    }

    public function setMinSearchChars($chars)
    {
        $this->minSearchChars = $chars;

        return $this;
    }
}
