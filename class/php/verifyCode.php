<?php
/**
 * 图片验证码类（需要安装gd库）
 * @author lilei
 * @property object $self 对象实例
 * @property resource $image 画布
 * @property string $string 随机字符串
 * @property string $code 验证码
 * @property int $length 验证码长度
 * @property int $width 画布宽度
 * @property int $height 画布高度
 * @property resource $background 画布背景色
 * @property string $font 字体文件路径
 * @property int $fontSize 字体大小
 * @property resource $fontColor 字体颜色
 */
class VerifyCode{
    protected static $self;
	protected $image;
	protected $string = '23456789abcdefghkmnpqrstuvwxyzABCDEFGHKMNPQRSTUVWXYZ';
	protected $code;
	protected $length = 4;
	protected $width = 150;
	protected $height = 50;
	protected $background;
	protected $font = __DIR__.'/font/Elephant.ttf';
	protected $fontSize = 20;
	protected $fontColor;

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
		try{
			$this->image = imagecreatetruecolor($this->width, $this->height);
			$this->background = imagecolorallocate($this->image, 250, 250, 250);
			imagefill($this->image, 0, 0, $this->background);
			$this->fontColor = imagecolorallocate($this->image, 0, 0, 0);
		}catch(Exception $e){
			die('请安装gd库');
		}
	}

	/**
     * 生成验证码
     * @param null
     * @return null
     */
	protected function createCode(){
		for($i=0; $i<$this->length; $i++){
			$this->code .= $this->string[mt_rand(0, strlen($this->string)-1)];
		}
	}

	/**
     * 画出验证码
     * @param null
     * @return null
     */
	protected function createText(){
		$average = $this->width/$this->length;
		if(function_exists('imagettftext')){
			for($i=0; $i<$this->length; $i++){
				$this->fontColor = imagecolorallocate($this->image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
				// 同时需要gd库和freetype库
				imagettftext($this->image, $this->fontSize, mt_rand(-30, 30), $i*$average+mt_rand(1, 5), $this->height/1.5, $this->fontColor, $this->font, $this->code[$i]);
			}
		}else{
			for($i=0; $i<$this->length; $i++){
				$this->fontColor = imagecolorallocate($this->image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
	        	imagestring($this->image, 5, $i*$average+mt_rand(5, 10), $this->height/3, $this->code[$i], $this->fontColor);
			}
		}
	}

	/**
     * 画出干扰线、雪花
     * @param null
     * @return null
     */
	protected function createLine(){
		// 线条
		for($i=0; $i<5; $i++){
			$color = imagecolorallocate($this->image, mt_rand(0, 255),mt_rand(0, 255), mt_rand(0, 255));
			imageline($this->image, mt_rand(0, $this->width), mt_rand(0, $this->height), mt_rand(0, $this->width), mt_rand(0, $this->height), $color);
		}
		// 雪花
		for($i=0; $i<10; $i++){
			$color = imagecolorallocate($this->image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
			imagestring($this->image, mt_rand(1, 5), mt_rand(0, $this->width), mt_rand(0, $this->height), '*', $color);
		}
	}

	/**
     * 输出图片
     * @param null
     * @return null
     */
	protected function outPut(){
		header("Content-Type:image/png");
	    header("Expires:-1");
	    header("Pragma:no-cache");
	    header("Cache-Control:no-cache,must-revalidate");
		imagepng($this->image);
		imagedestroy($this->image);
	}

	/**
     * 保存session
     * @param null
     * @return null
     */
	protected function save(){
		session_start();
		$_SESSION['verify_code'] = strtolower($this->code);
	}

	/**
     * 显示图片
     * @param null
     * @return null
     */
	public function display(){
		$this->createCode();
		$this->createText();
		$this->createLine();
		$this->outPut();
		$this->save();
	}
}
?>