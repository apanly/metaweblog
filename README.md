MetaWeblog
===================
###主要作用
* 通过MetaWeblog协议同时管理51cto,csdn,sina,163,oschina,cnblogs等博客

###支持博客
* 51cto
* csdn
* sina
* 163
* oschina
* cnblogs
* chinaunix

###主要功能
* 实现了发表新文章
* 实现了编辑文章

### 安装
    
    composer require apanly/metaweblog

### 使用
    
    $url = "https://my.oschina.net/action/xmlrpc";
    $target = new \apanly\metaweblog\MetaWeblog( $url );
    $username = "xxx";
    $passwd = "xxx";
    $target->setAuth( $username,$passwd );
    
    $blog_id = 784865;//等执行了发布新文章之后就会有这个值了
    
    if( $blog_id ){
    	$params = [
    		'title'=> '测试博文标题--编辑',
    		'description'=> '测试博文内容--编辑',
    		'categories'=> [ 1 ]
    	];
    	if( !$target->editPost( $blog_id,$params ) ){
    		var_dump( $target->getErrorMessage() );
    	}
    }else{
    	$params = [
    		'title'=> '测试博文标题',
    		'description'=> '测试博文内容',
    		'categories'=> [ 1 ]
    	];
    	if( $target->newPost( $params ) ){
    		$blog_id = $target->getBlogId();
    		var_dump( $blog_id );
    	}else{
    		var_dump( $target->getErrorMessage() );
    	}
    }
    
###截图
* ![新文章](./static/test_1.jpg)
* ![编辑文章](./static/test_2.jpg)
    
###ToDoList
* 目前代码比较冗余，后续改的更优雅点

###Lecense
PHP Browser is licensed under [The MIT License (MIT)](LICENSE).

###参考资料
* [MetaWeblog 同时管理51cto,csdn,sina,163,oschina,cnblogs等博客](http://www.vincentguo.cn/default/91)


