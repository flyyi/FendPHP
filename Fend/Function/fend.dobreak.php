<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://Fend.Gimoo.Net)
 *
 * 301�ض���
 * Ĭ�϶�������ҳ��
 *
 * @param string $url ת���URL��ַ
 * @return void
 *
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: fend.dobreak.php 4 2011-12-29 11:01:08Z gimoo $
**/

function doBreak($url=null)
{
    header('location:'.(null===$url ? $_SERVER['HTTP_REFERER'] : $url));exit;
}
?>