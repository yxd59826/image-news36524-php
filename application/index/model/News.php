<?php
namespace app\index\model;

use think\Model;

class News extends Model
{
    protected $autoWriteTimestamp = true;

    public function profile()
    {
        return $this->hasOne('NewsData','id');
    }

    public function catgory()
    {
        return $this->hasMany('catgory','id','catid');
    }

    public function getThumbAttr($value,$data){
    	if($value){
    		if (strpos($value,'http')!==false) {
    			return $value;
    		}else{
    			return "https://image.news36524.com".$value;
    		}    		
    	}    	
    }
}