<?php

interface iCacher 
{
    public function get();
    
    public function set($value);
    
    public function delete();
    
    public function flush();
    
    public function isCached();
}

?>
