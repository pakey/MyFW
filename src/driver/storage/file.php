<?php

namespace ptcms\driver\storage;

use \ptcms\Config;

class File
{

    protected static $path = null;
    protected static $url  = null;

    public function __construct($config = [])
    {
        self::$path = isset($config['path']) ? $config['path'] : (PUBLIC_PATH . DIRECTORY_SEPARATOR . Config::get('storage.path'));
        self::$url  = isset($config['url']) ? $config['url'] : (PUBLIC_URL . '/' . Config::get('storage.url') . '/');
    }

    public function exist($file)
    {
        $fullfile = strpos($file, PTCMS_ROOT) === 0 ? $file : self::$path . $file;
        return is_file($fullfile);
    }

    public function write($file, $content)
    {
        $fullfile = strpos($file, PTCMS_ROOT) === 0 ? $file : self::$path . $file;
        if (!strpos($file, '://') && !is_dir(dirname($fullfile))) {
            mkdir(dirname($fullfile), 0755, true);
        }
        return file_put_contents($fullfile, (string)$content);
    }

    public function read($file)
    {
        $fullfile = strpos($file, PTCMS_ROOT) === 0 ? $file : self::$path . $file;
        return file_get_contents($fullfile);
    }

    public function append($file, $content)
    {
        $fullfile = strpos($file, PTCMS_ROOT) === 0 ? $file : self::$path . $file;
        if (!strpos($file, '://') && !is_dir(dirname($fullfile))) {
            mkdir(dirname($fullfile), 0755, true);
        }
        return file_put_contents($fullfile, (string)$content, FILE_APPEND);
    }

    public function remove($file)
    {
        $file = strpos($file, PTCMS_ROOT) === 0 ? $file : self::$path . $file;
        if (is_file($file)) {
            //删除文件
            return unlink($file);
        } elseif (is_dir($file)) {
            //删除目录
            $handle = opendir($file);
            while (($filename = readdir($handle)) !== false) {
                if ($filename !== '.' && $filename !== '..') $this->remove($file . '/' . $filename);
            }
            closedir($handle);
            return rmdir($file);
        }
    }

    public function getUrl($file)
    {
        $file = strpos($file, PTCMS_ROOT) === 0 ? substr($file, strlen(self::$path)) : $file;
        return str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR,self::$url . $file);
    }

    public function getPath($file)
    {
        $fullfile = strpos($file, PTCMS_ROOT) === 0 ? $file : self::$path . $file;
        return $fullfile;
    }

    public function error()
    {
        return '';
    }
}