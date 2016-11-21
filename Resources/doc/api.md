# API

This section describes the API that is provided by the bundle:

| Path              | Method  | Action
|-------------------|---------|------------
| _api/{id}         | GET     | retrieves a json representation of a translation
| _api/{id}         | PUT     | edits a specific translation with the data provided in the request content 
| _api/_all         | GET     | gets a json object with all translations 
| _api/tags/_all    | GET     | gets a json object with all tags 
| _api/export       | POST    | exports all the modified translation messages 
| _api/history/{id} | GET     | retrieves history about a specified translation 

Note here that if you want to edit a translation you have to provide a json 
request content with translation properties as keys and their values. Here is an
example:

```json

{
  "description": "my new description",
  "tags": ["tag1", "tag2", "tag3"],
  "messages": {
    "en": "message",
    "de": "Nachricht"
  }
}

```

> **Warning!** Even though it is possible to do it, we strongly discourage changing any other 
fields this way, unless you really know what you are doing.
 