<?php
/**
 * 结果模型
 */
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
        $this->conn = new PDO('mysql:host=127.0.0.1;dbname=compass', 'root', 'hebarguan');
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
