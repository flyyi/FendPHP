<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2010 Gimoo Inc. (http://fend.gimoo.net)
 *
 * �ʿ��ѯ��������
 * �����ѯ�ͱ༭FendDB(FDB)������
 *
 * ------------------------
 *  д�����ݿ�:
 *  $fp=new FDB;
 *  $fp->open('dict.xdb',1);
 *  $fp->put('����','0');
 *  $fp->put('����','0');
 * ------------------------
 *  ���ò�ѯ:
 *  $fp=new FDB;
 *  $fp->open('dict.xdb');
 *  $buf=$fp->get('��ֽͼ��');
 *  //D:\soft_info\php5.24\php.exe make_xdb_file.php t.xdb db.txt
 * ------------------------
 *
 * @Package GimooFend
 * @Support http://bbs.eduu.com
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: Db.php 4 2011-12-29 11:01:08Z gimoo $
**/

define ('FDB_KEY_MAXLEN', 0xf0);//������󳤶�
define ('FDB_INDEX_HASH_BASE', 0xf422f);//�����������-Hash���ݼ���Ļ���, ����ʹ��Ĭ��ֵ. [h = ((h << 5) + h) ^ c]
define ('FDB_INDEX_HASH_PRIME', 2047);//������������-��ģ�Ļ���, ����ѡһ��������ԼΪ�ܼ�¼����1/10����.
define ('FDB_INDEX_LEN', 40);//�ļ�ͷ����
define ('FDB_TAGNAME', 'FDB');//�ļ�������
define ('FDB_VERSION', '1.0' );//����汾
define ('FDB_FLOAT_CHECK', 3.14);
//define ('FDB_SYSLEN', 32);//32λϵͳ

class Fend_Fcws_Db
{
    private $_fp=false;//���ݿ�ָ��
    private $_fmode=0;//�������ݿⷽʽ,0Ϊֻ��,1Ϊֻд
    private $_hbase=FDB_INDEX_HASH_BASE;
    private $_hprime=FDB_INDEX_HASH_PRIME;
    private $_version=34;//�ʵ�汾
    protected $_fsize=0;//�ʵ��ļ���С(����ֽ���)
    protected $_keymin=FDB_KEY_MAXLEN;//�ʵ��ļ��н�����С����(���ļ�����)
    protected $_keymax=0;//�ʵ��ļ��н�����da����(���ļ�����)

    /**
     * �����ݿ�
     *
     * @param  string $fpath �ļ�����·��
     * @param  string $wp �򿪷�ʽ,0Ϊֻ����ʽ,1Ϊд��ʽ
     * @return void
    **/
    public function open($fpath,$fm=0)
    {
        if($fm){
            $fp = @fopen($fpath, 'wb+');
            //д���ļ�ͷ
            fseek($fp, 0, SEEK_SET);
            fwrite($fp, pack('a3CiiIIIfa12', FDB_TAGNAME, $this->_version,$this->_hbase, $this->_hprime, 0, $this->_keymin, $this->_keymax,  FDB_FLOAT_CHECK, ''), FDB_INDEX_LEN);
            $this->_fsize = FDB_INDEX_LEN + 8 * $this->_hprime;
            flock($fp, LOCK_EX);
        }else{
            $fp = @fopen($fpath, 'rb');
        }

        //����ļ��Ƿ�ΪFDB�ļ�
        if(!$fm && !$this->_getHeader($fp)){
            fclose($fp);
            trigger_error("FDB::open(".basename($fpath)."), invalid FDB format.", E_USER_WARNING);
            return false;
        }
        $this->_fp=&$fp;
        $this->_fmode=&$fm;
    }

    /**
     * ��ȡKEY��ֵ
     * �����м�ⷵ��true or false
     *
     * @param  string $key һ���ַ���
     * @return string
    **/
    public function get($key)
    {
        //���key�Ƿ�Ϸ�
        $_len=strlen($key);
        if($_len==0 || $_len>FDB_KEY_MAXLEN) return false;
        $rs=$this->_get($key,$_len);
        if(!isset($rs['vlen']) || $rs['vlen'] == 0) return false;
        return $rs['value'];
    }

    /**
     * �������ݿ�
     * ��ε��ÿ����б����м�¼��
     *
     * @param  string $key һ���ַ���
     * @return bool   0|1
    **/
    public function next()
    {
        static $_stack=array(),$_index=-1;
        if(!($_tmp=array_pop($_stack))){
            do{
                if(++$_index >= $this->_hprime) break;

                fseek($this->_fp,$_index * 8 + FDB_INDEX_LEN,SEEK_SET);
                $buf=fread($this->_fp, 8);
                if(strlen($buf)!= 8){$_tmp=false;break;}

                $_tmp=unpack('Ioff/Ilen',$buf);
            }while($_tmp['len']==0);
        }

        //����Ƿ��ȡ�ѽ���
        if(!$_tmp || $_tmp['len']==0) return false;

        //��ȡ��¼��
        $rs=$this->_getTree($_tmp['off'],$_tmp['len']);

        //����Ƿ������ڵ�
        if($rs['llen'] != 0){
            array_push($_stack,array('off'=>$rs['loff'],'len'=>$rs['llen']));
        }

        //����Ƿ�����ҽڵ�
        if($rs['rlen'] != 0){
            array_push($_stack,array('off'=>$rs['roff'],'len'=>$rs['rlen']));
        }

        //�������: WORD\tTF\tIDF\tATTR\n
        //$rs['value']=unpack('ftf/fidf/Cflag/a3attr',$rs['value']);
        return $rs;
    }

    /**
     * д��ʿ���Ϣ
     * ���ʿ���д�����Ϣ
     *
     * @param  string $key   һ���ַ���
     * @param  string $value ��Ӧ��ֵ
     * @return bool   0|1
    **/
    public function put($key, $value)
    {
        //����Ƿ��п�д�뻷��
        if(!$this->_fp || !$this->_fmode){
            trigger_error("FDB::put(), null db handler or readonly.", E_USER_WARNING); return false;
        }

        //��֤�����Ƿ�Ϸ�
        $klen=strlen($key);
        $vlen=strlen($value);
        if(!$klen || $klen > FDB_KEY_MAXLEN) return false;

        $klen<$this->_keymin && $this->_keymin=$klen;
        $klen>$this->_keymax && $this->_keymax=$klen;

        //��������Ƿ����
        $rs=$this->_get($key,$klen);
        if(isset($rs['vlen']) && ($vlen <= $rs['vlen'])){

            if($vlen > 0){//���¼�¼��
                fseek($this->_fp, $rs['voff'], SEEK_SET);
                fwrite($this->_fp, $value, $vlen);
            }

            if ($vlen < $rs['vlen']){
                $newlen = $rs['len'] + $vlen - $rs['vlen'];
                fseek($this->_fp, $rs['ioff'] + 4, SEEK_SET);
                fwrite($this->_fp, pack('I', $newlen), 4);
            }
            return true;
        }

        //�������ݽṹ
        $new = array('loff' => 0, 'llen' => 0, 'roff' => 0, 'rlen' => 0);
        if(isset($rs['vlen'])){
            $new['loff'] = $rs['loff'];
            $new['llen'] = $rs['llen'];
            $new['roff'] = $rs['roff'];
            $new['rlen'] = $rs['rlen'];
        }
        $buf=pack('IIIIC', $new['loff'], $new['llen'], $new['roff'], $new['rlen'], $klen).$key.$value;
        $len=$klen + $vlen + 17;

        //д�����ݿ�
        $off=$this->_fsize;
        fseek($this->_fp, $off, SEEK_SET);
        fwrite($this->_fp, $buf, $len);
        $this->_fsize += $len;

        //��������
        fseek($this->_fp, $rs['ioff'], SEEK_SET);
        fwrite($this->_fp, pack('II', $off, $len), 8);
        return true;
    }

    /**
     * �����Ż���ṹ
     * ��дͷ��Ϣ,��֤�ļ�����ȷ��
     *
     * @return void
    **/
    public function optimize()
    {
        if(!$this->_fp || !$this->_fmode) return false;
        static $_cmpfunc=false;

        //��ȡ���������
        $i=-1;
        if($i<0 || $i>=$this->_hprime){
            $i=0;$j=$this->_hprime;
        }else{
            $j=$i+1;
        }

        //�ؽ�����
        while($i<$j){
            $ioff=$i++ * 8 + FDB_INDEX_LEN;

            //ȡ�����������ؽ�λ��
            $_syncTree=array();
            $this->_loadTree($ioff,$_syncTree);
            $count=count($_syncTree);
            if($count < 3) continue;

            if($_cmpfunc == false) $_cmpfunc = create_function('$a,$b', 'return strcmp(@$a[key],@$b[key]);');
            usort($_syncTree, $_cmpfunc);
            $this->_resetTree($_syncTree,$ioff, 0, $count - 1);
            unset($_syncTree);
        }
        fseek($this->_fp,12,SEEK_SET);
        fwrite($this->_fp, pack('III',$this->_fsize,$this->_keymin,$this->_keymax), 12);
        flock($this->_fp,LOCK_UN);
    }

    /**
     * �ر����ݿ�����
     * PHP5���ϰ汾ͨ����__destruct�Զ�����,�����Լ�����
     *
     * @return void
    **/
    public function close()
    {
        if(!$this->_fp) return;
        fclose($this->_fp);
        $this->_fp=false;
    }

    /**
     * ��ȡ�汾��Ϣ
     *
     * @return string
    **/
    public function version()
    {
        return sprintf("%s/%d.%d", FDB_VERSION, ($this->_version >> 5), ($this->_version & 0x1f));
    }

    /**
     * ����KEYȡ�����value
     *
     * @param  string $key һ���ַ���
     * @return string
    **/
    private function _get(&$key,&$len)
    {
        $ioff=($this->_hprime > 1 ? $this->_getIndex($key,$len) : 0) * 8 + FDB_INDEX_LEN;
        fseek($this->_fp, $ioff, SEEK_SET);
        $buf=fread($this->_fp, 8);

        if(strlen($buf)==8) $_tmp=unpack('Ioff/Ilen',$buf);
        else $_tmp=array('off'=>0,'len'=>0);
        return $this->_getTree($_tmp['off'], $_tmp['len'], $ioff, $key);
    }

    /**
     * ����KEYȡ�����value
     *
     * @param  string $key һ���ַ���
     * @return string
    **/
    private function _getIndex(&$key,$l)
    {
        $h=$this->_hbase;
        while ($l--){
            $h += ($h << 5);
            $h ^= ord($key[$l]);
            $h &= 0x7fffffff;
        }
        return ($h % $this->_hprime);
    }

    /**
     * ��ȡ�ļ�ͷ��Ϣ
     *
     * @param  string $key һ���ַ���
     * @return string
    **/
    private function _getHeader(&$fp)
    {
        fseek($fp, 0, SEEK_SET);
        $buf=fread($fp, FDB_INDEX_LEN);
        if(strlen($buf) !== FDB_INDEX_LEN) return false;
        $rs=unpack('a3tag/Cver/Ibase/Iprime/Ifsize/Ikeymin/Ikeymax/fcheck/a12reversed', $buf);
        if($rs['tag'] != FDB_TAGNAME) return false;

        //��ȡ�ļ���Ϣ,����ļ��Ƿ���
        $fs=fstat($fp);
        if($fs['size'] != $rs['fsize']) return false;
        $this->_hbase = $rs['base'];
        $this->_hprime = $rs['prime'];
        $this->_version = $rs['ver'];
        $this->_fsize = $rs['fsize'];
        $this->_keymax = $rs['keymax'];
        $this->_keymin = $rs['keymin'];
        return true;
    }

    /**
     * �ݹ�Ķ�ȡ��¼����
     *
     * @param  int $off  ��ʼλ��
     * @param  int $len  �ɶ�ȡ�ĳ���
     * @param  int $ioff ������ʼλ��
     * @param  string $key  ������
     * @return array
    **/
    private function _getTree(&$off,&$len,$ioff=0,$key=null)
    {
        if($len==0) return array('ioff'=>$ioff);

        //��ȡ��¼��
        fseek($this->_fp,$off,SEEK_SET);
        $rlen = FDB_KEY_MAXLEN + 17; $rlen>$len && $rlen=$len;

        $buf = fread($this->_fp, $rlen);
        $rs = unpack('Iloff/Illen/Iroff/Irlen/Cklen', substr($buf, 0, 17));

        //У��key�Ƿ��ȡ����
        $_key = substr($buf, 17, $rs['klen']);
        $cmp = $key ? strcmp($key, $_key) : 0;

        unset($buf);
        if($cmp > 0){// --> right
            return $this->_getTree($rs['roff'], $rs['rlen'], $off + 8, $key);
        }elseif($cmp < 0){// <-- left
            return $this->_getTree($rs['loff'], $rs['llen'], $off, $key);
        }else{//���ؽ������
            $rs['ioff']=$ioff;
            $rs['off']=$off;
            $rs['len']=$len;
            $rs['voff']=$off+17+$rs['klen'];
            $rs['vlen']=$len-17-$rs['klen'];
            $rs['key']=&$_key;
            fseek($this->_fp,$rs['voff'],SEEK_SET);
            $rs['value']=fread($this->_fp,$rs['vlen']);
            //$rs['value']=unpack('fss/fcc/Ctt/a3ds',$rs['value']);
            return $rs;
        }
    }

    /**
     * �ݹ�Ķ�ȡ˳���ȡ����
     *
     * @param  int $ioff ��ʼλ��
     * @return array
    **/
    private function _loadTree($ioff,&$_syncTree)
    {
        fseek($this->_fp,$ioff,SEEK_SET);
        $buf=fread($this->_fp,8);
        if(strlen($buf)!=8) return;

        $tmp=unpack('Ioff/Ilen',$buf);

        if($tmp['len']==0) return;
        fseek($this->_fp,$tmp['off'],SEEK_SET);

        $rlen = FDB_KEY_MAXLEN + 17;
        if($rlen > $tmp['len']) $rlen = $tmp['len'];
        $buf = fread($this->_fp, $rlen);

        $rec = unpack('Iloff/Illen/Iroff/Irlen/Cklen', substr($buf, 0, 17));
        $rec['off'] = $tmp['off'];
        $rec['len'] = $tmp['len'];
        $rec['key'] = substr($buf, 17, $rec['klen']);
        $_syncTree[] = $rec;
        unset($buf);

        if($rec['llen'] != 0) $this->_loadTree($tmp['off'],$_syncTree);
        if($rec['rlen'] != 0) $this->_loadTree($tmp['off'] + 8,$_syncTree);
    }

    /**
     * ���½��������ṹ
     *
     * @param  int $ioff ����ƫ����
     * @param  int $low
     * @param  int $high
     * @return void
    **/
    private function _resetTree(&$_syncTree,$ioff, $low, $high)
    {
        if($low<=$high){
            $mid=($low+$high)>>1;
            $node=$_syncTree[$mid];
            $buf=pack('II',$node['off'],$node['len']);

            $this->_resetTree($_syncTree,$node['off'], $low, $mid - 1);
            $this->_resetTree($_syncTree,$node['off'] + 8, $mid + 1, $high);
        }else{
            $buf=pack('II', 0, 0);
        }
        fseek($this->_fp, $ioff, SEEK_SET);
        fwrite($this->_fp, $buf, 8);
    }

    /**
     * �Զ��رռ����ٱ���
     *
     * @return void
    **/
    public function __destruct()
    {
        $this->optimize();//�Զ��Ż�
        $this->close();
    }
}
?>