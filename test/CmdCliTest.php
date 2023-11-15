<?php

use Apolinux\LogReader\CmdCli;
use PHPUnit\Framework\TestCase;

class CmdCliTest extends TestCase{
  public function testOk(){
    $cmd_cli = new CmdCli;
    $cmd_cli->addHelp('this is help');
    $cmd_cli->addOpt(
      'xml', 
      CmdCli::TYPE_SINGLE_OPT, 
      'x',
      'xml', 
      $require_parameter=false, 
      $optional=false
    );
    $cmd_cli->addOpt(
      'json',
      CmdCli::TYPE_SINGLE_OPT, 
      'j',
      'json',
      $require_parameter=false ,
      $optional=true
      ) ;
      
    $cmd_cli->addArg('filename',$optional=true);

    $input = ['stub','a b','-h','-c', 3 ,4 ,'--xml'];
    $msg = $cmd_cli->parse($input);
    $this->assertEmpty($msg);    
    $opts = $cmd_cli->getParsedOpts();
    $args = $cmd_cli->getParsedArgs();
    print_r($opts);print_r($args);
    $filename = $args['filename'];
    $parse_xml = $opts['xml'];
    $parse_json = $opts['json'];

    echo "parse result: $filename, $parse_xml, $parse_json". PHP_EOL;
    $this->assertTrue(false);
  }
}