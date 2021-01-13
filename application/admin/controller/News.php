<?php
namespace app\admin\controller;

use app\common\controller\Adminbase;
use think\Request;

class News extends Adminbase
{
    public function index()     {   
        $request = request();
        $params = $request->param();
        if($params){
            $page = $params['page'];
            $start = ($page-1)*$params['limit'];
            $data = model('news')->order('id','desc')->limit($start,$params['limit'])->select();
            foreach ($data as $key => $value) {
                $data[$key]['catename'] = model('Catgory')->where('id',$value['catid'])->value('name');
            }
            $count = model('news')->count();
            return json(['code'=>0,'msg'=>'','count'=>$count,'data'=>$data]);
        }
        $cats = model('Catgory')->field('id,name')->where('parent',0)->where('status',1)->select();
        $this->assign('cats',$cats);
        return $this->fetch();
    }

    public function newscurl(Request $request){
        if($request->isPost()){
            $params = $request->param();
            $data = $params['data'];
            $data = json_decode($data,true);
            $url = $data['openurl'];            
            $sitecontent = getUrlContent($url);        
            # preg_match_all('/(?<=jsonp1\\()[^\\)]+/', $sitecontent, $match);
            preg_match_all('/<h1 id=bd_article_title>[\s\S]*?<\/h1>/', $sitecontent, $title);
            dump($title[0]);
            if (count($title)){
                if($title[0]){
                    preg_match_all('/<div class=content>[\s\S]*?<\/article>/', $sitecontent, $match);
                    $str = $match[0][0];
                    $cc = preg_replace("/\?size=.\d*.x.\d*./","\"",$str);
                    preg_match_all('/[\x{4e00}-\x{9fff}]+/u', $cc, $descps);
                    $descp = implode('',$descps[0]);
                    $descp = mb_substr($descp,0,60);
                    $content = json(['content'=>$cc,'title'=>strip_tags($title[0][0]),'descp'=>$descp]);
                    return $content;
                }else{
                    preg_match_all('/(?<=window\.__INITIAL_DATA__ = )[\s\S](.*)\;+/', $sitecontent, $title2);
                    // echo $title2[0][0];
                    $tt = rtrim($title2[0][0], ";");
                    $tt = json_decode($tt,true);
                    $cc = preg_replace("/\?size=.\d*.x.\d*./","\"",$tt['result']['Detail']['content']);
                    preg_match_all('/[\x{4e00}-\x{9fff}]+/u', $cc, $descps);
                    $descp = implode('',$descps[0]);
                    $descp = mb_substr($descp,0,60);
                    $content = json(['content'=>$cc,'title'=>strip_tags($tt['result']['Detail']['title']),'descp'=>$descp]);
                    return $content;
                }
            }
        }        
        
    }
    public function uccurl(Request $request){
        if($request->isPost()){
            $params = $request->param();
            $data = $params['data'];
            $data = json_decode($data,true);
            $url = $data['openurl'];            
            $sitecontent = getUrlContent($url);
            //dump($sitecontent);die;
            preg_match('/(?<=xissJsonData = )[\s\S](.*)\;+/', $sitecontent, $obj);
            $objs = substr($obj[0], 0,-1);
            if($objs){
                $objs = json_decode($objs,true);                
                $title = $objs['title'];                
                $contents = strip_tags($objs['content']);
                preg_match_all('/[\x{4e00}-\x{9fff}]+/u', $contents, $descps);
                $descp = implode('',$descps[0]);
                $descp = mb_substr($descp,0,60);

                foreach ($objs['images'] as $key => $value) {
                    $imageUrl = substr($value['url'],0,-5);
                    $contents = $contents."<img src='".$imageUrl."'/>";
                }                
                $content = json(['content'=>$contents,'title'=>$title,'descp'=>$descp]);
                return $content;
            }
            

        }
    }
    public function newsearch(){
        $request = request();        
        $params = $request->param();
        if($params){
            $map = [];
            $page = $params['page'];
            $start = ($page-1)*$params['limit'];
            if($params['id']){
                $map[] = ['id','eq',$params['id']];
            }
            if($params['title']){
                $map[] = ['title','like',"%".$params['title']."%"];
            }
            if($params['catid']){
                $map[] = ['catid','eq',$params['catid']];
            }
            if($params['position']){
                $map[] = ['position','eq',$params['position']];
            }
            $data = model('news')->order('id','desc')->where($map)->limit($start,$params['limit'])->select();
            foreach ($data as $key => $value) {
                $data[$key]['catename'] = model('Catgory')->where('id',$value['catid'])->value('name');
            }
            $count = model('news')->where($map)->count();
            return json(['code'=>0,'msg'=>'','count'=>$count,'data'=>$data]);
        }
    }
    public function bloglist(){
        $request = request();
        $params = $request->param();
        if($params){
            $page = $params['page'];
            $start = ($page-1)*$params['limit'];
            $data = model('news')->where('catid',1)->order('id','desc')->limit($start,$params['limit'])->select();
            foreach ($data as $key => $value) {
                $data[$key]['catename'] = model('Catgory')->where('id',$value['catid'])->value('name');
            }
            $count = model('news')->where('catid',1)->count();
            return json(['code'=>0,'msg'=>'','count'=>$count,'data'=>$data]);
        }
        $cats = model('Catgory')->field('id,name')->where('parent',0)->where('status',1)->select();
        $this->assign('cats',$cats);
        return $this->fetch();
    }

