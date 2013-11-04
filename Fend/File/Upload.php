<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://fend.gimoo.net)
 *
 * ����ͼƬ
 *
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: Upload.php 4 2011-12-29 11:01:08Z gimoo $
**/

class Fend_File_Upload
{
	//--------------------
	public $fmsg=array();
	public $upfsize=1048576;//�����ϴ��ļ���СС�ڵ���1Mb
	public $upftype='gif|jpe|jpg|jpeg|png';//�����ϴ���ʽ
	public $upfroot=NULL;//�ļ���Ÿ�·��
	public $upfsons=NULL;//�ļ������Ŀ¼(<Y/m/d>)
	public $upfname='<rand.5>';//�ļ�����-���ڸ����Ѵ��ڵ�ͼƬ(<rand.5><ucid>)

	//�����ϴ�
	public function StartUp($str)
	{
		if(!isset($_FILES[$str])){
			return self::_Echo();//û���ϴ�
		}elseif(!is_uploaded_file($_FILES[$str]['tmp_name'])){
			return self::_Echo($_FILES[$str]['error']);//û���ϴ�
		}

		self::toExt();//��ȡ�ļ���չ��
		self::toInfo($str);//��ȡ�ϴ��ļ������Ϣ

		//��С����
		if($this->fmsg['size'] > $this->upfsize){
			return self::_Echo('102');//������С��
		}

		//��չ������
		if(!in_array(strtolower($this->fmsg['fext']),$this->upftype)){
			return self::_Echo('100');//��������ļ���չ��
		}

		$this->upfname=self::toFileName($this->upfname);//����һ��������ļ�����
		if(@move_uploaded_file($this->fmsg['tmp_name'],$this->upfname)){
			self::_Echo('0');
			return $this->fmsg['fpath'];
		}else{
			return self::_Echo('101');
		}
	}

	//��ȡ���ظ���������
	public function getBlob($str)
	{
		if(!isset($_FILES[$str])){
			return self::_Echo();//û���ϴ�
		}elseif(!is_uploaded_file($_FILES[$str]['tmp_name'])){
			return self::_Echo($_FILES[$str]['error']);//û���ϴ�
		}

		self::toExt();
		self::toInfo($str);//��ȡ�ϴ��ļ������Ϣ

		//��С����
		if($this->fmsg['size'] > $this->upfsize){
			return self::_Echo('102');//������С��
		}

		//��չ������
		if(!in_array(strtolower($this->fmsg['fext']),$this->upftype)){
			return self::_Echo('100');//��������ļ���չ��
		}

		self::_Echo();//�ϴ��ɹ�
		$this->fmsg['fblob']=@file_get_contents($this->fmsg['tmp_name']);
		return $this->fmsg['fblob'];
	}

	private function toExt()
	{
		//fomart URL
		if(!function_exists('_sPath')){
			function _sPath($url){
				$url=preg_replace('/[\/\\\\]+/','/',$url);
				return $url;
			}
		}
		$this->upfroot=_sPath($this->upfroot);
		$this->upfsons=_sPath($this->upfsons);
		$this->upfname=_sPath($this->upfname);
		//���������ϴ���չ����׺
		if(!is_array($this->upftype)){
			$this->upftype=str_replace(',','|',$this->upftype);
			$this->upftype=explode('|',$this->upftype);
		}

	}

	//ȡ���ϴ��ļ���Ϣ
	private function toInfo($str)
	{
		$this->fmsg=$_FILES[$str];
		$this->fmsg['name']=strtolower($this->fmsg['name']);
		$this->fmsg['fext']=pathinfo($this->fmsg['name'],PATHINFO_EXTENSION);
	}

	//��ȡ�ļ�·��
	private function toFileName($str)
	{
		//ת��·����ʽ
		!$str && $str=$this->fmsg['name'];
		$str=self::toPregPath($str);
		$this->upfsons=self::toPregPath($this->upfsons);//ת��Ϊ/
		$this->upfroot=self::toPregPath($this->upfroot);

		//��ʽ����Ŀ¼·��
		!empty($this->upfsons) && $this->upfsons=self::toPregBack($this->upfsons);
		!empty($str) && $str=self::toPregBack($str);

		if(!empty($this->upfsons)){
			$str=$this->upfsons.'/'.$str;
			$str=self::toPregPath($str);
		}

		//����·������ʱ,��Ⲣ���ϱ�Ҫ��б��
		if(!empty($this->upfroot)){
			if(substr($this->upfroot,-1)=='/'){
				$str{0}=='/' && $str=substr($str,1);
			}else{
				$str{0}!='/' && $str='/'.$str;
			}
		}
		self::toMakeDir($str);//��������Ŀ¼
		!pathinfo($str,PATHINFO_EXTENSION) && $str.='.'.$this->fmsg['fext'];

		$this->fmsg['fpath']=$str;
		$this->fmsg['tpath']=$this->upfroot;
		$this->fmsg['ftpath']=$this->upfroot.$str;
		return $this->fmsg['ftpath'];
	}

