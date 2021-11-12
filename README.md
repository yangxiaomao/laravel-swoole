laravel-swoole & Laravel7

swoole初体验

项目根目录执行命令：php artisan swoole:http start

测试数据：php 同步与异步的性能较量

条件1：从redis队列中取数据（500）

条件2：每条数据处理时，入库一条insert语句，同时usleep(100000) 100毫秒 

（由于使用swoole协程，异步执行程序，mysql最大连接数直接被干蹦了，
  我本地数据库的最大连接数据，我设置了512，所以这里用500测试）

结果：
 
同步IO阻塞（sync）执行时长：52.024203062057 秒

异步协程 （async）执行时长：1.2969169616699 秒

其实PHP也很快的，只要你用的好

测试应用（模拟为10万用户推送消息，发送优惠劵消息）

10万条数据（调用方法延迟100毫秒） （redis队列中取数据，数据库入库）执行时间：53.250306129456 秒

使用技术点（协程，等待组，mysql连接池，redis连接池）实现，大大减少了IO阻塞带来的性能问题


新增封装方法

一，时间段拆分                 使用场景（例如秒杀时间段）                   Service::splitTimeSlot

二，模拟微信群发红包            使用场景（群红包随机拆分，包含手气最佳）        Service::wxGroupRed

三，Websocket服务（基于swoole） 使用场景（同一场景数据同步推送，基于swoole，windows暂不支持）
    服务启动脚本命令（守护进程启动）   php artisan action:call TestController@webSocketServer
    
四，秒杀功能（防止超卖）         使用场景（秒杀，抢购，防止超卖）
    流程（先设置商品库存数量入redis，使用分布式锁，防止超卖）
    测试脚本命令  php artisan action:call TestController@concurrentCurl
    
五，多句柄模拟并发Curl请求       使用场景（http并发测试）                    CurlService::concurrentCurl


下面是另一个项目（自己编写的php，基于swoole的简单框架）

aglswoole自己编写的swoole框架，技术总结，分享

一，热更新（swoole http服务启动后，修改代码不需要重新启动）

二，spl_autoload_register 自动加载

三，http协议，简单的请求方式（get，post），后续迭代
    ①，定义路由，暂时支持get,post类型，后期封装独立路由文件，路由走控制器操作

四，msyql链接池 （简单的mysql操作）

五，设计了Controller，Method ，（简单C，后续迭代MVC）

六，定义了一些辅助函数

（一个简单的框架，暂时还不成熟，预计2022-01-01开源，敬请期待，并提出宝贵意见，谢谢）
