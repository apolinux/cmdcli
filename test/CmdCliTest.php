<?php

use Apolinux\CmdCli\CmdCli;
use PHPUnit\Framework\TestCase;

class CmdCliTest extends TestCase{
  
  public function testOneOption(){
    $cmd_cli = new CmdCli('A command name', false);
    $cmd_cli->addOpt(
      'xml', 
      CmdCli::TYPE_SINGLE_OPT, 
      'desc',
      'x',
      null, 
      false       
    );

    $input = ['cmdname','a','-x','sign'];
    $msg = $cmd_cli->parse($input);
    $this->assertNull($msg,(string)$msg);    
    $opts = $cmd_cli->getParsedOpts();
    $this->assertIsArray($opts);
    
    $this->assertTrue($opts['xml']);
  }

  public function testOneOptionNotDefined(){
    $cmd_cli = new CmdCli('A command name', false);
    $cmd_cli->addOpt(
      'xml', 
      CmdCli::TYPE_SINGLE_OPT, 
      'desc',
      'x',
      null, 
      false       
    );

    $input = ['cmdname','a','-xy','sign'];
    $msg = $cmd_cli->parse($input);
    $this->assertNotNull($msg,(string)$msg);    
    $opts = $cmd_cli->getParsedOpts();
    $this->assertIsArray($opts);
    
    $this->assertArrayNotHasKey('xml',$opts);
  }

  public function testOptionOptional(){
    $cmd_cli = new CmdCli('A command name', false);
    $cmd_cli->addOpt(
      'xml', 
      CmdCli::TYPE_SINGLE_OPT, 
      'desc',
      'x',
      null, 
      false,
      true      
    );

    $input = ['cmdname','a','sign'];
    $msg = $cmd_cli->parse($input);
    $this->assertNull($msg,(string)$msg);    
    $opts = $cmd_cli->getParsedOpts();
    $this->assertIsArray($opts);
    $this->assertArrayNotHasKey('xml',$opts);
  }

  public function testHelp(){
    $cmd_cli = new CmdCli('A command name', false);
    $cmd_cli->addOpt(
      'xml', 
      CmdCli::TYPE_SINGLE_OPT, 
      'define if input is xml',
      'x',
      null, 
      false,
      true      
    )->addOpt('game',null,'set game options','g','game')
    ->addOpt('live',null,'set live game','l',null,true,true)
    ->addArg('date');

    $input = ['cmdname','-h','aaa',' test ','--xxx'];
    $msg = $cmd_cli->parse($input);

    $this->assertNotNull($msg,(string)$msg);    
    $this->assertStringContainsString('Show this help',$msg);
    $this->assertStringContainsString('set game options',$msg);
    $this->assertStringContainsString('set live game',$msg);
    $this->assertStringContainsString('define if input is xml',$msg);
    $this->assertStringContainsString('A command name',$msg);
  }

  public function testOneArgument(){
    $cmd_cli = new CmdCli('A command name', false);
    $cmd_cli->addArg(
      'filename', 
      'filename to read' ,
      $optional=false
    );

    $input = ['cmdname','a','b'];
    $msg = $cmd_cli->parse($input);
    $this->assertNull($msg,(string)$msg);    
    $args = $cmd_cli->getParsedArgs();
    $this->assertIsArray($args);
    $this->assertCount(1,$args);
    $this->assertEquals('a',$args['filename']);
  }

  public function testTwoArgumentOneOptionalNonExistent(){
    $cmd_cli = new CmdCli('A command name', false);
    $cmd_cli->addArg(
      'filename', 
      'filename to read' ,
      $optional=false
    )->addArg(
      'logname', 
      'just other argument' ,
      $optional=true
    );

    $input = ['cmdname','a' ,'-x'];
    $msg = $cmd_cli->parse($input);
    $this->assertNull($msg,(string)$msg);    
    $args = $cmd_cli->getParsedArgs();
    $this->assertIsArray($args);
    $this->assertCount(1,$args);
    $this->assertEquals('a',$args['filename']);
  }

  public function testTwoArgumentOneOptionalExistent(){
    $cmd_cli = new CmdCli('A command name', false);
    $cmd_cli->addArg(
      'filename', 
      'filename to read' ,
      $optional=false
    )->addArg(
      'logname', 
      'just other argument' ,
      $optional=true
    );

    $input = ['cmdname','a' ,'-x','b'];
    $msg = $cmd_cli->parse($input);
    $this->assertNull($msg,(string)$msg);    
    $args = $cmd_cli->getParsedArgs();
    $this->assertIsArray($args);
    $this->assertCount(2,$args);
    $this->assertEquals('a',$args['filename']);
    $this->assertEquals('b',$args['logname']);
  }

