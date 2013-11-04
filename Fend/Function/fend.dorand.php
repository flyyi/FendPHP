<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://Fend.Gimoo.Net)
 *
 * ��ȡһ���Զ����ַ����ϵ������
 *
 * @param int    $len  ���ȡ�ó���
 * @param string $chr  ֻ��Ϊ���ֽ��ַ�
 * @return string
 * @--------------------------------
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: fend.dorand.php 4 2011-12-29 11:01:08Z gimoo $
**/

function doRand($len,$chr = '0123456789abcdefghigklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWSYZ')
{
    $hash = null;
    $max = strlen($chr) - 1;
    for($i = 0; $i < $len; $i++){
        $hash .= $chr{mt_rand(0, $max)};
    }
    return $hash;
}
?>