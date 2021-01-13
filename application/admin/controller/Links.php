<?php
namespace app\admin\controller;

use app\common\controller\Adminbase;
use think\facade\Cookie;
use think\facade\Env;
use think\Request;
use app\admin\model\Links as LinkModel;

class Links extends Adminbase
{
	public function index(){
		return view();
	}

    /**
     * Notes   :获取数据
     * Created by yxd
     * DateTime: 2019/9/3 13:07
     */
	public function getData(Request $request){
        if($request->isGet()){
            $params = $request->param();
            $page = $params['page'];
            $start = ($page-1)*$params['limit'];
            $data = LinkModel::order('id','desc')->limit($start,$params['limit'])->select();
            $count = LinkModel::count();
            return json(['code'=>0,'msg'=>'','count'=>$count,'data'=>$data]);
        }
    }

    public function listform(){
	    return view();
    }
    public function editform(){
    	return view();
    }
    public function linksadd(Request $request){
	    if($request->isPost()){
            $params = $request->param();
            $data = $params['data'];
            unset($data['image']);

            try {
                $res = LinkModel::create($data);
                if($res){
                    return json(['code'=>200,'msg'=>'添加成功']);
                }
            } catch (ValidateException $e) {
                // 这是进行验证异常捕获
                return json(['code'=>201,'msg'=>$e->getError()]);
            } catch (\Exception $e) {
                // 这是进行异常捕获
                return json(['code'=>201,'msg'=>$e->getMessage()]);
            }
        }

    }
    public function linksedit(Request $request){
    	if($request->isPost()){
            $params = $request->param();
            $data = $params['data'];
            unset($data['image']);
            try {
                $res = LinkModel::where('id',$data['id'])->update($data);
                if($res){
                    return json(['code'=>200,'msg'=>'更新成功']);
                }
            } catch (ValidateException $e) {
                // 这是进行验证异常捕获
                return json(['code'=>201,'msg'=>$e->getError()]);
            } catch (\Exception $e) {
                // 这是进行异常捕获
                return json(['code'=>201,'msg'=>$e->getMessage()]);
            }
        }
    }
    public function imgup(Request $request){
        $file = $request->file('image');
        if($file){
            $info = $file->validate(['size'=>9004800,'ext'=>'jpg,png,gif,jpeg'])->move('links');
            if($info){
                $src = '/links/'.$info->getSaveName();
                return json(['code'=>0,'msg'=>'上传成功','data'=>$src]);
            }else{
                // 上传失败获取错误信息
                $error = $file->getError();
                return json(['code'=>2,'msg'=>'上传失败','data'=>$error]);
            }
        }
    }

    public function linksdel(Request $request){
	    if($request->isPost()){
            $params = $request->param();
            $data = $params['data'];
            foreach ($data as $key=>$value){
                $res = LinkModel::destroy($value['id']);
            }
            return json(['code'=>200,'msg'=>'删除成功']);

        }


    }
}