  public function testManyArgument(){
    $cmd_cli = new CmdCli('A command name', false) ;
    $cmd_cli->addArg(
      'filename', 
      'filename to read' ,
      $optional=false
    )
    ->addArg(
      'logname', 
      'just other argument' ,
      $optional=false
    );

    $input = ['cmdname','a','b'];
    $msg = $cmd_cli->parse($input);
    $this->assertNull($msg,(string)$msg);    
    $args = $cmd_cli->getParsedArgs();
    $this->assertIsArray($args);
    $this->assertCount(2,$args);
    $this->assertEquals('a',$args['filename']);
    $this->assertEquals('b',$args['logname']);
  }

  function testOptionWithParameterExistent(){
    $cmd_cli = new CmdCli('A command name', false);
    $cmd_cli->addOpt(
      'xml', 
      CmdCli::TYPE_SINGLE_OPT, 
      'desc',
      'x',
      null, 
      $require_parameter=true       
    );

    $input = ['cmdname','a','-x','xml_param'];
    $msg = $cmd_cli->parse($input);
    $this->assertNull($msg,(string)$msg);    
    $opts = $cmd_cli->getParsedOpts();
    $this->assertIsArray($opts);
    $this->assertCount(1, $opts);
    $this->assertEquals('xml_param',$opts['xml']);
  }

  function testOptionWithParameterExistentButMissing(){
    $cmd_cli = new CmdCli('A command name', false);
    $cmd_cli->addOpt(
      'xml', 
      CmdCli::TYPE_SINGLE_OPT, 
      'desc',
      'x',
      null, 
      $require_parameter=true       
    );

    $input = ['cmdname','a','-x'];
    $msg = $cmd_cli->parse($input);
    
    $this->assertStringContainsString('Missing or incomplete',(string)$msg);    
    $opts = $cmd_cli->getParsedOpts();
    $this->assertIsArray($opts);
    $this->assertCount(0, $opts);
    $this->assertArrayNotHasKey('xml_param',$opts);
  }

  function testOptionOptionalNoDefined(){
      $cmd_cli = new CmdCli('A command name', false);
      $cmd_cli->addOpt(
        'xml', 
        CmdCli::TYPE_SINGLE_OPT, 
        'desc',
        'x',
        null, 
        false,
        $is_optional=true       
      );
  
      $input = ['cmdname','a','-xy','sign'];
      $msg = $cmd_cli->parse($input);
      $this->assertNull($msg,(string)$msg);    
      $opts = $cmd_cli->getParsedOpts();
      $this->assertIsArray($opts);
      $this->assertCount(0,$opts);
      $this->assertArrayNotHasKey('xml',$opts);
    }

  function testArgumentNotDefined(){
    $cmd_cli = new CmdCli('A command name', false);
    $cmd_cli->addArg(
      'filename', 
      'filename to read' ,
      $optional=false
    );

    $input = ['cmdname','-x','--a'];
    $msg = $cmd_cli->parse($input);
    $this->assertNotNull($msg,(string)$msg);    
    $args = $cmd_cli->getParsedArgs();
    $this->assertIsArray($args);
    $this->assertCount(0,$args);
  }

  function testExitOnError(){
    $cmd = '/bin/env php '. __DIR__ .'/testcmd.php 2>&1' ;
    exec($cmd,$out,$result_code);
    $this->assertEquals(1, $result_code, join(' ', $out));
    $this->assertStringContainsString("Missing or incomplete option 'xml'",join(' ',$out));
  }

  public function testMultiple(){
    $cmd_cli = new CmdCli('A command name', false);
    $cmd_cli->addHelp('this is help');
    $cmd_cli->addOpt(
      'xml', 
      CmdCli::TYPE_SINGLE_OPT, 
      'desc',
      'x',
      'xml', 
      $require_parameter=false, 
      $optional=false
    );
    $cmd_cli->addOpt(
      'json',
      CmdCli::TYPE_SINGLE_OPT, 
      'desc',
      'j',
      'json',
      $require_parameter=false ,
      $optional=true
      ) ;
      
    $cmd_cli->addArg('filename',$optional=true);

    $input = ['stub','a b','-c', 3 ,4 ,'--xml'];
    $msg = $cmd_cli->parse($input);
    $this->assertNull($msg,(string)$msg);    
    $opts = $cmd_cli->getParsedOpts();
    $this->assertIsArray($opts);
    $args = $cmd_cli->getParsedArgs();
    $this->assertIsArray($args);
    
    $this->assertEquals('a b',(string)($args['filename']));
    $this->assertTrue($opts['xml']);
    $this->assertArrayNotHasKey('json',$opts);
  }
}