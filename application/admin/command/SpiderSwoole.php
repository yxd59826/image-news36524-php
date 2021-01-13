<?php

namespace app\admin\command;

use Swoole\Process;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Log;
use app\admin\model\SpiderList as spiderModel;
use app\admin\model\Images as imagesModel;

class SpiderSwoole extends Command
{
    protected $server;

    protected function configure()
    {
        $this->setName('下载服务')
            ->addArgument('action', Argument::OPTIONAL, "start|stop|restart|reload", 'start')
            ->addOption('daemon', 'd', Option::VALUE_NONE, '该进程已后台运行')
            ->setDescription('Start 下载服务!');
    }

    protected function execute(Input $input, Output $output)
    {
        $action = trim($input->getArgument('action'));
        if (in_array($action, ['start', 'stop', 'reload', 'restart'])) {
            $this->$action();
        } else {
            $output->writeln("<error>传入参数错误:{$action}, 仅支持 start|stop|restart|reload 参数 .</error>");
        }
    }

    public function onWorkerStart($server, $worker_id)
    {
    	// echo "服务启动";
        // if (!$server->taskworker) {            
        //     if ($worker_id == 1) {
                
        //     }
        // }
    }

    public function onReceive($server, $fd, $reactor_id, $data)
    {
        if($data){
        	echo "接收到数据URL：".$data."\n";
        	$url = $data;
        	$sitecontent = $this->getUrlContentToutiao($url);			
			preg_match('/<title>(.*?)<\/title>/si', $sitecontent, $titlearr);
            preg_match('/gallery: JSON.parse\("(.*?)"\),/si', $sitecontent, $pics);
			$obj = stripslashes(html_entity_decode($pics[1])); 
			$imgobj = json_decode($obj,true);
			// dump($imgobj['sub_images']);
			$str = $this->getRandomStr(9,false);
			$path = '/home/wwwroot/news36524.com/public/uploads/toutiao/';
			$imgs = [];
            $n = 0;
			foreach ($imgobj['sub_images'] as $key => $value) {
				$file = $this->filedownload($value['url'],$path);
				echo "下载图片:".$file." 中……\n";
                if($n==0){
                    $thumb = '/uploads/toutiao/'.$file.'.jpg';
                }
				array_push($imgs, '/uploads/toutiao/'.$file.'.jpg');
			}
			$title = $titlearr[1];
			$data = ['title'=>$title,'thumb'=>$thumb,'pics'=>json_encode($imgs,JSON_UNESCAPED_UNICODE)];
			$imagesModel = new imagesModel();
            $has = $imagesModel::where('title',$title)->find();
            if($has){
                $server->send($fd, $title."已经存在");
                $n=0;
            }else{
                $res = $imagesModel::create($data);
                $n=0;
                echo "下载完成"."\n";
            }			
	        $server->close($fd);
        }        
    }

    protected function start()
    {
        $pid = $this->getMasterPid();
        if ($this->isRunning($pid)) {
            $this->output->writeln('<error>其他临时服务正在运行！！！</error>');
            return false;
        }
        $this->output->writeln('启动下载服务...');
        sleep(1);

        $options = [
            'daemonize' => false,
            'worker_num' => 12,
            'pid_file' => config('file.RUNTIME_PATH') . 'Spider_pid',
            'log_file' => config('file.RUNTIME_PATH') . 'Spider_log_file',
        ];
        // 开启守护进程模式
        if ($this->input->hasOption('daemon')) {
            $options['daemonize'] = true;
        }
        $this->server = new \Swoole\Server(
            '0.0.0.0',
            '9111',
            SWOOLE_BASE
        );

        $this->server->on('WorkerStart', array($this, 'onWorkerStart'));
        $this->server->on('Receive', array($this, 'onReceive'));
        $this->server->set($options);
        $this->output->writeln('下载服务已启动，地址：0.0.0.0：9111');
        $this->server->start();
    }

    protected function stop()
    {
        $pid = $this->getMasterPid();
        if (!$this->isRunning($pid)) {
            $this->output->writeln('<error>服务未运行！！！</error>');
            return false;
        }
        $this->removePid();
        $this->output->writeln('停止服务中...');
        Process::kill($pid, SIGTERM);
        $this->output->writeln('服务已停止');
    }

