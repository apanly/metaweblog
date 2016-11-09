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
    $target->setAuth( $username,$passwd );
    
    $params = [
        'title'=> $title,
        'description'=> $content,
        'categories'=> $catlog
    ];
            
    $target->newPost( $params );
    $blog_id = $target->getResponse();
    
    $target->editPost(  $blog_id ,$params );
    
###ToDoList
* 目前代码比较冗余，后续改的更优雅点

###Lecense
PHP Browser is licensed under [The MIT License (MIT)](LICENSE).


###参考资料
* [MetaWeblog 同时管理51cto,csdn,sina,163,oschina,cnblogs等博客](http://www.vincentguo.cn/default/91)


