# REST API client for the Booosta Framework

This modules provides a REST client for PHP Booosta to send REST requests to a server.

Booosta allows to develop PHP web applications quick. It is mainly designed for small web applications.
It does not provide a strict MVC distinction. Although the MVC concepts influence the framework. Templates,
data objects can be seen as the Vs and Ms of MVC.

Up to version 3 Booosta was available at Sourceforge: https://sourceforge.net/projects/booosta/ From version
4 on it resides on Github and is available from Packagist under booosta/booosta .

## Installation

This module can be used inside the Booosta framework. If you want to do so, install the framework first. See the
[installation instructions](https://github.com/buzanits/booosta-installer) for accomplishing this. If your
Booosta is installed, you can install this module.

You also can use this module in your standalone PHP scripts. In both cases you install it with:

```
composer require booosta/rest
```

## Usage in the Booosta framework

To use the REST functions, extend the `Application` object:

```
class MyREST extends \booosta\rest\Application
{
  protected $url = 'https://api.football-data.org/v4/';

  public function run()
  {
    // the following requests https://api.football-data.org/v4/competitions
    return $this->get('competitions');
  }
}
```

To use this class in the Booosta Framework do:

```
$rest = $this->makeInstance('MyREST');
print_r($rest->run());
```

## Usage as standalone Module:

```
$rest = new MyREST();
print_r($rest->run());
```

## Functions

```
// Submits a GET request to "$url$uri". If $raw is false, an array is returned, otherwise the JSON code
$this->get($uri, $raw = false);

// As get, but uses "$uri" instead of "$url$uri" ($url is the protected class variable)
$this->get_url($uri, $raw = false);

// Submits a POST request. $data has to be an array
$this->post($uri, $data, $raw = false);

// Submits a PUT request.
$this->put($uri, $data, $raw = false);

// Submits a DELETE request
$this->delete($uri);

// Sets optional username and password in the request. Call this before submitting a request.
$this->set_credentials($username, $password);
```

Inside your functions (in the above expamle `run()`) you can manipulate the REST clients behaviour:

```
// Sets request headers. Array with header name in the key and header value in the value.
$this->headers = $my_header_array;
// Example:
$this->headers = ['Content-Type' => 'application/json'];
```
