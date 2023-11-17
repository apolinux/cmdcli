<?php 

namespace Apolinux\CmdCli ;

/**
 * parse command line options, arguments and parameters of options
 * 
 * if parsed well, it shows no error. If there is an error in parsing,
 * it shows the help with error
 */
class CmdCli{
  /**
   * @const describe single option
   */
  const TYPE_SINGLE_OPT='SINGLE_OPT';

  /**
   * @var Option[] list of options
   */
  private $opt_list=[];

  /**
   * @var Argument[] list of arguments
   */
  private $arg_list=[];

  /**
   * @var string additional help text
   */
  private $help_text='';

  /**
   * @var int argument counter
   */
  private $arg_counter ;

  /**
   * @var bool true if exits when there is an error
   */
  private $exit_on_error ;

  /**
   * @var string command name
   */
  private $command_name ;

  /**
   * @var string description of command
   */
  private $description ;

  private $help_options = ['-h | --help', 'Shows this help'] ;

  /**
   * class constructor
   *
   * @param  string $description
   * @param  bool $exit_on_error
   * @param  string $command_name
   */
  public function __construct($description='', $exit_on_error=true, $command_name=null){
    $this->command_name = $command_name ;
    $this->exit_on_error = $exit_on_error;
    $this->description = $description ;
  }
  
  /**
   * adds additonal help text
   *
   * @param  string $text
   * @return CmdCli 
   */
  public function addHelp($text){
    $this->help_text = $text ;
    return $this ;
  }
  
  /**
   * add an option
   *
   * option is defined beginning with '-' or '--' characters, like '-x' or '--time'
   * option might require a parameter like '--amount 3123.55'
   * 
   * @param string $name option name
   * @param string $type not used 
   * @param string $description description of option used in help
   * @param string $short one word option required with prefix '-'
   * @param string $long any words option required with prefix '--'
   * @param bool $require_parameter true if requires an aditional parameter
   * @param bool $is_optional true if option is not mandatory
   * @return CmdCli
   */
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

    
  /**
   * Adds and argument
   * 
   * argument is an indepent string in command line that is not prefixed with '-' or '--'
   *
   * @param  string $arg_name argument name
   * @param  string $description description of argument used in help
   * @param  bool   $optional true if argument is optional
   * @return CmdCli
   */
  public function addArg($arg_name, $description=null, $optional=false){
    $this->arg_list[]=new Argument($arg_name, $description, $optional) ;
    return $this ;
  }
  
  /**
   * parse arguments from input
   *
   * reads input and validate each string against options and arguments defined previously
   * 
   * @param  array $input input to be parsed
   * @return string
   */
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
  
  /**
   * parse and argument/option
   *
   * @param  string $arg raw argument, can be a possible argument, option or option parameter
   * @param  Option|null $last last possible option parsed
   * @return Option|null
   */
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
      $this->setArgumentValue($arg);
    }
  }
  
  /**
   * Set argument value if is valid
   *
   * It requires that arg_counter is reset before outer loop in ::parse method
   * 
   * @param  string $arg_value value of argument
   * @return void
   */
  private function setArgumentValue($arg_value){
    if(isset($this->arg_list[$this->arg_counter])){
      $argument = $this->arg_list[$this->arg_counter];
      $argument->setValue($arg_value) ;
    }
    // @todo optional, do something with non existent arguments
    $this->arg_counter++;
  }

  /**
   * find if there is a option defined and return it
   * 
   * @param  string $option_name 
   * @param  string $type 'short' or 'long'
   * @throws CmdCliException
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

  /**
   * Shows message
   * 
   * shows the help including additional messages
   *
   * @param  string $text
   * @return string
   */
  private function showMessage($text=''){
    $description = $this->description ;
      
    $opt_list = $this->getOptList();
    $arg_list = $this->getArgList();
    $help_list = $this->getHelpList();
    $usage=$this->listUsage($this->command_name, $opt_list, $help_list, $arg_list);
    $opt_list_v = $this->getListLong();
    
    $info=<<<END
$description    
Usage: $usage
option list:
$opt_list_v
END;
    return $info . PHP_EOL . 
    ($this->help_text ? $this->help_text .PHP_EOL :'' ).
    (! empty($text) ? $text . PHP_EOL : '');
  }
  
  private function listUsage(...$args){
    return (
      trim(join(' ',$args)
        ) 
    );
  }

  /**
   * get option list as string
   * 
   * used in first line help
   *
   * @return string
   */
  private function getOptList(){
    $a=array_map(function($obj){
        return $obj->helpSimple() ;
      },$this->opt_list 
    );
    return implode(' ',$a);
  }
  
  /**
   * get argument list
   *
   * used in detailed help
   * 
   * @return string
   */
  private function getArgList(){
    $a=array_map(function($obj){
        return $obj->helpSimple() ;
      },$this->arg_list 
    );
    return implode(' ',$a);
  }
  
  /**
   * get detailed option list 
   *
   * @return array
   */
  private function getOptListLong(){
    $a=array_map(
      function($obj){
        return $obj->helpComplete() ;
      },
      $this->opt_list 
    );
    return $a ;
  }
  
  /**
   * get detailed argument list
   *
   * @return array
   */
  private function getArgListLong(){
    $a=array_map(
      function($obj){
        return $obj->showHelp() ;
      },
      $this->arg_list 
    );
    return $a ;
  }
  
  /**
   * get help options 
   * 
   * @return array
   */
  private function getHelpOptions($add_optional=false){
    $options = $this->help_options ;
    if($add_optional){
      $options[1] ="Optional. ". $options[1];
    }
    return $options ;
  }

  /**
   * set help options
   * @param string $short
   * @param string $long
   * @param string $description
   */
  public function setHelpOptions($short,$long,$description){
    $this->help_options = ["-$short | --$long", $description] ;
  }

  private function getHelpList(){
    return sprintf("[ %s ]",$this->help_options[0]) ;
  }

  /**
   * shows the detailed help of options
   * calculates max width of option list
   */
  private function getListLong(){
    $opts=array_merge(
      $this->getOptListLong(),
      [$this->getHelpOptions(true)],
      $this->getArgListLong()
    );

    // get max size of first column
    $max_length = array_reduce(
      array_column($opts,0),
      function($last,$current){
        return max($last, strlen($current));
      },
      0
    );

    $out = array_reduce(
      $opts,
      function($last,$current) use($max_length){
        return ($last=='' ? '': $last. PHP_EOL) . 
        sprintf("  %-{$max_length}s  %s",$current[0], $current[1]) ;
      },
      ''
    );
    return $out ;
  }

  /**
   * validate options and arguments
   *
   * checks each option and argument defined with the input 
   * and throws and exception if there is an error
   * 
   * @throws CmdCliException
   * @return void
   */
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
  
  
  
  /**
   * get parsed option list
   *
   * get a list of options that exists in input
   * 
   * @return Option[]
   */
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
   * get parsed arguments
   * 
   * get a list of arguments that exists in input
   * 
   * @todo convert to array with params
   * @return Argument[]
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