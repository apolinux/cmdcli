<?php 

namespace Apolinux\LogReader ;

class Argument{

  private $name;

  private $value ;

  public function __construct($name)
  {
    $this->name = $name ;
  }

  public function setValue($value){
    $this->value = $value ;
  }

  public function getValue(){
    return $this->value ;
  }

  public function getName(){
    return $this->name ;
  }
}