	//�ϴ��ļ������з����Ĵ�����Ϣ
	private function _Echo($str=NULL)
	{
		switch($str){
			case '0':
				$Ful='�ϴ��ɹ�';
				break;
			case '1':
				//$Ful="�ϴ����ļ������� php.ini �� upload_max_filesize ѡ�����Ƶ�ֵ.";
				$Ful="�ϴ����ļ������˷���������ѡ�����Ƶ�ֵ.";
				break;
			case '2':
				$Ful='�ϴ��ļ��Ĵ�С������HTML����MAX_FILE_SIZEѡ��ָ����ֵ��';
				break;
			case '3':
				$Ful='�ļ�������,ֻ�в��ֱ��ϴ�';
				break;
			case '4':
				$Ful='û���κ��ļ����ϴ�';
				break;
			case '6':
				$Ful='��ʱ�ļ�����ʧ,����ϵͳ����Ŀ¼����д';//�Ҳ�����ʱ�ļ���
				break;
			case '7':
				$Ful='�ļ�д��ʧ��';
				break;
			case '100':
				$Ful='�ϴ�ʧ��,�ļ����Ͳ�����';
				break;
			case '101':
				$Ful="�����ļ�ʧ��!";
				break;
			case '102':
				$this->upfsize=ceil($this->upfsize/1024);
				$Ful="�ļ���С���ܳ�����{$this->upfsize}Kb.";
				break;
			case '103':
				$Ful="��Ŀ¼����д�򲻴���";
				break;
			case '104':
				$Ful="�ļ����Ʋ���Ϊ��";
				break;
			default:
				$Ful='û���κο��ϴ�����';
				break;
		}
		$this->fmsg['msg']=$Ful;
		return FALSE;
	}

	private function toPregBack($str)
	{
		//�����滻����
		if(!function_exists('_toPregBack')){
			function _toPregBack($par){
				if(!$par[1]){
					return NULL;
				}elseif(FALSE===stripos($par[1],'rand')){
					//·������
					$par[1]=date(trim($par[1]),time());
				}else{
					//�ļ����ƴ���
					$length=pathinfo($par[1],PATHINFO_EXTENSION);
					if($length=='id'){
						return uniqid();
					}
					$length=(int)$length;
					$par[1] = NULL;
					$chars = '0123456789abcdefghigklmnopqrstuvwxyz';
					$max = strlen($chars) - 1;
					for($i = 0; $i < $length; $i++){
						$par[1] .= $chars{mt_rand(0, $max)};
					}
				}
				return $par[1];
			}
		}
		return preg_replace_callback('/<([^<>]*)>/i','_toPregBack',$str);
	}

	//ת��·��
	private function toPregPath($str=NULL)
	{
		//DIRECTORY_SEPARATOR
		return preg_replace('/[\/]+/','/',$str);
	}

	//����·���������Ŀ¼
	private function toMakeDir($fpath)
	{
		$DIrPar=$this->upfroot;
		$fpath=dirname($fpath);
		if(is_dir($DIrPar.$fpath)){return TRUE;}
		$fpath=explode('/',$fpath);

		//ѭ������Ŀ¼
		//print_r($fpath);
		substr($DIrPar,-1)=='/' && $DIrPar=substr($DIrPar,0,-1);
		foreach($fpath as $v){
			if(!$v) continue;
			$DIrPar.='/'.$v;
			if(is_dir($DIrPar)) continue;
			if(@mkdir($DIrPar,0777)){
				continue;
			}else{
				self::_Echo('103');
				break;
			}
		}
	}

}



/*
���������:
$up->fmsg['msg']	//��ʾ��Ϣ
$up->fmsg['fpath']	//�ļ���ַ,��������·��
$up->fmsg['tpath']	//��·��,�������ļ���ַ
$up->fmsg['ftpath']	//�ļ�ȫ·��

$up->fmsg['name']	//ԭ�ϴ��ļ�����
$up->fmsg['fext']	//�ļ���׺
$up->fmsg['type']	//�ļ�����
$up->fmsg['tmp_name']	//������ʱ�ļ�·��
$up->fmsg['error']	//�ϴ��������
$up->fmsg['size']	//�ϴ��ļ���С


echo '<pre>';
$up=new UpFile;
$up->upfroot='D:\sss2008';
$up->upfname='<rand.5>';
$up->upfsons='';
echo $up->StartUp('mfile');
print_r($up->fmsg);

<form method="post" class="Pform" enctype="multipart/form-data">
<input type="file" name="mfile" />
<input type='submit' class='PSub' value=' �� �� �� �� '>
</form>

*/
?>
