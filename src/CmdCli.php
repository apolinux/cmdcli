<?php 

namespace Apolinux\LogReader;

class CmdCli{
  const TYPE_SINGLE_OPT='SINGLE_OPT';

  private $opt_list=[];
  private $arg_list=[];
  private $help_text='';
  //private $optarg_list=[];
  private $arg_counter ;

  public function addHelp($text){
    $this->help_text = $text ;
  }

  public function addOpt($name, $type, $short=null,$long=null, $require_parameter=false, $is_optional=false){
    $this->opt_list[]=new Option($name, $type, $short,$long,$require_parameter, $is_optional);
  }

  public function addArg($arg_name){
    $this->arg_list[]=new Argument($arg_name) ;
  }

  public function parse($input=null){
    if(empty($input)){
      $input = $GLOBALS['argv'];
    }
    // remove script name
    array_shift($input);

    $args=[];
    $options=[];
    $last_arg=null;
    reset($this->arg_list);
    $this->arg_counter=0;
    foreach($input as $raw_arg){
      $option_temp = $this->parseArg($raw_arg, $last_arg);
      $last_arg=$option_temp;
    }
    // validate
    try{
      $this->validateOptions();
    }catch(CmdCliException $e){
      return $this->showMessage($e->getMessage(). PHP_EOL . $e->getTraceAsString());
    }
  }

  private function parseArg($arg, Option $last=null){
    if(substr($arg,0,2) === '--' ){
      $option = substr($arg,2);
      $type=Option::TYPE_LONG;
      return $this->findOption($option, $type);
    }elseif(substr($arg,0,1) == '-'){
      $option = substr($arg,1);
      $type=Option::TYPE_SHORT ;
      return $this->findOption($option, $type);
    }elseif($last != null && $last->requireParam()){
      $last->setParameter($arg) ;
    }else{
      // add to arg list if is required
      //$option= $arg;
      //$type = Option::TYPE_ARG ;
      $this->addToArgList($arg);
    }

    
  }

  private function showMessage($text=''){
    echo $this->help_text . PHP_EOL . (! empty($text)? $text . PHP_EOL : '');
  }

  private function validateOptions(){
    foreach($this->opt_list as $option){
      if(! $option->isValid()){
        throw new CmdCliException(sprintf("Missing or incomplete option '%s'",$option->getName()));
      }
    }

    foreach($this->arg_list as $argument){
      if(empty($argument->getValue())){
        throw new CmdCliException(sprintf("Missing argument '%s'",$argument->getName()));
      }
    }
  }

  private function addToArgList($arg_value){
    if(isset($this->arg_list[$this->arg_counter])){
      $argument = $this->arg_list[$this->arg_counter];
      $argument->setValue($arg_value) ;
    }// @todo optional, do something with non existent arguments
    $this->arg_counter++;
  }

  /**
   * @return Option 
   */
  private function findOption($option_name, $type){
    foreach($this->opt_list as $option){
      if($option->IsNamed($option_name,$type)){
        return $option ;
      }
    }
    // @todo, do something with non existent options
  }

  public function getParsedOpts(){
    return $this->opt_list;
  }

  public function getParsedArgs(){
    return $this->arg_list;
  }
}