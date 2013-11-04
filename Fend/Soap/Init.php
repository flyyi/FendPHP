<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://fend.gimoo.net)
 *
 * Soap��̬�������
 * �ͻ���:
 *  $sp=Fend_Soap_Init::Factory(1);
 *  $sp->__SetCookie('Name','value');//ע��COOKIE����������֤ͨ��ʹ��
 *  $sp->Init('uri:cmsApi','url:'.Cms_Conf_Bbs::$soapUrl);//��ʼ������
 *  $sp->getTx();//����Զ�̷���
 *
 * �����
 *  $sp=Fend_Soap_Init::Factory(0);
 *  $sp->Init('name:cmsApi');//��ʼ������
 *  $sp->setClass('cmsApi');//ע��һ�����õķ������
 *  $sp->handle();
 *  ע: �����ע��ĺ����Լ������еķ���������"__"��ͷ,"__"ΪFend_Soap�ı����ַ�,������ܻ��������벻�����
 *
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: Init.php 4 2011-12-29 11:01:08Z gimoo $
**/

require_once('Server.php');
require_once('Client.php');
class Fend_Soap_Init
{
    public static $instance;
    public static $pageType=array(0=>'Fend_Soap_Server',1=>'Fend_Soap_Client');
    public static function factory($tp=0)
    {
        if(!isset(self::$instance)){
           $c=&self::$pageType[$tp];
           self::$instance = new $c;
       }
       return self::$instance;
    }


}


?>