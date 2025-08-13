<?php
namespace Controllers;
use Core\{Request, Response};

abstract class BaseController {
  protected Request $req; protected Response $res;
  public function __construct(Request $req, Response $res){ $this->req=$req; $this->res=$res; }
}
