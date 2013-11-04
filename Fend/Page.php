<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://fend.gimoo.net)
 *
 * ����ģʽ �����ҳ����
 * ���ڶ�̬���뼰�������
 *
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: Page.php 4 2011-12-29 11:01:08Z gimoo $
**/

class Fend_Page
{
    /**
     * ����ģʽ ��̬�������
     * @param  string $name  ���ݿ�����
     * @return object $in       ���ݿ����
    **/
    public static function factory($name)
    {
        $obj='Fend_Page_'.ucfirst($name);
        $obj=new $obj;

        $args=func_get_args();unset($args[0]);
        foreach($args as $v){
            $v=explode(':',$v,2);
            if(!isset($v[1])) continue;
            $obj->$v[0]=$v[1];
        }
        return $obj;
    }
}

?>