<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Eduu Inc. (http://www.eduu.com)
 *
 * ����Ƿ�Ϊ������URL
 *
 * @param string $url ��Ҫ�����URL
 * @return bool
 * @--------------------------------
 * @Package GimooFend
 * @Support http://bbs.eduu.com
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: fend.isurl.php 4 2011-12-29 11:01:08Z gimoo $
**/

function isUrl($url)
{
    return preg_match('/^(http|https):\/\/([\w-]+\.)+[\w-]+([\/|?]?[^\s]*)*$/i', $url) ? true : false;
}
?>