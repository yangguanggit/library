<?php
/**
 * 自动加载类
 * @author lilei
 */
class AutoLoader
{
    protected static $that;
    protected $path;
    protected $ext = '.php,.inc';

    /**
     * 初始化
     * @return $this 对象实例
     */
    public static function init()
    {
        if (empty(self::$that) || !(self::$that instanceof self)) {
            self::$that = new self();
        }
        return self::$that;
    }

    /**
     * 构造函数
     */
    protected function __construct()
    {
        $this->path = implode(array(
            __DIR__ . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR,
            __DIR__ . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR,
            __DIR__ . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR,
        ), PATH_SEPARATOR);

        if (function_exists('__autoload')) {
            spl_autoload_register('__autoload');
        }
        spl_autoload_register(array($this, 'autoload'));
    }

    /**
     * 禁止克隆
     */
    protected function __clone()
    {

    }

    /**
     * 加载类库
     * @param string $class 类名
     */
    protected function autoload($class)
    {
        set_include_path($this->path);
        spl_autoload_extensions($this->ext);
        spl_autoload($class);
    }
}
?>