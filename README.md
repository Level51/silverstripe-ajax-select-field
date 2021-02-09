# SilverStripe Ajax Select Field

This module adds a `AjaxSelectField` which allows to select a single result returned from an ajax endpoint.

## Result Fetching
Results can be fetched from a custom endpoint (using the `setEndpoint(ENDPOINT_URL)` method) or using a callback function passed in to the field using `setSearchCallback` (see example below).

The response has to be a JSON array with an object per result, where each result has to have at least an `id` and `title` field, so for example: 

```json
[
  {
    "id": 1,
    "title": "Home"
  },
  {
    "id": 2,
    "title": "Contact Us"
  }
]
```

## Storage
By default the whole result (with all it's properties) will be stored in the DB as JSON string, although there is also an "id only" mode.

So the DB value will look like `{ "id": "1", "title": "Home" }` with `idOnlyMode = false`.
With the mode activated only the id will be stored (as with the default DropdownField).

## Methods / Options
### setEndpoint(string)
Set a custom search endpoint by passing the whole url.

### setSearchCallback(callable)
Pass a callback function executed on each search request. Uses the `search` method defined in the field class.

### setMinSearchChars(int)
Change the amount of characters required for searches to be executed (defaults to 3).

### setPlaceholder(string)
Change the default placeholder text of the input field.

### setGetVars(array)
Set a list of custom GET vars which should be added to each request. Have to be in format ["key" => "value"].

### setSearchHeaders(array)
Set a list of custom request headers sent with each search request. Have to be in format ["key" => "value"].

### setIdOnlyMode(bool)
En-/disable the idOnlyMode.
If active the field will only store the "id" of the selected result. Otherwise the full result payload will be stored.

Note that the search endpoint or callback has to support requests with a ?id param returning only that one result if the mode is active.

## Installation
`composer require level51/silverstripe-ajax-select-field`

## Usage Example
```php
AjaxSelectField::create('MyField', 'My Field Label')
    ->setSearchCallback(
        function ($query, $request) {
            // This part is only required if the idOnlyMode is active
            if ($id = $request->getVar('id')) {
                $page = SiteTree::get()->byID($id);
                return [
                    'id' => $page->ID,
                    'title' => $page->Title
                ];
            }
 
            $results = [];
            foreach (SiteTree::get()->filter('Title:PartialMatch', $query) as $page) {
                $results[] = [ 'id' => $page->ID, 'title' => $page->Title ];
            }

            return $results;
    })
```


## Requirements
- SilverStripe ^4
- PHP >=7.1
- ext-json

## Maintainer
- Level51 <hallo@lvl51.de>
