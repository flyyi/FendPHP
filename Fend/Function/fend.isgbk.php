<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Eduu Inc. (http://www.eduu.com)
 *
 * ��⴮���Ƿ��������
 *
 * @param string $str ��Ҫ�����ַ���
 * @return bool ��������Ϊtrue ����Ϊfalse
 * @--------------------------------
 * @Package GimooFend
 * @Support http://bbs.eduu.com
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: fend.isgbk.php 4 2011-12-29 11:01:08Z gimoo $
**/

function isgbk($str)
{
    for($i=0,$j=strlen($str);$i<$j;$i++){
      if(ord($str{$i})>0xa0) return true;
    }
    return false;
}
?>