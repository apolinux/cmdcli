<?php 

namespace Apolinux\CmdCli ;

/**
 * define argument of command line
 * 
 * Argument is a string in command line that is not an option,
 * it means that is not prefixed with '-' or '--' or is not a parameter of an option
 */
class Argument{
  
  /**
   * name of argument
   *
   * @var string
   */
  private $name;
  
  /**
   * value of argument
   *
   * @var string
   */
  private $value ;
  
  /**
   * description of argument
   *
   * @var string
   */
  private $description ;
  
  /**
   * true if argument is optional
   *
   * @var bool
   */
  private $optional ;
  
  /**
   * __construct
   *
   * @param  string $name
   * @param  string $description
   * @param  bool $optional
   */
  public function __construct($name, $description=null, $optional=false)
  {
    $this->name = $name ;
    $this->description = $description ;
    $this->optional = $optional ;
  }
  
  /**
   * set value
   *
   * @param  string $value
   * @return void
   */
  public function setValue($value){
    $this->value = $value ;
  }
  
  /**
   * get value
   *
   * @return string
   */
  public function getValue(){
    return $this->value ;
  }
  
  /**
   * get name
   *
   * @return string
   */
  public function getName(){
    return $this->name ;
  }
  
  /**
   * __toString
   *
   * @return string
   */
  public function __toString()
  {
    return "$this->value";
  }
  
  /**
   * returns optional
   *
   * @return bool
   */
  public function optional(){
    return $this->optional;
  }
  
  /**
   * show help
   *
   * @return array
   */
  public function showHelp(){
    return [
      $this->name, 
      ($this->optional ? 'Optional. ' : '') . $this->description 
    ];
  }
}