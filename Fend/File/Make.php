<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://fend.gimoo.net)
 *
 * �����ļ����ļ���
 *
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: Make.php 4 2011-12-29 11:01:08Z gimoo $
**/

Fend_Func::Init('doPath');//ע�ắ��
class Fend_File_Make
{
    static $froot=NULL;//������Ŀ¼
    static $fmod=0775;//�����ļ���Ȩ��
    static function Init($root,$fmod=0755)
    {
        self::$froot=$root;//ֱ�ӻ�ȡ����
        self::$fmod=$fmod;//ֱ�ӻ�ȡ����
    }

    //����ϵ��Ŀ¼-�޻���--����ȫ
    static function PutDir($fdir,$root=NULL)
    {
        $fdir=doPath($fdir);
        empty($root) && $root=self::$froot;
        if(is_dir($root.$fdir)) return TRUE;//Ŀ¼����ֱ�ӷ���
        $fdir=explode('/',$fdir);
        $_tem=NULL;
        if(!$fdir[0] || false!==strpos($fdir[0],':')){
            $fdir[0].='/';
            $_tem=$fdir[0];
            unset($fdir[0]);
        }
        foreach($fdir as $v){
            if(!$v) continue;
            $_tem.=$v.'/';
            if(is_dir($root.$_tem)) continue;
            if(!mkdir(doPath($root.$_tem),self::$fmod)){
                return FALSE;
            }
        }
        return TRUE;
    }

    //�����ļ�
    static function putFile($fpath,$fbody)
    {
        if(!empty(self::$froot) && !is_writable(self::$froot)) return FALSE ;//����Ŀ¼����д
        $dpath=dirname($fpath);
        if($dpath!='.' && $dpath!='..' && $dpath!='\\' && $dpath!='/') self::PutDir($dpath);//��Ŀ¼����
        return @file_put_contents(self::$froot.$fpath,$fbody);
    }

    //ɾ��һ���ļ�
    static function DelFile($fpath)
    {
        if(is_file($fpath)) return @unlink($fpath);
        return true;
    }
}




?>