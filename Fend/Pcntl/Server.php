<?php
/**
 * Fend Framework
 * [Gimoo!] (C)2006-2009 Eduu Inc. (http://www.eduu.com)
 *
 * ��Ϣ������
 * �ػ�����ģʽ����
 *
 *
 * @Package GimooFend
 * @Support http://bbs.eduu.com
 * @Author  Gimoo <gimoohr@gmail.com>
 * @version $Id: Server.php 4 2011-12-29 11:01:08Z gimoo $
**/

class Fend_Pcntl_Server
{
    public $pcfg=array(
        'access_log'=>'/var/log/fdpcntl-access.log',//������־
        'error_log'=>'/var/log/fdpcntl-error.log',//�쳣��־
        'server_ip'=>'127.0.0.1',//������IP
        'port'=>'8031',//�����˿�
        'user'=>'root',//�����û�
        'pidfile'=>'/var/run/fdpcntl.pid',//PID�ļ���ַ
        'fptype'=>'debug',//����ģʽ
        'fpdir'=>'',//���еĸ�Ŀ¼
    );
    private $_pid=0;//��ǰ�����PID
    static $in=null;

    /**
     * ����ģʽ: ���ǰ����
     *
     * @return object $in  ģ�����
    **/
    public static function Factory()
    {
        if(PHP_SAPI!=='cli') die('Not CLI');//ֻ����CLIģʽ�²�������

        if(null===self::$in){
            self::$in=new self();
            if(func_num_args()>=1){
                self::$in->loadCfg(func_get_arg(0));
            }
        }

        self::$in->getPID();//���ҵ�ǰPID
        $mod=@$_SERVER['argv'][1];
        switch($mod){
            case 'start'://��������
                self::$in->start();
                break;
            case 'stop'://ֹͣ����
                self::$in->stop();
                break;
            case 'reload'://�������������ļ�
                self::$in->reload();
                break;
            case 'restart'://��������
                self::$in->restart();
                break;
        }

        return self::$in;
    }

    /**
     * ��������
     * ������������ʱ,ֱ�ӷ��ز��ͳ��쳣
     * ������δ������ʱ,ֱ������
     *
     * @return void
    **/
    public function start()
    {
        if($this->_pid==0){
            $this->showMsg("Starting FDpcntl.");
            $this->run();
        }else{
            $this->showMsg("FDpcntl already running? (pid={$this->_pid}).");
        }
    }

    /**
     * ��������
     * �������ѱ�����ʱ,��ֹͣ�������κ�������������
     * ������δ������ʱ,ֱ����������
     *
     * @return void
    **/
    public function restart()
    {
        if($this->_pid==0){
            $this->showMsg("Starting FDpcntl.");
            $this->run();
        }else{
            $this->stop();//��ֹͣ����
            sleep(1);
            $this->start();//��������
        }
    }

    /**
     * �������������ļ�
     * ��������
     *
     * @return void
    **/
    public function reload()
    {

    }

    /**
     * ֹͣ����
     * ��������������ʱ���ܱ���ͣ
     * ����ֱ�ӷ�����Ϣ,�����κβ���
     *
     * @return void
    **/
    public function stop()
    {
        if($this->_pid==0){
            $this->showMsg("FDpcntl not running? (check {$this->pcfg['pidfile']})");
        }else{
            $this->showMsg("Stopping FDpcntl.");
            $this->showMsg("Waiting for PIDS: {$this->_pid}.");
            posix_kill($this->_pid, SIGTERM);//ֹͣ���񲢷�������ͷ
            $this->_pid=0;//����PID
            @unlink($this->pcfg['pidfile']);//����PID�ļ�
        }
    }

    /**
     * ��ʼ���з�����
     *
     * @return void
    **/
    private function run()
    {
        set_time_limit(0);
        //ob_implicit_flush();

        declare(ticks = 1);
        $this->sigDaemon();//����Daemonģʽ����
        pcntl_signal(SIGTERM, array($this, 'sigHandler'));
        pcntl_signal(SIGINT,  array($this, 'sigHandler'));
        pcntl_signal(SIGCHLD, array($this, 'sigHandler'));

        $sock=$this->startSocket();
        if(false===$sock){
            $this->showMsg('Waiting for clients Not connect.');//д����־�Ѿ��ɹ�����Socket
            return;
        }else{
            $this->showMsg('Waiting for clients to connect.');//д����־�Ѿ��ɹ�����Socket
        }
        $this->setPID();//���浱ǰPID�Ա㸴��

        //Daemon��ʽ���ͻ��˵�����
        while(true){
            $csock=@socket_accept($sock);//���ͻ����Ƿ����ź�����
            if(false===$csock){
                usleep(1000);//1ms
            }elseif($csock>0){
                if($this->sigClient($csock)) break;
            }else{
                $this->ErrorLog("Error: ".socket_strerror($csock));
            }
        }
        @socket_close($csock);
        @socket_close($sock);
    }

