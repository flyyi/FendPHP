<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://fend.gimoo.net)
 *
 * Soap �ͻ�����չ����
 *
 *
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: Client.php 4 2011-12-29 11:01:08Z gimoo $
**/

class Fend_Fcli_Client
{
    public static $in=null;
    public $_cfg=array(
        'url'=>'',//Զ�̵�ַ
        'host'=>'',//Զ�̵�ַ
        'port'=>80,//Զ�̵�ַ
        'key'=>'',//������Կ
        'time'=>15,//��ʱ����
        'debug'=>0,//�Ƿ������
    );
    public static function Factory()
    {
        if(null===self::$in) self::$in=new self();
        return self::$in;
    }

    /**
     * ��ʼ������
     * ������ز�����ͨ�Žӿ�
     *
     * @param  mixed ��Ҫ���õĲ������ϸ�ʽ:('name:appCMS','user:appCMS','pwd:fendcms')
     * @return void
    **/
    public function Init()
    {
        if(func_num_args()>0){
            $_cfg=&self::$in->_cfg;
            $args=func_get_args();
            foreach($args as $v){
                $v=explode(':',$v,2);
                $_cfg[$v[0]]=$v[1];
            }
        }

        if(empty(self::$in->_cfg['url'])){
            throw new Fend_Acl_Exception("Not Found cfg[url]",404);
        }
    }

    /**
     * �������Զ�̷���
     * //eval("class appfcli{};");
     *
     * @param  string $fc ��ѡ����
     * @return object Fend_Fcli_Base
    **/
    public function run($fc=null)
    {
        return new Fend_Fcli_Request($this,$fc);
    }
}

?>