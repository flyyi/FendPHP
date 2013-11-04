<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://fend.gimoo.net)
 *
 * ȡ���ļ���MIME����
 *
 *
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: Type.php 4 2011-12-29 11:01:08Z gimoo $
**/

class Fend_File_Type
{
    public $magFile=NULL;//Mime�ļ���ַ
    public $ft=NULL;//����

    //���캯��-�Զ�ִ��
    public function __construct()
    {
        $this->magFile=dirname(__FILE__).'/magic/magic';//����Mime�ļ�·��
    }

    //ȡ���ļ�
    public function getFileType($fname)
    {
        !$this->ft && $this->ft=new finfo(FILEINFO_MIME, $this->magFile);
        $ctype=$this->ft->file($fname);
        $ctype=str_replace(';',' ',$ctype);
        $ctype=explode(' ',$ctype);
        return $ctype[0];
    }
}




?>