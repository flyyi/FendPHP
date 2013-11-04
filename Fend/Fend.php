<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://fend.gimoo.net)
 *
 * ·���� Fend��ܵĺ��ļ�����
 * ����кܶ�ģ����Ҫͨ���ö�����м���
 *
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: Fend.php 4 2011-12-29 11:01:08Z gimoo $
**/
!defined('FD_DS') && define('FD_DS', DIRECTORY_SEPARATOR);
!defined('FD_ROOT') && define('FD_ROOT', dirname(__FILE__).FD_DS);
!defined('FD_LIBDIR') && define('FD_LIBDIR', dirname(FD_ROOT).FD_DS);
!defined('FD_AUTOLOAD') && define('FD_AUTOLOAD','fend_autoload');

abstract class Fend
{
    /**
     * Ĭ���Զ���ִ�еķ���,�ɱ�����
    **/
    public function Init(){}

    /**
     * �������ݿ����
     *
     * @param integer $tb ���ݿ��ʶ
     * @param boolean $dr �Ƿ����쳣���
    **/
    protected function getDb($tb=0,$dr=false)
    {
        $this->db->dbError=$dr;
        $this->db->getConn($tb);
    }

    /**
     * �������úϲ���ϵͳ������
     *
     * @param  string $fn �����ļ����ư�����׺
     * @return array ���ü���
    **/
    protected function getCfg($fn=null)
    {
        $fn && $this->cfg=array_merge($this->cfg,(array)Fend_Cache::get($fn));
        return $this->cfg;
    }

    /**
     * ע�������ģ��
     * ע��: �ں������ڵı���ʵ����ȫ����,���ظ�ռ���ڴ��Լ�CPU��Դ
     * ����ʹ��refVar���ô���
     *
     * @param string  $strVar ����ָ��
     * @param string  $tplVar ģ���еı�������
     * @param integer $tp     �Ƿ�����ע��
    **/
    protected function regVar($strVar,$tplVar='tmy')
    {
        $this->tpl->assign($tplVar,$strVar);
    }

    /**
     * ���ñ�����ģ��
     * ע��: ����ʱ����1����Ϊ����,����Ϊ����
     * ��һ�ֽ�ʡ��Դ�Ĵ��ݷ�ʽ
     *
     * @param string  $strVar ����ָ��
     * @param string  $tplVar ģ���еı�������
     * @param integer $tp     �Ƿ�����ע��
    **/
    protected function refVar(&$strVar,$tplVar='tmy')
    {
        $this->tpl->assignbyref($tplVar,$strVar);
    }

    /**
     * ����ģ�岢����
     *
     * @param string $tplVar ģ���ļ�����
    **/
    protected function showView($tplVar)
    {
        $this->tpl->display($tplVar);
    }

    /**
     * ħ������: ��̬����ȫ�ֱ��� ������������ʱ��ͼ����֮
     *
     * @param  string $k ��������
     * @return variable  ��������
    **/
    public function &__get($k)
    {
        !isset($GLOBALS['_'.$k]) && self::__set($k);
        return $GLOBALS['_'.$k];
    }

    /**
     * ħ������: ��̬����ȫ�ֱ��� ���ɹ������ı���������GLOBALS��
     *
     * Example : $this->var1=123 ������var1������ʱ�Զ�������$GLOBALS['_var1']��
     * @param string $k ��������
     * @param string $v ����ֵ
    **/
    public function __set($k,$v=null)
    {
        if(!isset($GLOBALS['_'.$k])){//��ʼ��ϵͳ����
            if(isset($this->FD_REG_FUNC[$k])){
                $v=$this->FD_REG_FUNC[$k]();
            }else{
                $GLOBALS['_'.$k]=&$v;
            }
        }
        $GLOBALS['_'.$k]=$v;//������ʱ����
    }

    /**
     * ħ������: ��ⱻ��̬�����ı���Ҳ������ȫ�ֱ���GLOBALS
     *
     * Example : isset($this->var1) = isset($GLOBALS['_var1'])
     * @param string $k ��������
    **/
    public function __isset($k)
    {
        return isset($GLOBALS['_'.$k]);
    }

    /**
     * ħ������: �ͷű�����Դ
     *
     * Example : unset($this->var1) = unset($GLOBALS['_var1'])
     * @param string $k ��������
    **/
    public function __unset($k)
    {
        unset($GLOBALS['_'.$k]);
    }

    /**
     * �����쳣��Ϣ
     *
     * @param string $msg �쳣��Ϣ
     * @param string $code �������
    **/
    protected function showTry($msg,$code=__LINE__)
    {
        throw new Fend_Exception($msg,$code,true);
    }
}

/**
 * ħ������: �Զ����ض����ļ�
 *
 * ע��: ��Fendǰ׺�Ķ��� ͨ��FD_ROOT���õ�Ŀ¼����
 *       ����ǰ׺���� ͨ��FD_LIBDIR���õ�·������
 *
 * @param string $fname ��������
 * $fn=FD_LIBDIR.str_replace('_','/',$fn);
**/
function Fend_AutoLoad($fname)
{
    $fn=explode('_',$fname);
    $fn[0]=$fn[0]=='Fend' ? FD_ROOT : FD_LIBDIR.$fn[0];
    $fn=join('/',$fn);

    if(!is_file($fn.'.php')){//��׽�쳣
        eval("class $fname{};");//��ʱ����һ��Ŀ�����
        throw new Fend_Exception("Has Not Found Class $fname");
    }else{
        require_once($fn.'.php');
    }
}
spl_autoload_register(FD_AUTOLOAD);
?>