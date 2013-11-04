<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Eduu Inc. (http://www.eduu.com)
 *
 * ��ȡ�ַ���
 * ͨ�����ڽ�ȡһ�����ĵ�ǰ���ַ���Ϊ���
 *
 * @param string $str  �ַ����ı�
 * @param int    $mx   ��ȡ�ĳ���
 * @param string $code ��ǰ�ַ�����
 * @return string
 * @--------------------------------
 * @Package GimooFend
 * @Support http://bbs.eduu.com
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: fend.strcut.php 4 2011-12-29 11:01:08Z gimoo $
**/

function strcut($str,$mx=10,$code='gbk')
{
    $str=strip_tags($str);
    $str=str_replace(array('��','��','��'),'',$str);//˫�ֽڵ��ַ�����
    $str=preg_replace('/[\s]+|(&[#0-9a-z]+;)+/is','',$str);//���˿հ��ַ�
    if(strlen($str)>$mx) $str=mb_strcut($str,0,$mx,$code);
    return $str;
}
?>