<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://Fend.Gimoo.Net)
 *
 * ��ȷ����·��
 * ����Ϊ���ϵ�ǰϵͳ��·����ʾ
 *
 * @param string $str ����·���ַ���
 * @return string �õ���ǰϵͳ�淶������·��
 * @--------------------------------
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: fend.dopath.php 4 2011-12-29 11:01:08Z gimoo $
**/

function doPath($str)
{
    $str=preg_replace('/[\/\\\\]+/',DIRECTORY_SEPARATOR,$str);
    substr($str,-1)!=DIRECTORY_SEPARATOR && $str.=DIRECTORY_SEPARATOR;
    return $str;
}
?>