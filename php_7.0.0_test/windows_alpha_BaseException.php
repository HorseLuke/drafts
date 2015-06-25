<?php

echoLine();

$e = new \Exception("1");

if($e instanceof \BaseException){
    echo "[YES]\Exception IS extends from \BaseException";
}else{
    echo "[NO]\Exception IS NOT extends from \BaseException";
}

echoLine();

try{
	eval('notExistFunction();');
}catch(\BaseException $e){
    var_dump($e);
}

try{
	eval('parseErrorLine');
}catch(\ParseException $e){
    var_dump($e);
}

echoLine();

echo "End of Test.";


function echoLine(){
    echo PHP_EOL. "===============". PHP_EOL. PHP_VERSION. PHP_EOL. "===============". PHP_EOL;
}


/*

//(非官方编译)https://phpdev.toolsforresearch.com/php-7.0.0alpha1-Win32-VC14-x86.zip

===============
7.0.0alpha1
===============
[YES]\Exception IS extends from \BaseException
===============
7.0.0alpha1
===============
object(EngineException)#2 (7) {
  ["message":protected]=>
  string(45) "Call to undefined function notExistFunction()"
  ["string":"BaseException":private]=>
  string(0) ""
  ["code":protected]=>
  int(1)
  ["file":protected]=>
  string(28) "R:\x.php(16) : eval()'d code"
  ["line":protected]=>
  int(1)
  ["trace":"BaseException":private]=>
  array(1) {
    [0]=>
    array(3) {
      ["file"]=>
      string(8) "R:\x.php"
      ["line"]=>
      int(16)
      ["function"]=>
      string(4) "eval"
    }
  }
  ["previous":"BaseException":private]=>
  NULL
}
object(ParseException)#1 (7) {
  ["message":protected]=>
  string(36) "syntax error, unexpected end of file"
  ["string":"BaseException":private]=>
  string(0) ""
  ["code":protected]=>
  int(4)
  ["file":protected]=>
  string(28) "R:\x.php(22) : eval()'d code"
  ["line":protected]=>
  int(1)
  ["trace":"BaseException":private]=>
  array(0) {
  }
  ["previous":"BaseException":private]=>
  NULL
}

===============
7.0.0alpha1
===============
End of Test.
*/



/*

//(官方QA编译)http://windows.php.net/downloads/qa/php-7.0.0alpha2-nts-Win32-VC14-x64.zip

===============
7.0.0alpha2
===============
[NO]\Exception IS NOT extends from \BaseException
===============
7.0.0alpha2
===============
PHP Fatal error:  Uncaught Error: Call to undefined function notExistFunction()
in R:\x.php(16) : eval()'d code:1
Stack trace:
#0 R:\x.php(16): eval()
#1 {main}
  thrown in R:\x.php(16) : eval()'d code on line 1

Fatal error: Uncaught Error: Call to undefined function notExistFunction() in R:
\x.php(16) : eval()'d code:1
Stack trace:
#0 R:\x.php(16): eval()
#1 {main}
  thrown in R:\x.php(16) : eval()'d code on line 1
*/
