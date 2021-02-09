<?php

namespace Level51\AjaxSelectField;

use SilverStripe\Control\HTTP;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Forms\FormField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\View\Requirements;

/**
 * Ajax Select / Dropdown field.
 *
 * Allows to select a single value/entry using a custom api endpoint or callback function.
 *
 * Usage:
 * ```php
 * AjaxSelectField::create('MyField', 'AjaxSelectExample')
 * ->setSearchCallback(
 * function ($query, $request) {
 * $results = [];
 * foreach (SiteTree::get()->find('Title:PartialMatch', $query) as $page) {
 * $results[] = [ 'id' => $page->ID, 'title' => $page->Title ];
 * }
 *
 * return $results;
 * }
 * )
 * ```
 *
 * @package Level51\AjaxSelectField
 */
class AjaxSelectField extends FormField
{
    private static $allowed_actions = ['search'];

    /**
     * @var int Min amount of characters needed to execute the search callback
     */
    private int $minSearchChars = 3;

    /**
     * @var string|null Search endpoint to call
     */
    private ?string $searchEndpoint = null;

    /**
     * @var callable|null Callback function to call on search
     */
    private $searchCallback = null;

    /**
     * @var string|null Custom placeholder for the search field
     */
    private ?string $placeholder = null;

    /**
     * @var array|null Optional getVars which should be added to each search request
     */
    private ?array $getVars = null;

    /**
     * @var array|null Optional request headers sent with each search request
     */
    private ?array $searchHeaders = null;

    public function __construct($name, $title = null, $value = null)
    {
        parent::__construct($name, $title, $value);
    }

    public function Field($properties = array())
    {
        if (!$this->searchEndpoint && !$this->searchCallback) {
            throw new \Exception(_t(__CLASS__ . '.ERROR_SEARCH_CONFIG'));
        }

        Requirements::javascript('level51/silverstripe-ajax-select-field: client/dist/ajaxSelectField.js');
        Requirements::css('level51/silverstripe-ajax-select-field: client/dist/ajaxSelectField.css');

        return parent::Field($properties);
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
                'value'  => ($value = $this->Value()) ? json_decode($value, true) : null,
                'config' => [
                    'minSearchChars' => $this->minSearchChars,
                    'searchEndpoint' => $this->searchEndpoint ?: $this->Link('search'),
                    'placeholder'    => $this->placeholder ?: _t(__CLASS__ . '.SEARCH_PLACEHOLDER'),
                    'getVars'        => $this->getVars,
                    'headers'        => $this->searchHeaders
                ]
            ]
        );
    }

    /**
     * Endpoint for search requests, if no custom searchEndpoint is set.
     *
     * Executes the searchCallback function provided and responds with json payload.
     *
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
    public function search(HTTPRequest $request): HTTPResponse
    {
        $searchResults = ($this->searchCallback)($request->getVar('query'), $request);
        $response = HTTPResponse::create();
        $response->addHeader('Access-Control-Allow-Origin', '*');
        $response->addHeader('Content-Type', 'application/json');

        return $response->setBody(json_encode($searchResults));
    }

    /**
     * Set a custom endpoint for all search requests.
     *
     * Note: Use either the searchEndpoint OR the searchCallback!
     * If both is set, the searchEndpoint is prefered.
     *
     * @param string $endpoint
     * @return $this
     */
    public function setEndpoint($endpoint): AjaxSelectField
    {
        $this->searchEndpoint = $endpoint;

        return $this;
    }

    /**
     * Pass in a callback which should be executed on search requests.
     *
     * The callback has to return an array with results, each of them has to have at least a "id" and "title" property.
     *
     * Note: Use either the searchEndpoint OR the searchCallback!
     * If both is set, the searchEndpoint is prefered.
     *
     * @param callable $callback
     * @return $this
     * @throws \Exception
     */
    public function setSearchCallback($callback): AjaxSelectField
    {
        if ($callback && is_callable($callback)) {
            $this->searchCallback = $callback;
        } else {
            throw new \Exception(_t(__CLASS__ . '.ERROR_INVALID_CALLBACK'));
        }

        return $this;
    }

    /**
     * Define the min length of search terms needed to execute the search.
     *
     * @param int $chars
     * @return $this
     */
    public function setMinSearchChars($chars): AjaxSelectField
    {
        $this->minSearchChars = $chars;

        return $this;
    }

    /**
     * Set a custom placeholder.
     *
     * @param string $placeholder
     * @return $this
     */
    public function setPlaceholder($placeholder): AjaxSelectField
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    /**
     * Set a list of custom GET vars which should be added to each request.
     *
     * Have to be in format ["key" => "value"].
     *
     * @param array $vars
     * @return $this
     */
    public function setGetVars($vars): AjaxSelectField
    {
        $this->getVars = $vars;

        return $this;
    }

    /**
     * Set a list of custom request headers sent with each search request.
     *
     * Have to be in format ["key" => "value"].
     *
     * @param array $headers
     *
     * @return AjaxSelectField
     */
    public function setSearchHeaders($headers): AjaxSelectField
    {
        $this->searchHeaders = $headers;

        return $this;
    }
}
