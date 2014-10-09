##todo
1、其他程序的index.php文件引用绝对路径
2、版权网址错误
3、小说小偷的namelist区块 改为模版中传递参数的值 不传递模版参数的key


##mind
1、array(
            'module'=>array(
                'user'=>array(
                    模块1=>地址1
                    模块2=>地址2
                )
            )
            'block'=>array()
        )
根据这个map来做自动加载

2、
模块必须设置了才允许访问
