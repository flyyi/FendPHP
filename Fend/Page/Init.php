<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://fend.gimoo.net)
 *
 * ����ģʽ �����ҳ����
 * ���ڶ�̬���뼰�������
 *
 * ע��: ׼������ ����һ�汾����ɾ���ö���, ��ʹ��Fend_Page����
 *
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: Init.php 4 2011-12-29 11:01:08Z gimoo $
**/

class Fend_Page_Init
{
    public static $pageType=array(
        0=>'Fend_Page_Dbpage',
        1=>'Fend_Page_Dbpage1',
    );

    public static function factory($tp=0)
    {
       $c=&self::$pageType[$tp];
       $c=new $c;
       return $c;
    }
}


?>