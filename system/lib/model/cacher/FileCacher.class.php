<?php

class FileCacher implements iCacher 
{
    /**
     * Key
     *
     * @var string
     */
    protected $key;
    
    /**
     * Expire time in sec
     *
     * @var int
     */
    protected $ttl;
    
    protected $group = false;
    
    protected $cache  = array();
    
    protected $value = null;
    
    public function __construct($key, $ttl = 0, $group = 'SYSTEM')
    {
        $this->key = $key;
        $this->ttl = $ttl * 60;
        $this->group = $group;
    }
    
    
    protected function getFilePath()
    {
        return WBS_DIR."temp/cache/".Wbs::getDbkey().($this->group ? "/".$this->group : "");
    }
    
    protected function getFileName()
    {
        return $this->getFilePath()."/".$this->key.".cache";
    }
    
    public function get()
    {
        if ($this->value !== null) {
            return $this->value;
        }
        $file = $this->getFileName();
        if (file_exists($file)) {
            $info = unserialize(file_get_contents($file));
            // if cache expire
            if (isset($info['ttl']) && $info['ttl'] && time() - $info['time'] >= $info['ttl']) {
                $this->delete();
            } else {
                $this->value = $info['value'];
                return $this->value;
            }
        } 
        return null;
    }
   
    public function set($value)
    {
        $path = $this->getFilePath();
        if (file_exists($path) || $this->createDir($path)) {
		    $h = fopen($this->getFileName(), 'w+');
	    	fwrite($h, serialize(array('time' => time(), 'ttl' => $this->ttl, 'value' => $value)));
	    	fclose($h);
        }
    }
    
    public function createDir($path)
    {
        $path = str_replace("\\", "/", $path);
        return mkdir($path, 0775, true);
    }
        
    public function delete()
    {
        $file = $this->getFileName();
        if (file_exists($file)) {
            unlink($file);
        }
    }
    
    public function flush()
    {
        
    }
    
    public function isCached()
    {
        return $this->get() === null ? false : true;
    }
        
}

?>