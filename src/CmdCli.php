<?php 

namespace Apolinux\CmdCli ;

/**
 * parse command line options, arguments and parameters of options
 * 
 * if parsed well, it shows no error. If there is an error in parsing,
 * it shows the help with error
 */
class CmdCli{
  const TYPE_SINGLE_OPT='SINGLE_OPT';

  private $opt_list=[];
  private $arg_list=[];
  private $help_text='';
  private $arg_counter ;
  private $exit_on_error ;
  private $command_name ;
  private $description ;

  public function __construct($description='', $exit_on_error=true, $command_name=null){
    $this->command_name = $command_name ;
    $this->exit_on_error = $exit_on_error;
    $this->description = $description ;
  }

  public function addHelp($text){
    $this->help_text = $text ;
    return $this ;
  }

  public function addOpt(
    string $name, 
    $type, 
    $description ,
    $short=null, 
    $long=null, 
    $require_parameter=false, 
    $is_optional=false
    ){
    $this->opt_list[$name]=new Option($name, $type, $description, $short,$long,$require_parameter, $is_optional);
    return $this ;
  }

  public function addArg($arg_name, $description=null, $optional=false){
    $this->arg_list[]=new Argument($arg_name, $description, $optional) ;
    return $this ;
  }

  public function parse($input=null){
    if(empty($input)){
      $input = $GLOBALS['argv'];
    }
    // remove script name
    $cmd_name_tmp = array_shift($input);

    $this->command_name = empty($this->command_name) ? $cmd_name_tmp : $this->command_name ;

    $last_arg=null;
    reset($this->arg_list);
    $this->arg_counter=0;
    try{
      foreach($input as $raw_arg){
        $option_temp = $this->parseArg($raw_arg, $last_arg);
        $last_arg=$option_temp;
      }
    
      $this->validateOptions();
    }catch(CmdCliException $e){
      //return $this->showMessage($e->getMessage(). PHP_EOL . $e->getTraceAsString());
      $out= $this->showMessage($e->getMessage());
      if($this->exit_on_error){
        fwrite(STDERR, $out);
        exit(1);
      }
      return $out ;
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
    $description = $this->command_name . 
      (!empty($this->description) ? '. ' .$this->description : '' ) ;
      
    $opt_list = $this->getOptList();
    $arg_list = $this->getArgList();
    $opt_list_v = $this->getOptListLong();
    $arg_list_v = $this->getArgListLong();
    $info=<<<END
$description    
Usage: $this->command_name $opt_list $arg_list
option list:
 $opt_list_v
 $arg_list_v
     -h | --help    Show this help
END;
    return $info . PHP_EOL . 
    ($this->help_text ? $this->help_text .PHP_EOL :'' ).
    (! empty($text) ? $text . PHP_EOL : '');
  }

  private function getOptList(){
    $a=array_map(function($obj){
        return $obj->helpSimple() ;
      },$this->opt_list 
    );
    return implode(' ',$a);
  }

  private function getArgList(){
    $a=array_map(function($obj){
        return $obj->getName() ;
      },$this->arg_list 
    );
    return implode(' ',$a);
  }

  private function getOptListLong(){
    $a=array_map(function($obj){
        return "\t".$obj->helpComplete() ;
      },$this->opt_list 
    );
    return implode("\n",$a);
  }

  private function getArgListLong(){
    $a=array_map(function($obj){
        return "\t".$obj->showHelp() ;
      },$this->arg_list 
    );
    return implode("\n",$a);
  }

  private function validateOptions(){
    foreach($this->opt_list as $option){
      if(! $option->isValid()){
        throw new CmdCliException(sprintf("Missing or incomplete option '%s'",$option->getName()));
      }
    }

    foreach($this->arg_list as $argument){
      if(! $argument->optional() && empty($argument->getValue())){
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
    if(in_array($option_name, ['h','help'])){
      throw new CmdCliException('');
    }
    foreach($this->opt_list as $option){
      if($option->IsNamed($option_name,$type)){
        return $option ;
      }
    }
    // @todo, do something with non existent options
  }

  public function getParsedOpts(){
    $out = [];
    foreach($this->opt_list as $opt_name => $option){
      if($option->isDefinedComplete()){
        $out[$opt_name] = $option->getValue() ; 
      }
    }
    return $out ;
  }

  /**
   * @todo convert to array with params
   */
  public function getParsedArgs(){
    $out=[];
    foreach($this->arg_list as $arg){
      if(! empty($arg->getValue())){
        $out[$arg->getName()] = $arg->getValue()  ;
      }
    }
    return $out ;
  }
}