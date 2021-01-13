<?php
namespace app\index\controller;

use think\Controller;
use think\Request;
use app\index\model\Comments;
use think\facade\Log;
use think\facade\Debug;

class Api extends Controller
{
	public function postcomment(Request $request){
		if($request->isPost()){
			$params = $request->param();
			$data = $params['params']['mm'];
			$array['content'] = $data;
			$array['ip'] = $request->ip();
			$nickname = cookie('nickname');
			if($nickname){
				$array['nickname'] = $nickname;
			}else{
				$array['nickname'] = randnick();
				cookie('nickname', $array['nickname']);
			}
			$array['topic_id'] = $params['params']['tid'];
			$commentsModel = new Comments();
			$res = $commentsModel::create($array);
			if($res){
				return json(['code'=>200,'msg'=>"评论成功"]);
			}else{
				return json(['code'=>204,'msg'=>"发生未知错误"]);
			}
		}		
	}

	public function getcomments(Request $request){
		if($request->isPost()){
			$params = $request->param();
			$where = $params['params'];
			$commentsModel = new Comments();
			$data = $commentsModel->where('topic_id',$where['tid'])->select();
			return json(['code'=>200,'data'=>$data]);
		}
	}

	public function commentstotal(){
	    $commentsModel = new Comments();
		$data = $commentsModel->count();
		return json(['code'=>200,'data'=>$data]);
	}

	public function getarticles(Request $request){		
		if ($request->isPost()) {			
			$params = $request->param();
			$page = $params['currentPage'];
			$pageSize = $params['pageSize'];
			Log::info("页码:".$page."，每页条数:".$pageSize);
			$start = ($page-1)*$pageSize;
			Debug::remark('begin');
			$data = model('news')->order('update_time','desc')->where('catid',4)->limit($start,$pageSize)->select();
			$count = model('news')->count();
			Debug::remark('end');
			Log::info(Debug::getRangeTime('begin','end').'s');
			return json(['code'=>10000,'data'=>$data,'total'=>$count]);
		}
		
	}

	public function getimages(Request $request){
		if ($request->isPost()) {
			$data = model('Images')->order('create_time','desc')->limit(4)->select();
			return json(['code'=>10000,'data'=>$data]);
		}
	}

	public function getlevone(Request $request){
		if ($request->isGet())  {			
			$data = model('news')->where('position',1)->order('create_time','desc')->select();			
			return json(['code'=>10000,'data'=>$data]);
		}
	}

	public function gettopten(Request $request){
		if ($request->isPost()){
			$hotdata = model('news')->whereTime('create_time','week')->order('views','desc')->limit(9)->select();
        	return json(['code'=>10000,'data'=>$hotdata]);
		}
	}

	public function gettoken(Request $request){		
		$params = $request->param();
		Log::info(json_encode($params));
		if ($params) {
			$token='news36524';
			$timestamp = $params['timestamp'];
			$nonce = $params['nonce'];
			$signature = $params['signature'];
			$array = array($token,$timestamp,$nonce);
			sort($array);		 
			//2.将排序后的三个参数拼接后用sha1加密
			$tmpstr = implode($array);
			$tmpstr = sha1($tmpstr);		 
			//3. 将加密后的字符串与 signature 进行对比, 判断该请求是否来自微信
			if($tmpstr == $signature)
			{
				return $params['echostr'];
			}else{
				return false;
			}			
		}
	}
}