    public function blogadd(){
        $request = request();        
        if($request->isPost()){
            $params = $request->param();
            $data = json_decode($params['data'],true);
            $arr['content'] = $data['content'];
            unset($data['content']);
            unset($data['test-editormd-markdown-doc']);
            unset($data['content']);
            $data['catid'] = 1;
            $res = model('news')->create($data);            
            if($res){
                $arr['id'] = $res->id;
                model('NewsData')->create($arr);
                return json(['code'=>200,'msg'=>'添加成功']);
            }
        }
        return view();
    }
    public function blogedit(Request $request){
        $params = $request->param();
        if($request->isPost()){
            $data = json_decode($params['data'],true);
            $arr['content'] = $data['content'];            
            unset($data['content']);
            unset($data['test-editormd-markdown-doc']);
            unset($data['content']);            
            $news = array(
                'id' => $data['id'], 
                'title' => $data['title'],
                'catid' => 1,                
                'thumb' => $data['thumb'],
                'update_time' => time(),
            );       
            $res = model('news')->isUpdate(true)->save($news);
            if($res){
                $newsdata = array(
                    'id' => $data['id'], 
                    'content' => $arr['content']
                );
                $r = model('NewsData')->isUpdate(true)->save($newsdata);
                return json(['code'=>200,'msg'=>'更新成功']);
            }else{
                return json(['code'=>201,'msg'=>'更新失败']);
            }
        }
        $id=input('get.id');
        $data = model('news')::get($id);
        $this->assign('data',$data);
        return view();
    }  
   	public function newsadd(){
        $request = request();        
        $params = $request->param();
        if($params){
            $data = json_decode($params['data'],true);
            $arr['content'] = $data['content'];            
            unset($data['image']);
            unset($data['content']);
            $thumbs = getpics($arr['content'])?getpics($arr['content']):[];            
            if (count($thumbs)) {
                preg_match('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png))\"?.+>/i',$thumbs[0],$match);           
                if (count($match)) {
                    $data['thumb'] = $match[1];
                }
                
            }
            if(isset($data['status'])){
                if($data['status'] =="on"){
                    $data['status'] = 1;
                }
            }

            if(isset($data['islink'])){
                if($data['islink']=="on"){
                    $data['islink'] = 1;
                }
            }else{
                unset($data['link']);
            }
            $has = model('news')->where('title',$data['title'])->find();
            if(!$has){
                $res = model('news')->create($data);
                if($res){
                    $arr['id'] = $res->id;                
                    model('NewsData')->create($arr);
                    return json(['code'=>200,'msg'=>'添加成功']);
                }
            }else{
                model('news')->where('title',$data['title'])->update(['update_time'=>time()]);
                return json(['code'=>200,'msg'=>'更新成功']);
            }
            
        }        
        $cats = model('Catgory')->field('id,name')->where('parent',0)->where('status',1)->select();
        $this->assign('cats',$cats);
   		return $this->fetch('add');
   	}

    public function ucadd(){
        $request = request();        
        $params = $request->param();
        if($params){
            $data = json_decode($params['data'],true);
            $arr['content'] = $data['content'];
            unset($data['image']);
            unset($data['content']);
            $thumbs = getpics($arr['content']);
            if (count($thumbs)) {
                preg_match('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png))\"?.+>/i',$thumbs[0],$match);                
                $data['thumb'] = $match[1];
            }  
            if(isset($data['status'])){
                if($data['status'] =="on"){
                    $data['status'] = 1;
                }
            }

            if(isset($data['islink'])){
                if($data['islink']=="on"){
                    $data['islink'] = 1;
                }
            }else{
                unset($data['link']);
            }
            $has = model('news')->where('title',$data['title'])->find();
            if(!$has){
                $res = model('news')->create($data);            
                if($res){
                    $arr['id'] = $res->id;                
                    model('NewsData')->create($arr);
                    return json(['code'=>200,'msg'=>'添加成功']);
                }
            }else{
                return json(['code'=>201,'msg'=>'已经存在']);
            }
            
        }        
        $cats = model('Catgory')->field('id,name')->where('parent',0)->where('status',1)->select();
        $this->assign('cats',$cats);
        return $this->fetch();
    }

