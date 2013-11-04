<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://fend.gimoo.net)
 *
 * ������� ��Ҫ�������������ļ�,��������
 * �罫ҳ�沿����Ϣ,���ݿ��ѯ��������浽�ļ�
 *
 * �����ļ��ĸ�ʽ����:
 * ================== ���� ==========================
 *   Fend_dir    =/fend/
 *   Fend_Cache  =/Fend/Cache
 *   [scfg]
 *   isStart     =yes
 *   isMake      =1
 *
 *   ����ͨ��get���뷽ʽ���Եõ�
 *   $var=array(
 *       'Fend_dir'=>'/fend/',
 *       'Fend_Cache'=>'/Fend/Cache',
 *       'scfg'=>array(
 *           'isStart'=>'yes',
 *           'isMake'=>'1',
 *       ),
 *   )
 *   ͨ��load��ʽ����
 *   $_Fend_dir='/fend/'
 *   $_Fend_Cache='/Fend/Cache'
 *   $_scfg['isStart']='yes'
 *   $_scfg['isMake']='1'
 * ================== ���� ==========================
 *
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: Conf.php 4 2011-12-29 11:01:08Z gimoo $
**/

class Fend_Conf
{
    /**
     * Ԥ������ ��չʹ��
     *
    **/
    public static function factory()
    {
    }

    /**
     * ��������
     * ��Ϊ���鷵��
     *
     * @param  string  $fname �����ļ�·���Լ�����
     * @return array
    **/
    static function get($fname)
    {
        $item=array();
        $fs=fopen($fname,'rb');
        if(!$fs) return $item;
        //��ʼ��ȡ�ļ�
        $k=null;//�����ʱ����
        while($buf=fgets($fs,128)){
            $buf=trim($buf);
            if(empty($buf)) continue;
            //����ע��
            $s=substr($buf,0,1);
            if($s==';' || $s=='#') continue;
            //������
            if($s=='[' && substr($buf,-1)==']'){
                $k=substr($buf,1,-1);
                continue;
            }

            list($key,$value)=explode('=',$buf);
            if(isset($k)){
                $item[$k][rtrim($key)]=ltrim($value);
            }else{
                $item[rtrim($key)]=ltrim($value);
            }
        }
        return $item;
    }

    /**
     * ��������
     * ��Ϊ���鷵��
     *
     * @param  string  $fname �����ļ�·���Լ�����
     * @return bool
    **/
    static function load($fname)
    {
        $fs=fopen($fname,'rb');
        if(!$fs) return false;
        $item=&$GLOBALS;
        //��ʼ��ȡ�ļ�
        $k=null;//�����ʱ����
        while($buf=fgets($fs,128)){
            $buf=trim($buf);
            if(empty($buf)) continue;
            //����ע��
            $s=substr($buf,0,1);
            if($s==';' || $s=='#') continue;
            //������
            if($s=='[' && substr($buf,-1)==']'){
                $k=substr($buf,1,-1);
                continue;
            }

            list($key,$value)=explode('=',$buf);
            if(isset($k)){
                $GLOBALS['_'.$k][rtrim($key)]=ltrim($value);
            }else{
                $GLOBALS['_'.rtrim($key)]=ltrim($value);
            }
        }
        return true;
    }
}

?>