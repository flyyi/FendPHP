<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Eduu Inc. (http://www.eduu.com)
 *
 * ���һ��IP��ַ�Ƿ�����
 * ֻ�ܽ���ģ���ļ��
 *
 * @Package GimooFend
 * @Support http://bbs.eduu.com
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: fend.isip.php 4 2011-12-29 11:01:08Z gimoo $
**/

function isIp($ip)
{
    return preg_match('/^\d{0,3}\.\d{0,3}\.\d{0,3}\.\d{0,3}$/',$ip) ? true : false;
}
?>