<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Eduu Inc. (http://www.eduu.com)
 *
 * ���´���URL��Ϣ
 *
 * @param string $key   ��ֵ�ļ�
 * @param string $value �����ֵ
 * @return string
 * @--------------------------------
 * @Package GimooFend
 * @Support http://bbs.eduu.com
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: fend.dopars.php 4 2011-12-29 11:01:08Z gimoo $
**/

function dopars($key,$value)
{
    parse_str($_SERVER['QUERY_STRING'],$pars);
    $pars[$key]=$value;
    $pars=http_build_query($pars);
    return $pars;
}

?>