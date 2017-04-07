/**
 * @Author: 杰少Pakey
 * @Email : Pakey@qq.com
 * @File  : controller.php
 */
namespace Kuxin;

class Controller
{
    public function init() -> void
    {
    }
    
    public function ajax(data, type = "json")
    {
        Response::setType(type);
        return data;
    }

}