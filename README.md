# CmdCli

Is a library created to parse command line options and arguments. 
This library reads command line string parts and map each option with an object.
Also it can shows the help using option description.

## Installation

composer require apolinux/cmdcli

## Example

```
<?php

use Apolinux\CmdCli\CmdCli;

require_once __DIR__ . '/vendor/autoload.php';

$cmd_cli = new CmdCli('A command name');

$cmd_cli->addOpt(
  'xml',  // name of option
  CmdCli::TYPE_SINGLE_OPT,  // data type
  'is an xml option', // description
  'x', // short option, '-x'
  '--xml', // long option, '--xml' 
  false  ,    // require a parameter or not
  false      // is optional or not
)
->addArg(
  'logname' , // argument name
  'is a log name', // description
  true,  // is optional
);

$input = ['cmdname','-abc','-y','--sign','-x','file123.log'];

$text=$cmd_cli->parse($input);

// get options
$options = $cmd_cli->getParsedOpts();

// get arguments
$args = $cmd_cli->getParsedArgs();

// to know if one argument is defined. It shows true
var_dump($options['xml']);

// get argument value. It shows 'file123.log'
echo $args['logname'];

// other options not defined before are ignored

```

