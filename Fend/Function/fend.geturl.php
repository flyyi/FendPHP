<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://Fend.Gimoo.Net)
 *
 * ��ȡURL
 * ȡ�ø���ǰURL��ȫ���ַ�����ʾ
 *
 * @param int $tx Ϊ0ʱ��ǰURL,Ϊ1ʱ��URL
 * @return string
 * @--------------------------------
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: fend.geturl.php 4 2011-12-29 11:01:08Z gimoo $
**/

function getUrl($tx=0)
{
    if($tx) return empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER'];
    $url='http://'.$_SERVER['HTTP_HOST'];
    if(isset($_SERVER['REQUEST_URI'])){
        $url.=$_SERVER['REQUEST_URI'];
    }else{
        $url.=$_SERVER['PHP_SELF'].(!empty($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : '');
    }
    return $url;
}
?>