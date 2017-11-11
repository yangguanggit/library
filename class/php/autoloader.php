<?php
/**
 * 自动加载类
 * @author lilei
 * @property object $self 对象实例
 * @property string $ext 扩展名
 * @property array $path 类库路径
 */
class AutoLoader{
    protected static $self;
    protected $ext = '.php,.inc';
    protected $path;

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
        $this->path = implode(array(
            __DIR__.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR,
            __DIR__.DIRECTORY_SEPARATOR.'model'.DIRECTORY_SEPARATOR,
            __DIR__.DIRECTORY_SEPARATOR.'controller'.DIRECTORY_SEPARATOR,
        ), PATH_SEPARATOR);

        if(function_exists('__autoload')){
            spl_autoload_register('__autoload');
        }
        spl_autoload_register(array($this, 'autoload'));
    }

    /**
     * 加载类库
     * @param null
     * @return null
     */
    protected function autoload($class){
        set_include_path($this->path);
        spl_autoload_extensions($this->ext);
        spl_autoload($class);
    }
}
?>