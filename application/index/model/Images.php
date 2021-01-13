<?php
namespace app\index\model;

use think\Model;

class Images extends Model
{
    protected $autoWriteTimestamp = true;  
     
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