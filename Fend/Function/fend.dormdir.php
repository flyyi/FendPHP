<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://Fend.Gimoo.Net)
 *
 * ɾ��ָ��Ŀ¼
 * ע��: �⽫����Ŀ¼�����д��ڵ���Ŀ¼���ļ�,����ִ��
 *
 * @param string $sdir һ��Ŀ¼������·��
 * @return boolen falseִ��ʧ��,true��ʾ����ɹ�
 * @--------------------------------
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: fend.dormdir.php 4 2011-12-29 11:01:08Z gimoo $
**/

function doRmDir($sdir)
{
    if(!is_dir($sdir)) return true;
    $cwd=getcwd();
    if(!chdir($sdir)) return false;

    $fs=dir('./');
    while(false !==($entry=$fs->read())){
        if($entry=='.' || $entry=='..') continue;
        if(is_dir($entry)){
            if(!doRmDir($entry)) return false;
        }else{
            if(!unlink($entry)) return false;
        }
    }
    $fs->close();

    if(!chdir($cwd)) return false;
    if(!rmdir($sdir)) return false;
    return true;
}
?>