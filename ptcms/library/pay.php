<?php

/**
 * @Author: 杰少Pakey
 * @Email : admin@ptcms.com
 * @File  : pay.php
 */
abstract class pay {

    public static function getInstance($type) {

    }

    abstract public function to();

    abstract public function signfunc();

    abstract public function verifyNotify();

    abstract public function notify();

}