<?php
class FilesFunctions
{
    /**
     * Converts backslashes to slashes
     * 
     * @param string $filePath
     * @return string - fixes path 
     */
    public static function fixPathSlashes ($filePath)
    {
        return str_replace('\\', '/', $filePath);
    }

    public static function getSizeStr ($fileSize)
    {
        if (! $fileSize) {
            return "0.00 KB";
        }
        $res = "";
        if ($fileSize < 1024) {
            $res = $fileSize . " bytes";
        } else 
            if ($fileSize < 1024 * 1024) {
                $res = round(100 * (ceil($fileSize) / 1024)) / 100 . " KB";
            } else {
                $res = round(100 * ceil($fileSize) / (1024 * 1024)) / 100 . " MB";
            }
        return $res;
    }
}
?>