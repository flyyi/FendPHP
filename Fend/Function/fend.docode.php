<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Eduu Inc. (http://www.eduu.com)
 *
 * �ַ������ܽ���
 * ����һ��key���м��ܽ���
 *
 * @param string $string    ��Ҫ������ַ���
 * @param string $operation �ӽ�����,de����|en����
 * @param string $key       �������,Ĭ��ΪFDKEY
 * @param string $expiry    ����ʱ��
 * @return string
 * @--------------------------------
 * @Package GimooFend
 * @Support http://bbs.eduu.com
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: fend.docode.php 4 2011-12-29 11:01:08Z gimoo $
**/

define('FDKEY','FendGimoo');
function docode($string, $operation = 'de', $key = '', $expiry = 0)
{
    $result = null;
    $ckey_length = 10;//�����Կ
    $key = md5($key ? $key : FDKEY);//ȡ����ԿMD5��
    $keya = md5(substr($key, 0, 16));//��ԿMD5��ǰ16λ
    $keyb = md5(substr($key, 16, 16));//��ԿMD5�ĺ�16λ

    $keyc = $ckey_length ? ($operation == 'de' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'de' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);

    $box = range(0, 255);
    $rndkey = array();
    for($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    for($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if($operation == 'de') {
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc.str_replace('=', '', base64_encode($result));
    }
}

?>