<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Eduu Inc. (http://www.eduu.com)
 *
 * ����Ƿ�ΪEmail
 *
 * @param string $str �����ʼ�Email
 * @return bool �ɹ�����true
 * @--------------------------------
 * @Package GimooFend
 * @Support http://bbs.eduu.com
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: fend.ismail.php 4 2011-12-29 11:01:08Z gimoo $
**/

function ismail($str)
{
    return preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', $str);
}

?>