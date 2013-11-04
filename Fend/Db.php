<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://fend.gimoo.net)
 *
 * ���ݿ������,ͨ����ģ����м������л����ݿ�Ӧ��
 * ��MYSQL MYSQLI Sqlserver��
 * ע��: ���ж���������Fend_Db_Base��׼
 *
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: Db.php 4 2011-12-29 11:01:08Z gimoo $
**/

class Fend_Db
{
    static public $in=null;//���浱ǰ����

    /**
     * ����ģʽ ��̬�������
     * @param  string $dbClass  ���ݿ�����
     * @return object $in       ���ݿ����
    **/
    public static function factory($dbClass)
    {
        if(!isset(self::$in)){
            $dbClass='Fend_Db_'.ucfirst(strtolower($dbClass));
            self::$in=new $dbClass;
        }
        return self::$in;
    }
}
?>