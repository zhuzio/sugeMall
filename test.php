<?php
/**
 * Created by PhpStorm.
 * User: wendy
 * Date: 2017/10/11
 * Time: 18:43
 */
$str='超超123\'';
echo $str."##";
preg_match_all('/[\x{4e00}-\x{9fff}]+/u', $str, $matches);
$str = join('', $matches[0]);
echo $str;