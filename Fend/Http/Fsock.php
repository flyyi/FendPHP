<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Gimoo Inc. (http://fend.gimoo.net)
 *
 * FsockԶ��ͨѶģ��
 * ����: ����α���HEADERͷ��������,��ʵ��ģ������˷���վ��
 *
 * Example: Fend_Http_Fsock::Factory('time:5','size:30','loop:1','charset:gb2312')->get('http://fend.gimoo.net');
 * @Package GimooFend
 * @Support http://bbs.gimoo.net
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: Fsock.php 4 2011-12-29 11:01:08Z gimoo $
**/

class Fend_Http_Fsock
{
    private $_cfg=array(
        'time'=>30,//���ӳ�ʱ
        'agent'=>'Mozilla/4.0 (Compatible; Msie 6.0; Windows Nt 5.1; Sv1; Mozilla/4.0(Maxthon)',//α�����
        'size'=>10000,//���ӳ�ʱ(��λKB)
        'method'=>'GET',//��������--GET/HEAD/ POST
        'loop'=>0,//�����ض������,0Ϊ�ر�
        'out'=>'body',//���÷��ؽ������,head,body,tohead
        'referer'=>null,//��������URL��ַ
        'ip'=>null,//��������/������IP��ַ
        'charset'=>null,//���ؽ���ַ���
    );//fsock����

    public static $in;
    public static function factory()
    {
        if(null===self::$in) self::$in = new self();

        //��ȡ��������val('time:30','agent:get')
        if(func_num_args()>0){
            $_cfg=&self::$in->_cfg;
            $args=func_get_args();
            foreach($args as $v){
                $v=explode(':',$v,2);
                $_cfg[$v[0]]=$v[1];
            }
        }
        return self::$in;
    }

    /**
     * ��ȡҳ����Ϣ
     *
     * @param  string  $url Url���ӵ�ַ
     * @return array|string
    **/
    public function get($url)
    {
        $body=self::getFile($url);//����sock����
        !empty($this->_cfg['charset']) && $body['body'] && $body['charset']=self::ckCharset($body['body']);
        return isset($body[$this->_cfg['out']]) ? $body[$this->_cfg['out']] : $body;
    }

    //����Ƿ��Ǽ��Զ���ļ�����
    public function getFile($url)
    {
        $_data=array('body'=>'','head'=>array(),'tohead'=>null);

        //ȡ�ø�ʽ��URL�õ� HOST REQUIST
        $url=str_replace(' ','%20',$url);
        $_url=@parse_url($url);
        if(empty($_url['scheme']) || empty($_url['host'])) return $_data;//�Ƿ���ַ
        switch ($_url['scheme']) {
            case 'https':
                $_url['scpact'] = 'ssl://';
                !isset($_url['port']) && $_url['port'] = 443;
                break;
            case 'http':
            default:
                $_url['scpact'] = '';
                !isset($_url['port']) && $_url['port'] = 80;
        }
        !isset($_url['path']) && $_url['path']='/';
        $_url['uri']= empty($_url['query']) ? $_url['path'] : $_url['path'].'?'.$_url['query'];
        $_url['url']=$_url['scheme'].'://'.$_url['host'];
        $_url['ip']=empty($this->_cfg['ip']) ? $_url['host'] : $this->_cfg['ip'];

        //����Headerͷ��Ϣ
        $http_header = null;
        $http_header.= "{$this->_cfg['method']} {$_url['uri']} HTTP/1.0\r\n";
        $http_header.= "Host: {$_url['host']}\r\n";
        isset($this->_cfg['referer']) && $http_header.= "Referer: {$this->_cfg['referer']} \r\n";
        $http_header.= "Connection: close\r\n";
        $http_header.= "Cache-Control: no-cache\r\n";
        $http_header.= "User-Agent: {$this->_cfg['agent']}\r\n";
        $http_header.= "\r\n";
        $_data['tohead']=&$http_header;//����ͷ��Ϣ

        //fsockopen(��������,�˿�,�������,������ϸ��Ϣ,��ʱʱ����)
        $fp=@fsockopen($_url['scpact'].$_url['ip'], $_url['port'], $errno, $errstr, $this->_cfg['time']);
        if(!$fp){ $_data['msg']=$errstr;return $_data; }//����ʧ��
        stream_set_blocking($fp, true);//����Ϊ����ģʽ����������
        socket_set_timeout($fp,$this->_cfg['time']);//���ó�ʱʱ��

        @fwrite($fp, $http_header);//�Ѿ�������д��ͷ��Ϣ//$CRLF="\x0d"."\x0a"."\x0d"."\x0a";
        $thd=true;
        //ѭ����ȡ�ļ��ֽ���
        $bodysize=$this->_cfg['size']*1024;//�õ��ֽ���
        $info = stream_get_meta_data($fp);
        while( !feof($fp) && (!$info['timed_out']) ){
            $tmp_stream=fgets($fp,128);//���ļ��ֽ���
            $info = stream_get_meta_data($fp);
            //�Ƿ���ͷ
            if($thd){

                //��ȡHTTP�Ĵ𸴴���
                if(!isset($_data['head']['http'])){
                    $_data['head']['http']=trim($tmp_stream);
                    //$_data['head']['url']=$url;
                    continue;
                }

                //����Ƿ��ȡͷ��Ϣ����
                if($tmp_stream == "\r\n"){
                    if(false===stripos($_data['head']['http'],'200') || $this->_cfg['out']=='head') break;
                    $thd=false;continue;
                }

                //����ͷ��Ϣ
                if(!preg_match('/([^:]+):(.*)/i',$tmp_stream,$tmp_hd)){
                    $_data[]=trim($tmp_stream);
                    continue;//ȡ��HEADER ͷ����
                }

                $tmp_hd[1]=strtolower(trim($tmp_hd[1]));
                $tmp_hd[2]=trim($tmp_hd[2]);
                $_data['head'][$tmp_hd[1]]=$tmp_hd[2];//����ͷ��Ϣ�������

                if($this->_cfg['loop']<=0) continue;//��ת���

                //����Ƿ�ת��
                if($tmp_hd[1]=='location'){
                    --$this->_cfg['loop'];//�������
                    if(false!==stripos($tmp_hd[2],'cncmax.cn')) break;
                    if(substr($tmp_hd[2],0,7) != 'http://'){
                        if(substr($tmp_hd[2],0,1) == '/'){
                            $tmp_hd[2]=$_url['url'].$tmp_hd[2];//---/web/index.html
                        }else{
                            $tmp_hd[2]=$_url['url'].substr($_url['path'],0,strrpos($_url['path'],'/')).'/'.$tmp_hd[2];//--web/index.html
                        }
                    }
                    @fclose($fp);//�ر�����
                    $this->_cfg['referer']=&$url;
                    $this->_cfg['ip']=null;
                    $_data=$this->getFile($tmp_hd[2]);//��ʼ��ת���еڶ��γ�������
                    break;
                }
            }else{
                if($bodysize<=0) break;
                $bodysize=$bodysize-strlen($tmp_stream);
                $_data['body'].=$tmp_stream;
            }
        }
        @fclose($fp);
        return $_data;
    }

    //����ҳ��ȡ�������Ͳ�����ת�� <meta http-equiv="content-type" content="text/html; charset=gb2312">
    private function ckCharset(&$str)
    {
        //����ҳ��ȡ����
        if(preg_match('/<meta[^>]+(?:charset=|encode=)([a-z0-9\-]+)[\'"]/i',$str,$_code)){
            $_code=strtolower(trim($_code[1]));
        }else{
            $_code='';
        }

        //��δ��ȡ������������UTF8/BGK/GB2312ʱ�����ַ�����֤,ȷ���ַ�������ȷ��
        if(empty($_code)){
            //$_code='iso-8859-1';
            $len=strlen($str);
            for($i=0;$i<$len;++$i){
                $_c1=ord($str[$i]);
                if($_c1<0x80){//���ֽ�
                    continue;
                }else{//�Ѿ��ҵ����ֽ��ַ�
                    $_c2=ord($str[++$i]);
                    $_c3=++$i<$len ? ord($str[$i]) : 0x00;
                    if($_c1>=0xE0 && $_c1<=0xEF && $_c3>=0x80 && $_c3<=0xBF && $_c3>=0x80 && $_c3<=0xBF){
                        $_code='utf-8';
                    }elseif($_c1>=0x81 && $_c1<=0xFE && $_c2>=0x40 && $_c2<=0xFE){
                        $_code='gbk';
                    }
                    break;
                }
            }
        }

        if($this->_cfg['charset']==$_code || $_code=='iso-8859-1' || empty($_code)){
            return $_code;
        }else{
            $str=mb_convert_encoding($str,$this->_cfg['charset'],$_code);
        }
        return $_code;
    }
}

?>