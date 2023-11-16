<?php 

namespace Apolinux\CmdCli ;

class Argument{

  private $name;

  private $value ;

  private $description ;

  private $optional ;

  public function __construct($name, $description=null, $optional=false)
  {
    $this->name = $name ;
    $this->description = $description ;
    $this->optional = $optional ;
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

  public function __toString()
  {
    return "$this->value";
  }

  public function optional(){
    return $this->optional;
  }

  public function showHelp(){
    return "$this->name\t" . ($this->optional ? 'Optional. ' : '') . $this->description ;
  }
}