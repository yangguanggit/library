<?php
/**
 * 文件上传类
 * @property object $self 对象实例
 * @property string $file 文件数组（$_FILES）
 * @property string $path 上传文件保存目录
 * @property string $url 上传成功文件地址
 * @property int $max 最大上传文件大小（单位M）
 * @property string $error 错误信息
 * @property array $ext 文件扩展名
 */
class FileUpload{
    protected static $self;
	protected $file;
	protected $path;
	protected $url;
	protected $max;
	protected $error;
	protected $ext = array(
		'image'=>array('png', 'jpg', 'gif', 'jpeg'),
		'document'=>array('txt', 'doc', 'xls', 'ppt', 'pdf', 'docx', 'xlsx', 'pptx'),
		'compress'=>array('zip', 'rar')
	);

    /**
     * 初始化
     * @param null
     * @return object 对象实例
     */
    public static function init(){
        if(self::$self === null){
            self::$self = new self();
        }
        return self::$self;
    }

    /**
     * 构造函数
     * @param null
     * @return null
     */
    protected function __construct(){
    	
    }

	/**
     * 转换文件数组格式（多文件上传时使用）
     * @param null
     * @return array 转换后文件数组
     */
	protected function exchange(){
		$array = array();
		foreach($this->file as $k => $v){
			foreach($v as $i => $j){
				$array[$i][$k] = $j;
			}
		}
		return $array;
	}

	/**
     * 判断文件是否上传
     * @param string $name 文件名
     * @return bool
     */
	protected function isUpload($name){
		return is_uploaded_file($name);
	}

	/**
     * 检查文件大小
     * @param int $size 文件大小
     * @return bool
     */
	protected function checkSize($size){
		if($size>$this->max*1024*1024 || $size==0){
			$this->error = '文件不能大于'.$this->max.'M，请重新上传';
	        return false;
		}
		return true;
	}

	/**
     * 检查文件格式
     * @param string $name 文件名
     * @param string $type 文件类型
     * @return bool|string 成功返回文件类型，失败返回false
     */
	protected function checkExtension($name, $type){
		$info = pathinfo($name);
        $ext = strtolower($info['extension']);
		if($type=='all'){
	    	if(in_array($ext, $this->ext['image'])){
	    		$type = 'image';
	    	}else if(in_array($ext, $this->ext['document'])){
	    		$type = 'document';
	    	}else if(in_array($ext, $this->ext['compress'])){
	    		$type = 'compress';
	    	}else{
	        	$this->error = '文件格式不正确，请上传图片、文档、压缩文件';
	    	}
	    }else if($type=='image' && !in_array($ext, $this->ext['image'])){
	        $this->error = '文件格式不正确，请上传图片';
	    }else if($type=='document' && !in_array($ext, $this->ext['document'])){
	        $this->error = '文件格式不正确，请上传文档';
	    }else if($type=='compress' && !in_array($ext, $this->ext['compress'])){
	        $this->error = '文件格式不正确，请上传压缩文件';
	    }
	    if($this->error){
	    	return false;
	    }else{
	    	return $type;
	    }
	}

	/**
     * 创建目录
     * @param string $dir 文件保存目录
     * @return null
     */
	protected function makeDir($dir=''){
		$this->path = $_SERVER['DOCUMENT_ROOT'].'/upload';
		$this->url = '/upload';
		if($dir){
			$this->path .= '/'.$dir;
			$this->url .= '/'.$dir;
		}
		if(!is_dir($this->path)){
		    mkdir($this->path, 0777, true);
		    chmod($this->path, 0777);
		}
	}

	/**
     * 生成新的文件名
     * @param string $name 原文件名
     * @return null
     */
	protected function generateName($name){
		$name = date('YmdHis').mt_rand(10, 99).$name;
		$this->path .= '/'.$name;
		$this->url .= '/'.$name;
	}

	/**
     * 移动临时文件到上传文件保存目录
     * @param string $name 临时文件名
     * @return bool
     */
	protected function moveUpload($name){
		return move_uploaded_file($name, $this->path);
	}

	/**
     * 单文件上传
     * @param string $name 表单文件控件名
     * @param string $type 文件类型
     * @param int $max 最大上传文件大小（单位M）
     * @param string $dir 文件保存目录
     * @return array|bool 成功返回数组，失败返回错误信息
     */
	public function upload($name, $type='all', $max=2, $dir=''){
		if(empty($_FILES[$name])){
			return '数据错误';
		}
		$this->file = $_FILES[$name];
		$this->error = $this->file['error'];
		$this->max = $max;
		$tempName = $this->file['tmp_name'];
		$name = $this->file['name'];
        $size = $this->file['size'];
        if($this->isUpload($tempName) && $this->checkSize($size) && ($fileType=$this->checkExtension($name, $type))){
			$this->makeDir($dir);
    		$this->generateName($name);
    		if($this->moveUpload($tempName)){
    			return array('name'=>$name, 'type'=>$fileType, 'url'=>$this->url);
    		}
        }
        return $this->error;
	}

	/**
     * 多文件上传
     * @param string $name 表单文件控件名
     * @param string $type 文件类型
     * @param int $max 最大上传文件大小（单位M）
     * @param string $dir 文件保存目录
     * @return array|bool 成功返回数组，失败返回错误信息
     */
	public function multipleUpload($name, $type='all', $max=2, $dir=''){
		if(empty($_FILES[$name])){
			return '数据错误';
		}
		$this->file = $_FILES[$name];
		$this->file = $this->exchange();
		$this->max = $max;
		$result = array();
		$success = count($this->file);
		$fail = 0;
		foreach($this->file as $v){
			$tempName = $v['tmp_name'];
			$name = $v['name'];
	        $size = $v['size'];
	        if($this->isUpload($tempName) && $this->checkSize($size) && ($fileType=$this->checkExtension($name, $type))){
				$this->makeDir($dir);
        		$this->generateName($name);
        		if($this->moveUpload($tempName)){
        			$result['data'][] = array('name'=>$name, 'type'=>$fileType, 'url'=>$this->url);
        			continue;
        		}
	        }
        	$success--;
        	$fail++;
		}
		
		$result['success'] = $success;
		$result['fail'] = $fail;
		return $result;
	}
}
?>