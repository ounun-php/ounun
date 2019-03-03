<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 2019/3/3
 * Time: 01:07
 */

namespace ounun\mvc\model;


class cms
{
    /** @var array DB 相关 */
    private static $_instance = [];

    /**
     * @param string $key
     * @param array  $config
     * @return $this 返回数据库连接对像
     */
    public static function instance(\ounun\pdo $db):self
    {
        if(empty(self::$_instance)) {
            self::$_instance = new self($db);
        }
        return self::$_instance;
    }

    /**
     * cms constructor.
     * @param \ounun\pdo $db
     */
    public function __construct(\ounun\pdo $db)
    {
        $this->db = $db;
    }

    /** @var \ounun\pdo */
    public $db;
}