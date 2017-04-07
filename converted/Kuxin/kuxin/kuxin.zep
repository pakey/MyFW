namespace Kuxin;

use Kuxin\Helper\Json;
use Kuxin\Helper\Jsonp;
use Kuxin\Helper\Xml;
use ReflectionClass;
class Kuxin
{
    public static function init() -> void
    {
        var tmp;
    
        // 设定错误和异常处理
        //register_shutdown_function([__CLASS__, 'shutdown']);
        //set_error_handler(array(__CLASS__, 'error'));
        //set_exception_handler([__CLASS__, 'exception']);
        // 注册AUTOLOAD方法
        let tmp = [__CLASS__, "autoload"];
        spl_autoload_register(tmp);
    }
    
    public static function start() -> void
    {
        var controllerName, controller, actionName, returnn, body;
    
        self::init();
        Router::dispatcher();
        let controllerName =  "app\\controller\\" . Router::controller;
        /** @var \Kuxin\Controller $controller */
        let controller =  Loader::instance(controllerName);
        let actionName =  Router::action;
        controller->init();
        if method_exists(controller, actionName) {
            let returnn =  controller->{actionName}();
            if Response::isAutoRender() {
                switch (Response::getType()) {
                    default:
                        if is_string(returnn) {
                            let body = returnn;
                        }
                }
            } else {
                let body = returnn;
            }
            //设置输出内容
            Response::setBody(body);
        } else {
            trigger_error("控制器[" . controllerName . "]对应的方法[" . actionName . "]不存在", E_USER_ERROR);
        }
    }
    
    protected static function autoload(classname) -> void
    {
        var file;
    
        let file =  PT_ROOT . "/" . strtr(strtolower(classname), "\\", "/") . ".php";
        Loader::import(file);
    }

}
//项目根目录