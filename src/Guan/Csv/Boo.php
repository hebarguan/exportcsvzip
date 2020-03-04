<?php
/**
 * @author guanhuaihai@gmail.com
 * @date 2020-03-04
 * @desc 本屌乱写的
 */
namespace Guan\Csv;
use ZipArchive;

class Boo
{
    /**
     * 压缩包文件名
     */
    private $zipfile = '';

    /**
     * Csv文件名
     */
    private $filename = '';

    /**
     * 文件命名后缀
     */
    private $count = 1;

    /**
     * 压缩包的文件列表
     */
    private $files = [];

    /**
     * 临时存储文件的目录
     */
    private $temp = '';

    /**
     * Csv文件后缀名
     */
    private $ext = 'csv';

    /**
     * Zip文件完整路径
     */
    private $zipfilePath = '';

    /**
     * 初始化构造器
     * @param string $path 选择存放的路径 默认是PHP默认的上传文件临时存储目录
     * @param string $filename 导出的文件名
     * 
     * @return void
     */
    public function __construct($path = '', $filename = 'deault', $memoryLimit = '300M')
    {
        // 临时存储完整路径
        $this->temp = empty($path) ? sys_get_temp_dir().'/' : rtrim($path, '/').'/';
        // 创建一个用于存放文件的唯一目录
        $this->createUniquePath();
        // Csv文件名
        $this->filename = $filename;
        // 压缩包文件名
        $this->zipfile = $this->filename.'.zip';
        
        $this->zipfilePath = $this->temp.$this->zipfile;

        set_time_limit(0);
        // 设置内存
        ini_set('memory_limit', $memoryLimit);
    }

    /**
     * 创建用于存放文件的目录
     * 
     * @return void
     */
    private function createUniquePath()
    {
        $dirname = uniqid().date('YmdHis');
        $this->temp = $this->temp.$dirname.'/';
        @mkdir($this->temp);
    }

    /**
     * 将结果数据导出为Csv文件
     * 
     * @param array $result 结果集
     * 
     * @return void
     */
    public function create($result)
    {
        if (count($result) <= 0) {
            return false;
        }
        $csv = $this->temp.$this->filename.'_'.$this->count.'.'.$this->ext;
        // 若该文件已存在则删除
        if (is_file($csv)) {
            @unlink($csv);
        }
        // 创建并打开文件
        $handle = fopen($csv, 'a');
        foreach ($result as $row)
        {
            fputcsv($handle, $row);
        }
        $this->files[] = $csv;
        fclose($handle);
        // 文件计数器
        $this->count++;
        // 清除内存
        $result = null;
    }

    /**
     * 将导出的Csv文件打包成Zip压缩包
     * 并删除打包后的Csv文件
     * 
     * @return void
     */
    private function zip()
    {
        // 清除已存在的文件
        if (is_file($this->zipfilePath)) {
            @unlink($this->zipfilePath);
        }
        $zip = new ZipArchive;
        $zip->open($this->zipfilePath, ZipArchive::CREATE);
        foreach ($this->files as $file)
        {
            $zip->addFile($file, ltrim($file, $this->temp));
        }
        $zip->close();
    }

    /**
     * 导出压缩文件 Zip
     * 
     * @return response
     */
    public function exportCsv()
    {
        $this->zip();
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment;filename="'.$this->zipfile.'"');
        header('Cache-Control: max-age=0');
        ob_end_clean();
        readfile($this->zipfilePath);
    }

    /**
     * 保存文件
     * 
     * @param string $filename 要保存的压缩文件名
     * 
     * @return string 返回文件完整地址
     */
    public function save($filename = null)
    {
        $this->zip();
        if ($filename) {
            @rename($this->zipfilePath, $this->temp.$filename);
            return $this->temp.$filename;
        }
        return $this->zipfilePath;
    }
}
