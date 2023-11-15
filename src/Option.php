<?php 

namespace Apolinux\LogReader ;

class Option {
  const TYPE_LONG='LONG';
  const TYPE_SHORT='SHORT';
  const TYPE_ARG='ARG';

  private $name ;
  private $short ;
  private $long ;
  private $data_type ;
  private $require_param ;
  //private $value ; // @todo could be array?
  private $parameter ;
  private $optional;
  private $defined = false;

  public function __construct($name, $data_type,$short=null,$long=null, $require_param=false, $is_optional=false){
    $this->name = $name ;
    $this->short = $short ;
    $this->long  = $long;
    $this->data_type  = $data_type ;
    $this->require_param = $require_param ;
    $this->optional = $is_optional ;
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
    
    return (
      $this->optional 
      || 
      (
        $this->defined && (
          ($this->require_param && ! is_null($this->parameter)) 
          || 
          (! $this->require_param)
        )
      )
    );
  }
}