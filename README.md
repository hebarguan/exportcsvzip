## 说明

支持分片导出大量表数据，每片数据为一个独立的csv文件，并生成一个Zip压缩包

## Installation

需要libzip和phpzip扩展 (自行安装)

手动安装：

```
composer require hebarguan/exportcsv
```

或者

```json
{
    "require": {
        "hebarguan/exportcsv": "^1.0.2"
    }
}
```

如果你使用的不是框架没有自动导入autoload.php则要有手动引入

```php
require_once(__DIR__.'/vender/autoload.php')
```


## Using

控制器
```php
class Controller
{
    /**
     * 模型：用于读取数据
     */
    private $model = null;

    /**
     * 构造器
     */
    public function __construct()
    {
        $this->model = new Model();
    }

    /**
     * 生成Csv文件
     * 
     * @param Object $boo 依赖实例
     * @param int $limit 每页限制
     * @param int $page 页数
     * 
     * @return void
     */
    private function createCsv($boo, $limit, $page)
    {
        $rowCount = 0;
        // 执行结果
        $result = $this->model->query($limit, $page, $count);
        // 使用实例把结果导成单个Csv文件
        $boo->create($result);
        // 清除内存
        $result = null;
        // 如果取出的结果数量少于限制的条数，说明结果已经取完
        if ($rowCount < $limit) {
            return true;
        } else {
            // 否则读取第二页并生成为一个Csv文件
            $page++;
            $this->createCsv($boo, $limit, $page);
        }
    }

    /**
     * 执行导出任务
     * limit 参数为每次取出的行数，值越大占用的内存就越多，可根据该条件设置实例的第三个参数
     * 
     * @return void
     */
    public function run()
    {
        $page = 1;
        $limit = 10000;
        /**
         * @param string $path 文件存放目录 默认：php的临时存储目录
         * @param string $filename 要保存的文件名 默认：default
         * @param string $memoryLimit 内存限制  默认：300M
         */
        $boo = new Guan\Csv\Boo('./');
        $this->createCsv($boo, $limit, $page);
        $boo->exportCsv();
    }

}
```

模型
```php
class Model
{
    /**
     * 句柄
     */
    private $conn = null;

    public function __construct()
    {
        $this->connect();
    }

    /**
     * 连接数据库
     */
    public function connect()
    {
        $this->conn = new PDO('mysql:host=127.0.0.1;dbname=test', 'root', 'test');
    }

    /**
     * 查询结果
     * 
     * @param int $limit
     * @param int $page
     * @param int $count
     * 
     * @return array
     */
    public function query($limit, $page, &$count)
    {
        $sql = 'select * from system_log limit '.$limit.' offset '.$limit*($page - 1);
        $query = $this->conn->query($sql);
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        $count = $query->rowCount();
        // 清除结果集
        $query = null;
        return $result;
    }
}
```

如果你使用的是php框架下面的就不用看了，哈哈哈

执行导出 (自行改下PDO中的配置)
```php

$test = new Controller();
$test->run();

```

上面的代码在相对还是比较简单的，直接复制到你的项目下面，改下数据查询方法就行
