<?php
namespace app\admin\controller;

use app\common\controller\Adminbase;
use think\facade\Cookie;
use think\facade\Env;
use think\Request;
use app\admin\model\SpiderList as spiderModel;
use app\admin\model\Images as imagesModel;

class Spider
{
	public function index(){
		return view();
	}

	public function geturl(Request $request){
		$params = $request->param();		
		$url = $params['openurl'];
		$client = new \swoole_client(SWOOLE_SOCK_TCP);
        if (!$client->connect('127.0.0.1', 9111, -1)) {
            exit("connect failed. Error: {$client->errCode}\n");
        }
        $client->send($url);
		
		return "已提交后台进程";
	}

	public function ucvideo(Request $request){
        $cats = model('Catgory')->field('id,name')->where('parent',0)->where('status',1)->select();
        $this->assign('cats',$cats);
	    return view();
	}
    public function ucvideourl(Request $request){
        $params = $request->param();
        $data = $params['data'];
        $data = json_decode($data,true);
        $url = $data['openurl'];
        $sitecontent = getUrlContent($url);
        dump($sitecontent);
    }
	
    public function test(){
        $url = "https://www.toutiao.com/a6817621907341312515#p=1";
        $sitecontent = $this->getUrlContentToutiao($url);
        preg_match('/<title>(.*?)<\/title>/si', $sitecontent, $titlearr);
        preg_match('/<ul class="image-list" (.*?)>(.*?)<\/ul>/si', $sitecontent, $content);
        if($content){
            preg_match_all('/<img src="(.*?)" (.*?)>/si', $content[2], $imgss);
            foreach ($imgss[1] as $key=>$value){
                echo $value;
                $path = '/home/wwwroot/image.news36524.com/public/uploads/toutiao2/'.date('Y-m-d').'/';
                $file = $this->filedownload($value,$path);
                echo $file;
            }
        }
        
        die;
        preg_match('/<title>(.*?)<\/title>/si', $sitecontent, $titlearr);
        preg_match('/gallery: JSON.parse\("(.*?)"\),/si', $sitecontent, $pics);

        $obj = stripslashes(html_entity_decode($pics[1]));
        $imgobj = json_decode($obj,true);
        $str = $this->getRandomStr(9,false);

        $path = '/home/wwwroot/news36524.com/public/uploads/toutiao/';
        $imgs = [];
        $n = 0;
        foreach ($imgobj['sub_images'] as $key => $value) {
            echo $value['url'];
            $file = $this->filedownload($value['url'],$path);
            echo "下载图片:".$file." 中……\n";
            if($n==0){
                $thumb = '/uploads/toutiao/'.$file.'.jpg';
            }
            array_push($imgs, '/uploads/toutiao/'.$file.'.jpg');
        }
        $title = $titlearr[1];
        $data = ['title'=>$title,'thumb'=>$thumb,'pics'=>json_encode($imgs,JSON_UNESCAPED_UNICODE)];
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
    public function download($filename)
    {
        header("Content-Type: application/force-download");
        header('Content-Disposition: attachment; filename="11111.jpg"');
        $img = file_get_contents($filename);
        echo $img;
    }
    public function filedownload($url,$path){
        $url = "https://p6-tt.byteimg.com/origin/pgc-image/53eeb158296541c09fa04c1421cb9810.jpg";
        $headers = array(
            'user-agent: Mozilla/5.0',
            'cookie: LARDAR_WEB_ID=441a34a5-6c48-4498-9278-9e1eb5b9af80; WEATHER_CITY=%E5%8C%97%E4%BA%AC; csrftoken=f9b45f8499653bfdfd0d8e2a07069935; sid_guard=937fb305570da3fd9ff97a2202111892%7C1592579429%7C5184000%7CTue%2C+18-Aug-2020+15%3A10%3A29+GMT; ttcid=45b9bfbfb0e144bbad6776b19857011a81; _ga=GA1.2.1464184095.1592827540; tt_webid=6863617368226170381; MONITOR_WEB_ID=441a34a5-6c48-4498-9278-9e1eb5b9af80; s_v_web_id=verify_ke4zxui7_KxfTyxWV_a1ds_4iL1_98Hh_YlKL7vhmIbdd; tt_webid=6863617368226170381; __tasessionId=l4y9u27q11598060466340; tt_scid=tHIS08y3NXuetZ.orWpfcz.kvU.qtf0KNwoQYzBl.qOdjsX161QIhwfsWKxnuj8Ab7b2; __ac_nonce=05f4078d300484ab813a4; __ac_signature=_02B4Z6wo00f015SRrrAAAIBB12-kHvMcIc-UlKoAALpvowtLWAWMOWAp1FZ4bZJBLNBbVR5J3JRhV-Re1SpU57kJDik2yTVf2oF-B10i4YgQjfssvt3thr56jb1RwYrYMZ6SRQZ7bT3WIAWec7; __ac_referer=https://www.toutiao.com/ch/news_image/',
            'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
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
        $resource = fopen($path . $filename, 'a');
        dump($resource);
        fwrite($resource, $file);
        fclose($resource);
        return $filename;
    }
}