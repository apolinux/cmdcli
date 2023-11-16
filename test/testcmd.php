<?php
/**
 * script used in tests 
 * to validate exit code and text
 */
use Apolinux\CmdCli\CmdCli;

require_once __DIR__ . '/../vendor/autoload.php';

$cmd_cli = new CmdCli('A command name');
$cmd_cli->addOpt(
  'xml', 
  CmdCli::TYPE_SINGLE_OPT, 
  'desc',
  'x',
  null, 
  false       
);

$input = ['cmdname','a','y','sign'];
$cmd_cli->parse($input);