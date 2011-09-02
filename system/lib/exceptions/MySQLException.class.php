<?php

class MySQLException extends Exception
{
	const CONTEXT_RADIUS  = 5;
		
    private function getFileContext()
    {
    	$file = $this->getFile();
    	$line_number = $this->getLine();
        $context = array();
        $i = 0;
        foreach(file($file) as $line) {
            $i++;
            if($i >= $line_number - self::CONTEXT_RADIUS && $i <= $line_number + self::CONTEXT_RADIUS) {
                if ($i == $line_number) {
                    $context[] = ' >>'. $i ."\t". $line;
                }else{
                    $context[] = '   '. $i ."\t". $line;
                }
            }
            if($i > $line_number + self::CONTEXT_RADIUS) break;
        }
        return "\n". implode("", $context);
    }
	
	
/*	public function __toString()
	{
		if (defined('DEVELOPER') || Env::Get('debug')) {
			$message = nl2br($this->getMessage());
			return <<<HTML
  <style>
    body { background-color: #fff; color: #333; }
    body, p, ol, ul, td { font-family: verdana, arial, helvetica, sans-serif; font-size: 13px; line-height: 25px; }
    pre { background-color: #eee; padding: 10px; font-size: 11px; line-height: 18px; }
    a { color: #000; }
    a:visited { color: #666; }
    a:hover { color: #fff; background-color:#000; }
  </style>
  <script>
  function TextDump() {
    w = window.open('', "Error text dump", "scrollbars=yes,resizable=yes,status=yes,width=1000px,height=800px,top=100px,left=100px");
    w.document.write('<html><body>');
    w.document.write('<h1>' + document.getElementById('Title').innerHTML + '</h1>');
    w.document.write(document.getElementById('Context').innerHTML);
    w.document.write(document.getElementById('Trace').innerHTML);
    w.document.write(document.getElementById('Request').innerHTML);

    w.document.write('</body></html>');
    w.document.close();
  }
  </script>
<div style="width:99%; position:relative">
<h2 id='Title'>{$message}</h2>
<a href="#" onclick="TextDump(); return false;">Raw dump</a>
<div id="Context" style="display: block;"><h3>Error in '{$this->getFile()}' around line {$this->getLine()}:</h3><pre>{$this->getFileContext()}</pre></div>
<div id="Trace"><h2>Call stack</h2><pre>{$this->getTraceAsString()}</pre></div>
<div id="Request"><h2>Request</h2><pre></pre></div>
</div>
HTML;
		} else {
			return _("Error executing query");
		}		
	}
*/
} 

?>
