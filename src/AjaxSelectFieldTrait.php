<?php


namespace Level51\AjaxSelectField;


use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;

trait AjaxSelectFieldTrait
{
    private static $allowed_actions = ['search'];

    /**
     * @var int Min amount of characters needed to execute the search callback
     */
    private $minSearchChars = 3;

    /**
     * @var string|null Search endpoint to call
     */
    private $searchEndpoint = null;

    /**
     * @var callable|null Callback function to call on search
     */
    private $searchCallback = null;

    /**
     * @var string|null Custom placeholder for the search field
     */
    private $placeholder = null;

    /**
     * @var array|null Optional getVars which should be added to each search request
     */
    private $getVars = null;

    /**
     * @var array|null Optional request headers sent with each search request
     */
    private $searchHeaders = null;

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
    public function setEndpoint($endpoint)
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
    public function setSearchCallback($callback)
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
    public function setMinSearchChars($chars)
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
    public function setPlaceholder($placeholder)
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
    public function setGetVars($vars)
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
    public function setSearchHeaders($headers)
    {
        $this->searchHeaders = $headers;

        return $this;
    }
}
