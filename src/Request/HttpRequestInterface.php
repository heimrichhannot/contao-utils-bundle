<?php
/**
 * Created by PhpStorm.
 * User: tkoerner
 * Date: 23.02.18
 * Time: 09:27
 */

namespace HeimrichHannot\UtilsBundle\Request;


interface HttpRequestInterface
{
    public function init($url);
    public function setOption($name, $value);
    public function execute();
    public function getInfo($name);
    public function close();
}