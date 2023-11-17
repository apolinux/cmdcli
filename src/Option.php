<?php 

namespace Apolinux\CmdCli ;

/**
 * Define an Option 
 * 
 * option is a string that begins with '-' or '--' and contains numbers or words
 */
class Option {
  /**
   * @const string
   */
  const TYPE_LONG='LONG';

  /**
   * @const string
   */
  const TYPE_SHORT='SHORT';
  
  /**
   * @const string
   */
  const DATA_TYPE_BOOL = 'bool';

  /**
   * @const string
   */
  const DATA_TYPE_NOBOOL='nobool';

  /**
   * @var string name of option
   */
  private $name ;

    
  /**
   * short option without '-'
   *
   * @var string
   */
  private $short ;
  
  /**
   * long option without '--'
   *
   * @var string
   */
  private $long ;
    
  /**
   * data type
   *
   * @var string
   */
  private $data_type ;
  
  /**
   * true if parameter is required
   *
   * @var bool
   */
  private $require_param ;
  
  /**
   * value of parameter
   *
   * @var string
   */
  private $parameter ;
    
  /**
   * true if option is not mandatory
   *
   * @var bool
   */
  private $optional;
    
  /**
   * true if is defined after parsing option
   *
   * @var bool
   */
  private $defined = false;
    
  /**
   * description of option
   *
   * @var string
   */
  private $description ;

  /**
   * class constructor
   * 
   * @param string $name
   * @param string $data_type
   * @param string $short short option, 1 word
   * @param string $long long option
   * @param bool   $require_param true if option requires a parameter
   * @param bool   $is_optional true if option is optional
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
  
  /**
   * get name
   *
   * @return string
   */
  public function getName(){
    return $this->name ;
  }
  
  /**
   * return optional
   *
   * @return bool
   */
  public function isOptional(){
    return $this->optional ;
  }
  
  /**
   * return requireParam
   *
   * @return bool
   */
  public function requireParam(){
    return $this->require_param;
  }
  
  /**
   * set parameter
   *
   * @param  string $parameter
   * @return void
   */
  public function setParameter($parameter){
    $this->parameter = $parameter;
  }

  /**
   * define if is named or not and set defined
   * 
   * @param  string $name
   * @param  int    $size short or long
   * @return bool
   */
  public function isNamed($name, $size){
    $type_lower = strtolower($size);
    $is_named= ( $this->$type_lower == $name ) ;
    if($is_named){
      $this->defined = true ;
    }
    return $is_named;
  }
  
  /**
   * return if option is valid
   *
   * @return bool
   */
  public function isValid(){
    return ( $this->optional || $this->isDefinedComplete() );
  }
  
  /**
   * return if option is defined
   *
   * @return bool
   */
  public function isDefinedComplete(){
    return (
      $this->defined && (
        ($this->require_param && ! is_null($this->parameter)) 
        || 
        (! $this->require_param)
      )
    );
  }
  
  /**
   * return value of option
   * 
   * return bool if option not requires parameter
   * return string if option requires parameter or  null
   *
   * @return null|bool|string
   */
  public function getValue(){
    if($this->isDefinedComplete()){
      if($this->require_param){
        return $this->parameter ;
      }
      return true ;
    }
    return false ;
  }
  
  /**
   * returns description of short/long options
   *
   * @return string
   */
  public function helpSimple(){
    $out = $this->getShortLong();
    if($this->optional){
      $out="[$out]";
    }
    return $out ;
  }
  
  /**
   * return short information
   *
   * @return string
   */
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
  
  /**
   * return detailed information
   *
   * @return array
   */
  public function helpComplete(){
    return [
      $this->getShortLong() , 
      ($this->optional ? 'Optional. ':'') . 
      ($this->description ?? $this->name )
    ];
  }
}