<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://fend.gimoo.net)
 *
 * �Զ����뺯��ģ�� [Auto Load Function]
 * ���������ڿ���п���,��̬�ļ����ⲿ����
 *
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: Func.php 4 2011-12-29 11:01:08Z gimoo $
**/

class Fend_Func
{
    private $fdir=null;
    private $fitem=array();
    private static $in=null;

    /**
     * ����̬��ʼ��������洢��[Function]Ŀ¼�ĺ�����
     *
     * @Example:
     *          ��̬����: Fend_Func::factory('dopost')
     *          ��̬����: Fend_Func::factory('dopost','doget')
     *          ��̬����: Fend_Func::factory()->dopost();
     * @param  string ������,���
     * @return Object
    **/
    public static function factory()
    {
        if(null===self::$in){
            self::$in = new self();
            self::$in->fdir=FD_ROOT.'Function/';
            $GLOBALS['_FD_FUNC']=&self::$in->fitem;
        }
        $args=func_get_args();
        foreach($args as $v) self::$in->isFunction($v);
        return self::$in;
    }

    /**
     * ��ͬ�� factory ����
     * ע��: �÷���׼������,����һ�汾�з���
    **/
    public static function Init()
    {
        if(null===self::$in){
            self::$in = new self();
            self::$in->fdir=FD_ROOT.'Function/';
            $GLOBALS['_FD_FUNC']=&self::$in->fitem;
        }
        $args=func_get_args();
        foreach($args as $v) self::$in->isFunction($v);
        return self::$in;
    }

    /**
     * ��Ⲣ���뺯��,˽�ܷ������ڲ�ʹ��
     *
     * @param  string ������
     * @return null
    **/
    private function isFunction($fn)
    {
        $fn=strtolower($fn);
        if(!in_array($fn,$this->fitem)){
            if(is_file($this->fdir.'fend.'.$fn.'.php')){
                include($this->fdir.'fend.'.$fn.'.php');
            }else{
                trigger_error("Has Not Found Function $fn()", E_USER_WARNING);
                //throw new Fend_Exception("Has Not Found Function $fn()",__LINE__);
            }
            $this->fitem[]=$fn;
        }
    }

    /**
     * ħ������: �Զ���������в����ڵķ���
     *
     * @param  string  ������
     * @return resource
    **/
    public function __call($fn,$fv)
    {
        self::isFunction($fn);
        return call_user_func_array($fn,$fv);
    }
}
?>