    /**
     * ����һ��Socket�ն�
     * �ɹ�����Socket��Դ��ʶ
     * ʧ�ܷ���false
     *
     * @return results|bool
    **/
    public function startSocket()
    {
        //����һ��socket�ն�
        if(($sock=socket_create(AF_INET, SOCK_STREAM,SOL_TCP))===false){
            $this->showMsg('Failed to create socket: '.socket_strerror($sock));
            return false;
        }

        if (($ret=socket_bind($sock,$this->pcfg['server_ip'],$this->pcfg['port']))===false){
            socket_close($sock);
            $this->showMsg('Failed to bind socket: '.socket_strerror($ret));
            return false;
        }

        if (($ret = socket_listen($sock, 0)) === false){
            $this->showMsg('Failed to listen to socket: '.socket_strerror($ret));
            return false;
        }
        unset($ret);
        socket_set_nonblock($sock);
        return $sock;
    }



    /**
     * Daemon�ػ��̷�ʽ���з���
     * ����������ʱʹ��Daemon��ʽ�����̨�ػ�����
     *
     * @return void
    **/
    public function sigDaemon()
    {
        $this->ErrorLog("Begin parent daemon pid({$this->_pid}).");
        $pid = pcntl_fork();
        if ($pid == - 1){//�����½���ʧ��
            $this->showMsg("Fork failure pid({$this->_pid}).");exit;
        }else if ($pid){//�رյ�ǰ�ĸ�����
            $this->ErrorLog("End parent daemon pid({$this->_pid}) exit.");exit;
        }else{//���������ӽ�������Ϊ�ػ�����
            posix_setsid();

            //�������е��û�
            $pw=posix_getpwnam($this->pcfg['user']);
            if(is_array($pw) && count($pw)>0){
                posix_setuid($pw['uid']);
                posix_setgid($pw['gid']);
            }

            $this->_pid = posix_getpid();
            $this->ErrorLog("Begin child daemon pid({$this->_pid}).");
        }
    }

    /**
     * �źŴ���
     * ������������ֹͣ�����������ȶ���ʱ�Զ����ø��źŴ���
     *
     * @param  string $sig �źų���
     * @return void
    **/
    public function sigHandler($sig)
    {
        //$this->RunLog("EXIT PID($this->_pid) $sig - ".posix_getpid().".");
        switch ($sig){
            case SIGINT://2,�жϷ��� interrupt prog (term) (ctl-c)
            case SIGTERM://15,��ֹ���� terminate process (term)
                exit;
                break;

            case SIGHUP://1,�˳��ն� terminal line hangup (term)
            case SIGQUIT://3,�˳����� quit program (core)

                break;

            case SIGCHLD://20,�ӽ��̸ı� child status changed (ign)
                pcntl_waitpid(- 1, $status);
                break;

            case SIGUSR1:// 30, user defined signal 1 (term)
            case SIGUSR2:// 31, user defined signal 2 (term)
                echo "Caught SIGUSR1...\n";
                break;

            default://handle all other signals
                break;
        }
    }

    /**
     * �ն˴���
     * ���е�SIGϵ�д�����: sigDebug sigFend sigFunction ʱ����ֵֻ��Ϊ0-2֮�����������ֵ
     * 0: �׳���Ϣ���ն�,�����ȴ�����
     * 1: �׳���Ϣ���ն�,����ֹ����رս���
     * 2: ������Ϣ���׳�,����ֹ����رվ���
     *
     * @param  string $csock  Socket��Դ
     * @return bool
    **/
    public function sigClient($csock)
    {
        ob_start();
        $this->ErrorLog('Begin client.');
        $pid=pcntl_fork();//����һ���µĽ���
        if ($pid==-1){
            $this->ErrorLog("Fock clinet child error.");
        }elseif($pid==0){//�ӽ��̳ɹ�����
            $pid = posix_getpid();//��ȡ��ǰ���̵�PID
            $this->ErrorLog("Begin client child pid({$pid}).");
            $this->ErrorLog("Begin handle user logic.");

            //--------------��ȡ�ͻ��˵�����
            empty($this->pcfg['fpmsg']) && socket_write($csock, $this->pcfg['fpmsg']."\r\n", strlen($this->pcfg['fpmsg']."\r\n"));
            socket_set_block($csock);//δ֪

            if($this->pcfg['fptype']=='debug'){
                $this->sigDebug($csock);//���д�����
            }else{//�������з��ر��붼Ϊtrue
                $this->sigFend($csock);//���д�����
            }
            //--------------��ȡ�ͻ��˵�����
            $this->ErrorLog("End handle user logic.");
            $this->ErrorLog("End client");
            return true;
        }else{
            $this->ErrorLog("Close csock in child pid({$pid}).");
        }
        return false;
    }

