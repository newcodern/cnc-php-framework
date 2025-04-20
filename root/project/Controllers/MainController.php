<?php
namespace Controllers;


class ControllerMain extends Controller
{
public function index(){
    Controller::renderView('index');
}

}
