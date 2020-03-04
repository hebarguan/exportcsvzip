<?php
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
        if ($rowCount < $this->limit) {
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
        $boo = new \Guan\Csv\Boo('./');
        $this->createCsv($boo, $limit, $page);
        $boo->exportCsv();
    }

}
