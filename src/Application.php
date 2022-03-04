<?php
namespace booosta\rest;

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