    /**
     * Debug����ģʽ
     *
     * @param  string $str  �ͻ��˴��ݹ������ַ�
     * @return int
    **/
    public function sigDebug($csock)
    {
        while(true){
            $nbuf=null;
            if(false===($nbuf=socket_read($csock,2048,PHP_NORMAL_READ))){//��ȡʧ��ֱ������ѭ��
                $this->ErrorLog("socket_read() failed: reason: ".socket_strerror(socket_last_error($csock)));
                break;
            }
            $nbuf=trim($nbuf);//ȥ���հ��ַ�
            if(empty($nbuf)) continue;//û���κοɴ�ӡ�ַ�ʱ���ص��ȴ�
            if($nbuf=='quit') break;
            if($nbuf{0}=='$'){
                eval('$nbuf=@print_r('.$nbuf.',true);');
            }
            $nbuf="#-result: $nbuf\r\n#";

            //$tmsg=$isloop!==2 ? ob_get_contents() : null;//ȡ�û���
            //ob_clean();//�������
            socket_write($csock, $nbuf, strlen($nbuf));
        }
    }

    /**
     * Fendģʽ
     *
     * @param  string $csock �ն���������Դ
     * @return bool
    **/
    public function sigFend($csock)
    {
        if(false===($nbuf=socket_read($csock,12000))){//��ȡʧ��ֱ������ѭ��
            $this->ErrorLog("Socket_read() failed reason: ".socket_strerror(socket_last_error($csock)));
            return true;
        }

        //��������ȡ����Ϣ
        $nbuf=explode("\r\n",$nbuf);
        $item=array('FP-Close'=>0,'FDTCP'=>null);
        foreach($nbuf as &$v){
            if(empty($v) || false===strpos($v,':')) continue;
            list($key,$value)=explode(':',$v,2);

            if($key=='GET'){
                parse_str($value,$_GET);
            }elseif($key=='POST'){
                parse_str($value,$_POST);
            }else{
                $item[$key]=$value;
            }
        }

        //��̬���뺯���ļ�
        is_file($this->pcfg['function']['apps_php']) && include($this->pcfg['function']['apps_php']);
        if(!function_exists($this->pcfg['function']['apps_mod'])){
            $this->ErrorLog("ERROR: Call_user_func_array({$this->pcfg['function']['apps_mod']}).");
            return true;
        }

        //$this->runLog("GET:{$item['FDTCP']}");
        call_user_func_array($this->pcfg['function']['apps_mod'],array(&$item,&$this));

        if($item['FP-Close']){
            $tmsg=ob_get_contents();//ȡ�û���
            @socket_write($csock, $tmsg, strlen($tmsg));
        }
        ob_clean();//�������
    }

    /**
     * ��ȡ��ǰ�������з���PID
     * ���������ɹ�����ʱ���ش���0������
     * ��������ʧ�����л�δ����ʱ����һ��С�ڵ���0������
     *
     * @return int
    **/
    private function getPID()
    {
        if(is_file($this->pcfg['pidfile'])){
            $this->_pid=(int)file_get_contents($this->pcfg['pidfile']);
        }else{
            $this->_pid=0;
        }
    }

    /**
     * ���õ�ǰ����PID
     *
     * @return void
    **/
    private function setPID()
    {
        $this->_pid = posix_getpid();
        $this->ErrorLog("Begin child daemon pid($this->_pid).");
        file_put_contents($this->pcfg['pidfile'],"{$this->_pid}");
        $this->ErrorLog("Write pidfile({$this->pcfg['pidfile']}).");
    }

    /**
     * �����쳣���ն˲���¼��־
     *
     * @param  string $str �쳣��Ϣ
     * @param  string $t   �Ƿ���ֹ
     * @return void
    **/
    private function showMsg($str)
    {
        echo $str."\n";
        $this->ErrorLog($str);
    }

    /**
     * ��¼�쳣��־
     * ����DEBUG�����Ϣ
     *
     * @param  string $str    �쳣��Ϣ
    **/
    private function ErrorLog($str)
    {
        $str=date("[Y-m-d H:i:s]").' '.$str."\n";
        file_put_contents($this->pcfg['error_log'],$str,FILE_APPEND);
    }

    /**
     * ��¼������־
     *
     * @param  string $str    �쳣��Ϣ
    **/
    public function RunLog($str)
    {
        $str=date("[Y-m-d H:i:s]").' '.$str."\n";
        file_put_contents($this->pcfg['access_log'],$str,FILE_APPEND);
    }

    /**
     * ���������ļ�
     *
     * @param  string $str    �쳣��Ϣ
    **/
    public function loadCfg($fini)
    {
        $fini=parse_ini_file($fini,true);
        is_array($fini) && $this->pcfg=array_merge($this->pcfg,$fini);
        //if(!in_array($this->pcfg['fptype'],array('debug','fend','function'))) $this->pcfg['fptype']='debug';

        //������������Ŀ¼
        !is_dir($this->pcfg['fpdir']) && $this->pcfg['fpdir']=dirname(__FILE__).'/';
        chdir($this->pcfg['fpdir']);//�л�������Ŀ¼

        empty($this->pcfg['function']['apps_mod']) && $this->pcfg['function']['apps_mod']='sigFunction';
    }

    /**
     * ˵��
     *
     * @param  string $var1    ����˵��
     * @param  string $var2    ����˵��
     * @return array  $tplPre  ģ���׺
    **/
    public function __destruct()
    {

    }

}
?>