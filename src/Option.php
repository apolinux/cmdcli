<?php 

namespace Apolinux\CmdCli ;

class Option {
  const TYPE_LONG='LONG';
  const TYPE_SHORT='SHORT';
  //const TYPE_ARG='ARG';

  const DATA_TYPE_BOOL = 'bool';
  const DATA_TYPE_NOBOOL='nobool';

  private $name ;
  private $short ;
  private $long ;
  private $data_type ;
  private $require_param ;
  //private $value ; // @todo could be array?
  private $parameter ;
  private $optional;
  private $defined = false;
  private $description ;

  /**
   * class constructor
   * 
   * @param string $name
   * @param string $data_type
   * @param string $short short option, 1 word
   * @param string $long long option
   * @param bool $require_param true if option requires a parameter
   * @param bool $is_optional true if option is optional
   */
  public function __construct(
    string $name, 
    $data_type, 
    string $description = null ,
    $short=null,
    $long=null, 
    bool $require_param=false, 
    bool $is_optional=false
    ){
    $this->name = $name ;
    $this->short = $short ;
    $this->long  = $long;
    $this->data_type  = $data_type ;
    $this->require_param = $require_param ;
    $this->optional = $is_optional ;
    $this->description = $description ;
  }

  public function getName(){
    return $this->name ;
  }

  public function isOptional(){
    return $this->optional ;
  }

  public function requireParam(){
    return $this->require_param;
  }

  public function setParameter($parameter){
    $this->parameter = $parameter;
  }

  /**
   * @param string $name
   * @param int $size short or long
   */
  public function isNamed($name, $size){
    $type_lower = strtolower($size);
    $is_named= ( $this->$type_lower == $name ) ;
    if($is_named){
      $this->defined = true ;
    }
    return $is_named;
  }

  public function isValid(){
    return ( $this->optional || $this->isDefinedComplete() );
  }

  public function isDefinedComplete(){
    return (
      $this->defined && (
        ($this->require_param && ! is_null($this->parameter)) 
        || 
        (! $this->require_param)
      )
    );
  }

  public function getValue(){
    if($this->isDefinedComplete()){
      if($this->require_param){
        return $this->parameter ;
      }
      return true ;
    }
    return false ;
  }

  public function helpSimple(){
    $out = $this->getShortLong();
    if($this->optional){
      $out="[$out]";
    }
    return $out ;
  }

  private function getShortLong(){
    $out=[];
    if($this->short){
      $out[]= '-'. $this->short ;
    }
    if($this->long){
      $out[] = '--' . $this->long;
    }

    return sprintf("%s", implode(' | ',$out));
  }

  public function helpComplete(){
    return $this->getShortLong() ."\t" . 
    ($this->optional ? 'Optional. ':'') . 
    ($this->description ?? $this->name );
  }
}