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

class Fend_Soap_Client extends SoapClient
{
    public $tbSoap=array(
        'name'=>'uri',
        'url'=>'location',
        'lang'=>'encoding',
    );

    //�ӹܹ���
    function __construct(){}

    //��ʼ������
    public function Init()
    {
        //����Ĭ������
        $sp=array(
            'uri'=>'PhpSoap',
            'encoding'=>'GBK',
            'location'=>'http://'.@$_SERVER['HTTP_HOST'],

        );

        $getArgs=func_get_args();
        foreach($getArgs as $v){
            if(!preg_match('/^([^:]+):(.*?)$/Ui',$v,$sv)){continue;}
            $k=isset($this->tbSoap[$sv[1]]) ? $this->tbSoap[$sv[1]] : $sv[1];
            $sp[$k]=$sv[2];
        }

        //û�����������ռ�ʱ,�����Զ�����Ϊ��ǰ������
        !isset($sp['uri']) && $sp['uri']=$_SERVER['HTTP_HOST'];
        parent::__construct(NULL,$sp);
    }

    //��Զ��Soap������ͨѶ
    public function __call($fName,$fValue)
    {
        return self::__soapCall($fName,$fValue);
    }

}


?>