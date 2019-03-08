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
    /** @var cms DB 相关 */
    protected static $_instance;

    /**
     * @param \ounun\pdo $db
     * @return $this  返回数据库连接对像
     */
    public static function instance(\ounun\pdo $db):self
    {
        if(empty(static::$_instance)) {
            static::$_instance = new static($db);
        }
        return static::$_instance;
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