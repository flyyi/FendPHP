<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://fend.gimoo.net)
 *
 * ʵ��˵��
 *
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: Example.php 4 2011-12-29 11:01:08Z gimoo $
**/

/**
 * ·�ɲ� router.php
 * ��Ҫ����:
 * ȫ�������ļ� config.php
 * Fend��� Fend.php
**/
require_once('config.php');//��������
require_once(FD_ROOT.'Fend.php');//Fend���
class Router extends Fend
{
    /**
     * ����ģ�岢����
     * �̳�Fend::showView����
     *
     * @param  string $tplVar ģ���ʶ
     * @param  string $tplDir ģ��Ŀ¼
     * @param  string $tplPre ģ���׺
    **/
    public function showView($tplVar,$tplPre='.tpl')
    {
        //����һ��������Smartyģ���ڲ�
        parent::regVar($this->dm,'dm');
        $tplVar=empty($this->uri[1]) ? $tplVar : $this->uri[1].'/'.$tplVar;
        parent::showView($tplVar.$tplPre);
    }

    /**
     * ������ʾ��Ϣ
     * $aUrl Ϊ��ֱ�ӷ��� Ϊ1���ظ�ģ�� �ǿշ��ص�ָ��ģ��
     *
     * @param  string $txt  ��ʾ��Ϣ
     * @param  string $aUrl Ϊ��ֱ�ӷ��� Ϊ1���ظ�ģ�� �ǿշ��ص�ָ��ģ��
    **/
    public function gMsg($txt=null,$aUrl=null)
    {
        die($txt);
        if(!$aUrl){//Ϊ��ֱ�ӷ���
            $aUrl=@$_SERVER['HTTP_REFERER'];
        }elseif($aUrl==2){//������ת
            $aUrl=null;
        }elseif($aUrl==1){//Ϊ1���ظ�ģ��
            $aUrl="?gcms=".doget('gcms');
        }else{//�ǿշ��ص�ָ��ģ��
            $aUrl=str_replace('{#gcms}','gcms='.$this->gcms,$aUrl);
            $aUrl="{$aUrl}";
        }
        $tmy['txt']=&$txt;
        $tmy['url']=&$aUrl;
        self::regVar($tmy);
        parent::showView($this->cfg['sys_webtpl'].'index_msg.tpl');exit;
    }


    /**
     * �������԰�-�������ͳ��쳣
     * ���Smarty�������ķ���
     *
     * @param string $str ��ʶ
     * @return void
    **/
    public function gLang($str,$reg=null)
    {
        self::regVar($str,'FDmsg');
        is_array($reg) && self::regVar($reg,'FDreg');
    }
}

/**
 * ����ļ�
 * �쳣�����п����Զ����쳣���
**/
try{
    require_once('../router.php');
    Fend_Acl::Factory()->setAcl($aclcfg);
    isset($aclmod) && Fend_Acl::Factory()->setModule($aclmod);
    Fend_Acl::Factory()->run($mods);

}catch(Fend_Exception $e){
    $e->ShowTry(defined('FD_DEBUG') ? FD_DEBUG : 0);
}

?>