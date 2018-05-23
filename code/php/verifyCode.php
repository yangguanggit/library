<?php
/**
 * 图片验证码类（需要安装gd库）
 * @author lilei
 */
class VerifyCode
{
    protected static $that;
    protected $image;
    protected $string = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    protected $code;
    protected $config = array(
        'length' => 4,
        'width' => 150,
        'height' => 50,
        'background' => array(250, 250, 250),
        'font' => __DIR__ . '/font/Elephant.ttf',
        'fontSize' => 20,
        'fontColor' => array(0, 0, 0)
    );

    /**
     * 初始化
     * @param array $config 配置数组
     * @return $this 对象实例
     */
    public static function init($config = array())
    {
        if (empty(self::$that) || !(self::$that instanceof self)) {
            self::$that = new self($config);
        }
        return self::$that;
    }

    /**
     * 构造函数
     * @param array $config 配置数组
     */
    protected function __construct($config = array())
    {
        try {
            if ($config) {
                $this->config = $config + $this->config;
            }
            $this->image = imagecreatetruecolor($this->config['width'], $this->config['height']);
            $rgb = $this->config['background'];
            $background = imagecolorallocate($this->image, $rgb[0], $rgb[1], $rgb[2]);
            imagefill($this->image, 0, 0, $background);
        } catch (Exception $e) {
            die('请安装gd库');
        }
    }

    /**
     * 禁止克隆
     */
    protected function __clone()
    {

    }

    /**
     * 生成验证码
     * @return $this 对象实例
     */
    protected function generate()
    {
        for ($i = 0; $i < $this->config['length']; $i++) {
            $this->code .= $this->string[mt_rand(0, strlen($this->string) - 1)];
        }
        return $this;
    }

    /**
     * 保存session
     * @return $this 对象实例
     */
    protected function save()
    {
        session_start();
        $_SESSION['verifyCode'] = strtolower($this->code);
        return $this;
    }

    /**
     * 画出验证码
     * @return $this 对象实例
     */
    protected function draw()
    {
        $rate = $this->config['width'] / $this->config['length'];
        if (function_exists('imagettftext')) {
            for ($i = 0; $i < $this->config['length']; $i++) {
                $color = imagecolorallocate($this->image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
                // 同时需要gd库和freetype库
                imagettftext($this->image, $this->config['fontSize'], mt_rand(-30, 30), $i * $rate + mt_rand(1, 5), $this->config['height'] / 1.5, $color, $this->config['font'], $this->code[$i]);
            }
        } else {
            for ($i = 0; $i < $this->config['length']; $i++) {
                $color = imagecolorallocate($this->image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
                imagestring($this->image, 5, $i * $rate + mt_rand(5, 10), $this->config['height'] / 3, $this->code[$i], $color);
            }
        }
        return $this;
    }

    /**
     * 画出干扰线、雪花
     * @return $this 对象实例
     */
    protected function interfere()
    {
        // 线条
        for ($i = 0; $i < 5; $i++) {
            $color = imagecolorallocate($this->image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imageline($this->image, mt_rand(0, $this->config['width']), mt_rand(0, $this->config['height']), mt_rand(0, $this->config['width']), mt_rand(0, $this->config['height']), $color);
        }
        // 雪花
        for ($i = 0; $i < 10; $i++) {
            $color = imagecolorallocate($this->image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagestring($this->image, mt_rand(1, 5), mt_rand(0, $this->config['width']), mt_rand(0, $this->config['height']), '*', $color);
        }
        return $this;
    }

    /**
     * 输出图片
     */
    protected function out()
    {
        header("Content-Type:image/png");
        header("Expires:-1");
        header("Pragma:no-cache");
        header("Cache-Control:no-cache,must-revalidate");
        imagepng($this->image);
        imagedestroy($this->image);
    }

    /**
     * 显示图片
     */
    public function show()
    {
        $this->generate()
            ->save()
            ->draw()
            ->interfere()
            ->out();
    }
}
?>