    public function markadd(){
        $request = request();        
        $params = $request->param();
        if($params){
            $data = json_decode($params['data'],true);
            $arr['content'] = $data['content'];
            unset($data['image']);
            unset($data['content']);

            if(isset($data['status'])){
                if($data['status'] =="on"){
                    $data['status'] = 1;
                }
            }

            if(isset($data['islink'])){
                if($data['islink']=="on"){
                    $data['islink'] = 1;
                }
            }else{
                unset($data['link']);
            }
            
            $res = model('news')->create($data);            
            if($res){
                $arr['id'] = $res->id;
                model('NewsData')->create($arr);
                return json(['code'=>200,'msg'=>'添加成功']);
            }
        }        
        $cats = model('Catgory')->field('id,name')->where('parent',0)->where('status',1)->select();
        $this->assign('cats',$cats);
        return $this->fetch('markadd');
    }

    public function newsedit(){
        $request = request();
        $params = $request->param();
        if($params){
            $data = json_decode($params['data'],true);            
            unset($data['image']);
            unset($data['file']);            
            if($data['status'] == "on"){
                $data['status'] = 1;
            }else{
                $data['status'] = 0;
            }
            $thumbs = getpics($data['content'])?getpics($data['content']):[];
            if (count($thumbs)) {
                preg_match('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png))\"?.+>/i',$thumbs[0],$match);                
                $data['thumb'] = $match[1];
            }  
            $news = array(
                'id' => $data['id'], 
                'title' => $data['title'],
                'catid' => $data['catid'],
                'link' => $data['link'],
                'status' => $data['status'],
                'listorder' => $data['listorder'],
                'thumb' => $data['thumb'],
                'update_time' => time(),
            );   
              
            $res = model('news')->isUpdate(true)->save($news);
            if($res){
                $newsdata = array(
                    'id' => $data['id'], 
                    'content' => $data['content']
                );
                $r = model('NewsData')->isUpdate(true)->save($newsdata);
                return json(['code'=>200,'msg'=>'更新成功']);
            }else{
                return json(['code'=>201,'msg'=>'更新失败']);
            }
        }
    }  	
    

    public function getcatgory(Request $request){

        $params = $request->param();
        if($request->isGet()){            
            $list = model('Catgory')->field('id,name')->where('parent',0)->where('status',1)->select();
            return json($list);
        }
    }

    public function upload(){
        $file = request()->file('image');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->validate(['size'=>9004800,'ext'=>'jpg,png,gif,jpeg'])->move('uploads');
        if($info){
            $src = '/uploads/'.$info->getSaveName();
            return json(['code'=>0,'msg'=>'上传成功','data'=>$src]);
        }else{
            // 上传失败获取错误信息
            $error = $file->getError();
            return json(['code'=>2,'msg'=>'上传失败','data'=>$error]);
        }
    }

    public function uploadImage(){
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->validate(['size'=>9004800,'ext'=>'jpg,png,gif,jpeg'])->move('uploads');
        if($info){
            $src = '/uploads/'.$info->getSaveName();
            return json(['code'=>0,'msg'=>'上传成功','data'=>['src'=>$src,'title'=>$info->getFilename()]]);            
        }else{
            // 上传失败获取错误信息
            $error = $file->getError();
            return json(['code'=>2,'msg'=>'上传失败','data'=>$error]);
        }
    }

    public function edit(){
        $id=input('get.id');
        $data = model('news')::get($id);
        $cats = model('Catgory')->field('id,name')->where('parent',0)->where('status',1)->select();
        $this->assign('data',$data);
        $this->assign('cats',$cats);
        return view();
    }

    public function dele(Request $request){
        if($request->isPost()){
            $params = $request->param();
            $res = model('news')->where('id',$params['id'])->delete();
            if($res){
                return json(['code'=>10000,'msg'=>'删除成功']);
            }else{
                return json(['code'=>10001,'msg'=>'删除失败']);
            }
        }
    }
} 
