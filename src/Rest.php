<?php
namespace booosta\rest;

use \booosta\Framework as b;
b::init_module('rest');

class Rest extends \booosta\base\Module
{ 
  use moduletrait_rest;

  protected $url, $data, $method;
  protected $username, $password;
  protected $headers;
  protected $error;

  public function __construct($url = null, $data = null, $method = 'GET')
  {
    parent::__construct();

    if($this->disabled) return;

    $this->url = $url;
    $this->data = $data;
    $this->method = $method;
  }

  public function __invoke($url = null, $data = null, $method = null)
  {
    if($this->disabled) return true;

    if($url === null) $url = $this->url;
    if($data === null) $data = $this->data;
    if($method === null) $method = $this->method;
    #\booosta\debug($data);

    $curl = curl_init();

    switch ($method):
    case 'POST':
      curl_setopt($curl, CURLOPT_POST, 1);
      if($data) curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    break;
    case 'PUT':
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
      if($data) curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    break;
    case 'DELETE':
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
    break;
    default:
      if($data) $url = sprintf('%s?%s', $url, http_build_query($data));
    endswitch;

    // Optional Authentication:
    if($this->username):
      curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
      curl_setopt($curl, CURLOPT_USERPWD, "$this->username:$this->password");
      curl_setopt($curl, CURLOPT_HEADER, 0);
    endif;

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_VERBOSE, 0);

    if($this->headers):
      $headers = [];
      foreach($this->headers as $header_var=>$header_val) $headers[] = "$header_var: $header_val";
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    endif;
    #\booosta\debug($this->headers); \booosta\debug($headers);

    #\booosta\debug(curl_getinfo($curl, CURLOPT_HTTPHEADER));
    $result = curl_exec($curl);
    #\booosta\debug($result);
    #\booosta\debug(curl_getinfo($curl));
    $statuscode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if($statuscode != '200') $this->error = "statuscode $statuscode: $result";

    curl_close($curl);

    // Hook after_call
    $this->after_call($result);

    #\booosta\debug($result);
    return $result;
  }

  protected function after_call(&$result) {}
  public function get_error() { return $this->error; }
  public function set_headers($headers) { $this->headers = $headers; }

  public function set_credentials($username, $password)
  {
    $this->username = $username;
    $this->password = $password;
  }
}


class Application extends \booosta\base\Base
{
  protected $url;
  protected $headers;
  protected $username, $password;
  public $error;

  public function __construct($url = null)
  {
    parent::__construct();
    if($url) $this->url = $url;
  }
  
  protected function post($uri, $data, $rawdata = false)
  {
    $headers = $this->headers;
    $encdata = $this->prepare_data($data, $rawdata);

    #\booosta\debug("$this->url$uri"); \booosta\debug($encdata);
    $rest = $this->makeInstance('\\booosta\\rest\\rest', "$this->url$uri", $encdata, 'POST');
    $rest->set_headers($headers);
    if($this->username) $rest->set_credentials($this->username, $this->password);
    $result = $rest();
    #debug($result);

    if($this->error = $rest->get_error()) return "ERROR in post to '$this->url$uri': $this->error";
    return json_decode($result, true);
  }

  protected function put($uri, $data, $rawdata = false)
  {
    $headers = $this->headers;
    $encdata = $this->prepare_data($data, $rawdata);

    #\booosta\debug(json_encode($data));
    $rest = $this->makeInstance('\\booosta\\rest\\rest', "$this->url$uri", $encdata, 'PUT');
    $rest->set_headers($headers);
    if($this->username) $rest->set_credentials($this->username, $this->password);
    $result = $rest();

    if($this->error = $rest->get_error()) return "ERROR in put to '$this->url$uri': $this->error";
    return json_decode($result, true);
  }

  protected function get($uri, $rawdata = false)
  {
    return $this->get_url("$this->url$uri", $rawdata);
  }

  protected function get_url($url, $rawdata = false)
  {
    $headers = $this->headers;

    $rest = $this->makeInstance('\\booosta\\rest\\rest', $url, null, 'GET');
    $rest->set_headers($headers);
    if($this->username) $rest->set_credentials($this->username, $this->password);
    $result = $rest();

    if($this->error = $rest->get_error()) return "ERROR in get '$url': $this->error";
    return $rawdata ? $result : json_decode($result, true);
  }

  protected function delete($uri)
  {
    $headers = $this->headers;

    $rest = $this->makeInstance('\\booosta\\rest\\rest', "$this->url$uri", null, 'DELETE');
    $rest->set_headers($headers);
    if($this->username) $rest->set_credentials($this->username, $this->password);
    $result = $rest();

    if($this->error = $rest->get_error()) return "ERROR in delete '$this->url$uri': $this->error";
    return json_decode($result, true);
  }
  
  public function set_credentials($username, $password)
  {
    #\booosta\debug("in Application set_credentials: $username, $password");
    $this->username = $username;
    $this->password = $password;
  }

  protected function prepare_data($data, $mode = false)
  {
    if($mode === false || strtolower($mode) == 'json') return json_encode($data);
    if(is_array($data) && substr(strtolower($mode), 0, 8) == 'www-form') return http_build_query($data);
    return $data;
  }
}
