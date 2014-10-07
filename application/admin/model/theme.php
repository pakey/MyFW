<?php

class themeModel {

    public function getlist() {
        $fp = opendir(TPL_PATH);
        $list = array();
        while ($path = readdir($fp)) {
            $file = TPL_PATH . '/' . $path;
            if ($path != '.' && $path != '..' && is_dir($file)) {
                $configfile = $file . '/config.ini';
                if (is_file($configfile)) {
                    $config = parse_ini_file($configfile, true);
                    $config['demo'] = is_file($file . '/demo.jpg') ? str_replace(PT_ROOT, '', $file . '/demo.jpg') : PT_DIR . '/public/image/nopic.jpg';
                    $list[$path] = $config;
                }
            }
        }
        return $list;
    }
}