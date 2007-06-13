<?php
/*
lol_core.php Ver 0.2
Copyright (c) 2007 Jeff Jones, www.tetraboy.com

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/
function lol_core_parse($code) {
    $code = lol_core_replace($code);
    return $code;
}
//@todo only replace inside tags
function lol_core_replace($code) {
    $array = array(
        '/^HAI$/','<?php',
        '/^KTHXBYE$/','?>',
        '/^[\s]*CAN HAS ([^\.]+)\?$/','include(\'$1.php\');',
        '/^[\s]*MUST HAS ([^\.]+)$/','require(\'$1.php\');',
        '/^[\s]*VISIBLE ([a-zA-Z0-9-]+)$/','echo \$$1;',
        '/^[\s]*VISIBLE (.*)$/','echo $1;',
        '/^[\s]*I HAS A ([a-zA-Z0-9_-]+)$/','\$$1 = null;',
        '/^[\s]*I HAS A ([a-zA-Z0-9_-]+) ITZ (BUCKET)$/','\$$1 = array(',
        '/^[\s]*I HAS A ([a-zA-Z0-9_-]+) ITZ ([a-zA-Z0-9]+)$/','\$$1 = \'$2\';',
        '/^[\s]*I HAS A ([a-zA-Z0-9_-]+) ITZ (.*)$/','\$$1 = $2;',
        '/^[\s]*([a-zA-Z0-9_-]+) IZ (BUCKET)$/','\$$1 = array(',
        '/^[\s]*([a-zA-Z0-9_-]+) IZ ([a-zA-Z0-9]+)$/','\$$1 = \'$2\';',
        '/^[\s]*([a-zA-Z0-9_-]+) IZ (.*)$/','\$$1 = $2;',
        '/&&([a-zA-Z0-9_-]+)&&/','\$\$$1',
        '/&([a-zA-Z0-9_-]+)&/','\$$1',
        '/&([a-zA-Z0-9_-]+)#([a-zA-Z0-9,_-]+)&/e','lol_core_pregArray(\'$1\',\'$2\');',
        '/^([\s]*)(KTHX)([\s]*)$/','}',
        '/^[\s]*([a-zA-Z0-9_-]+) UPUP!$/','\$$1++;',
        '/^[\s]*([a-zA-Z0-9_-]+) DOWNDOWN!$/','\$$1--;',
        '/^[\s]*I FOUND MAH ([a-zA-Z0-9_-]+)$/','return \$$1;',
        '/^[\s]*I FOUND MAH (.*)$/','return $1;',
        '/^[\s]*SO IM LIKE ([a-zA-Z0-9_-]+) WITH (.*)$/e','lol_core_pregFunc(\'$1\',\'$2\');',
        '/^[\s]*BTW (.*)$/','//$1',
        '/^[\s]*BTW!$/','/*',
        '/^[\s]*!BTW$/','*/',
        '/^[\s]*ALWAYZ ([a-zA-Z0-9_-]+) IZ (.*)$/','define("__$1__","$2");',
        '/^[\s]*(IZ) (.*)$/e','lol_core_pregExpression(\'if\',\'$2\');',
        '/^[\s]*(ORLY) (.*)$/e','lol_core_pregExpression(\'elseif\',\'$2\');',
        '/^[\s]*(NOWAI)$/e','lol_core_pregExpression(\'else\',\'$2\');',
        '/^[\s]*BUCKET$/',');',
        '/^[\s]*BAG$/','),',
        '/^[\s]*(!!) FISH (".*") !!$/','$2,',
        '/^[\s]*([a-zA-Z0-9_-]+) FISH IZ BAG$/','\'$1\' => array(',
        '/^[\s]*([a-zA-Z0-9_-]+) FISH (".*") !!$/','\'$1\' => $2,',
        '/^[\s]*IM IN YR ([a-zA-Z0-9_-]+)$/','while(true) {',
        '/^[\s]*IM IN YR ([a-zA-Z0-9_-]+) ITZA ([a-zA-Z0-9_-]+)$/','foreach(\$$1 as \$$2) {',
        '/^[\s]*IM IN YR ([a-zA-Z0-9_-]+) ITZA (.*)$/','foreach(\$$1 as $2) {',
        '/^[\s]*DIAF[\s]*?(.*)$/','die("$1");',
    );
    $search = array();
    $replace = array();
    $lines = explode("\n",$code);

    foreach($array as $key=>$var){
        if(1 & $key)    {
            $replace[] = $var;
        } else {
            $search[] = $var;
        }
    }
    $lines = preg_replace($search,$replace,$lines);
    $code = implode("\n",$lines);
    return $code;
}
function lol_core_pregArray($name,$string) {
    $var = '$'.$name;
    $keys = explode(',',$string.',');
    foreach($keys as $key) {
        if($key !== '') {
            $var .= "['{$key}']";
        }
    }
    return $var;
}
function lol_core_pregExpression($name,$string) {
    switch($name) {
        case 'if':
            $expr = "if ($string) {";
        break;
        case 'elseif':
            $expr = "} elseif ($string) {";
        break;
        case 'else':
            $expr = "} else {";
        break;
    }
    return $expr;
}
function lol_core_pregFunc($name,$args) {
    $func = 'function '.$name.' (';
    $args = explode(' ',$args);
    $i=0;
    foreach ($args as $arg) {
        if($i==1){$func .= ',';}
        $func .= '$'.$arg;
        $i=1;
    }
    $func .= ') {';
    return $func;
}
?>