    protected function reload()
    {
        $pid = $this->getMasterPid();
        if (!$this->isRunning($pid)) {
            $this->output->writeln('<error>其他临时服务未运行！！！</error>');
            return false;
        }
        $this->output->writeln('服务正在热重启...');
        Process::kill($pid, SIGUSR1);
        $this->output->writeln('服务热重启成功');
    }

    protected function restart()
    {
        $pid = $this->getMasterPid();
        if ($this->isRunning($pid)) {
            $this->stop();
        }
        $this->start();
    }

    protected function getMasterPid()
    {
        if (is_file(config('file.RUNTIME_PATH') . 'Spider_pid')) {
            $masterPid = (int)file_get_contents(config('file.RUNTIME_PATH') . 'Spider_pid');
        } else {
            $masterPid = 0;
        }
        return $masterPid;
    }

    protected function isRunning($pid)
    {
        if (empty($pid)) {
            return false;
        }
        return Process::kill($pid, 0);
    }

    protected function removePid()
    {
        if (is_file(config('file.RUNTIME_PATH') . 'Spider_pid')) {
            unlink(config('file.RUNTIME_PATH') . 'Spider_pid');
        }
    }

    public function filedownload($url,$path){
		$headers = array(
			'user-agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36'
	    );
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		$file = curl_exec($ch);
		curl_close($ch);
		$filename = pathinfo($url, PATHINFO_BASENAME);
		$resource = fopen($path . $filename.".jpg", 'a');
		fwrite($resource, $file);
		fclose($resource);
		return $filename;
	}

	public function getUrlContentToutiao($url){
	    $ch = curl_init();
	    $headers = array(
			'user-agent: Mozilla/5.0',            
            'cookie: LARDAR_WEB_ID=441a34a5-6c48-4498-9278-9e1eb5b9af80; WEATHER_CITY=%E5%8C%97%E4%BA%AC; csrftoken=f9b45f8499653bfdfd0d8e2a07069935; sid_guard=937fb305570da3fd9ff97a2202111892%7C1592579429%7C5184000%7CTue%2C+18-Aug-2020+15%3A10%3A29+GMT; ttcid=45b9bfbfb0e144bbad6776b19857011a81; _ga=GA1.2.1464184095.1592827540; tt_webid=6863617368226170381; MONITOR_WEB_ID=441a34a5-6c48-4498-9278-9e1eb5b9af80; s_v_web_id=verify_ke4zxui7_KxfTyxWV_a1ds_4iL1_98Hh_YlKL7vhmIbdd; tt_webid=6863617368226170381; __tasessionId=l4y9u27q11598060466340; tt_scid=tHIS08y3NXuetZ.orWpfcz.kvU.qtf0KNwoQYzBl.qOdjsX161QIhwfsWKxnuj8Ab7b2; __ac_nonce=05f4078d300484ab813a4; __ac_signature=_02B4Z6wo00f015SRrrAAAIBB12-kHvMcIc-UlKoAALpvowtLWAWMOWAp1FZ4bZJBLNBbVR5J3JRhV-Re1SpU57kJDik2yTVf2oF-B10i4YgQjfssvt3thr56jb1RwYrYMZ6SRQZ7bT3WIAWec7; __ac_referer=https://www.toutiao.com/ch/news_image/',
            'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',  
	    );
	    curl_setopt($ch,CURLOPT_URL,$url);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($ch,CURLOPT_HEADER,1);
	    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	    $output = curl_exec($ch);
	    curl_close($ch);
	    return $output;
	}


	public function getRandomStr($len, $special=true){
	    $chars = array(
	        "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
	        "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
	        "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
	        "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
	        "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
	        "3", "4", "5", "6", "7", "8", "9"
	    );

	    if($special){
	        $chars = array_merge($chars, array(
	            "!", "@", "#", "$", "?", "|", "{", "/", ":", ";",
	            "%", "^", "&", "*", "(", ")", "-", "_", "[", "]",
	            "}", "<", ">", "~", "+", "=", ",", "."
	        ));
	    }

	    $charsLen = count($chars) - 1;
	    shuffle($chars);                            //打乱数组顺序
	    $str = '';
	    for($i=0; $i<$len; $i++){
	        $str .= $chars[mt_rand(0, $charsLen)];    //随机取出一位
	    }
	    return $str;
	}
}
