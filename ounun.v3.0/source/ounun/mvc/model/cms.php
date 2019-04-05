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
    protected static $instance;

    /** @var \ounun\pdo */
    public $db;

    /**
     * cms constructor.
     * @param \ounun\pdo $db
     */
    public function __construct(\ounun\pdo $db = null)
    {
        if ($db) {
            $this->db = $db;
        }
        static::$instance = $this;
    }
}