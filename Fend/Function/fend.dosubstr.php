<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Eduu Inc. (http://www.eduu.com)
 *
 * �ַ�����ȡ
 * ��һ���ַ�����ָ������ʼ�ַ���ʼ��ȡ��ָ���Ľ����ַ�
 *
 * @param string $str     �ַ���
 * @param string $b_start ��ʼ��
 * @param string $b_end   ������
 * @return string|void
 * @--------------------------------
 * @Package GimooFend
 * @Support http://bbs.eduu.com
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: fend.dosubstr.php 4 2011-12-29 11:01:08Z gimoo $
**/

function doSubStr($str,$b_start,$b_end)
{
    //������ʼλ��
    $s_pos=stripos($str,$b_start);
    if(false===$s_pos) return null;
    $s_pos+=strlen($b_start);

    //�������λ��
    $e_pos=stripos($str,$b_end,$s_pos);
    if(false===$e_pos) return null;

    return substr($str,$s_pos,$e_pos-$s_pos);
}

?>