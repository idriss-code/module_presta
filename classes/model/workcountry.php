<?php

class Workcountry extends ObjectModel
{
    /** @var integer Workcountry ID */
    public $id;

    /** @var string Workcountry name */
    public $name;

    /** @var string Workcountry ISO code 2 char */
    public $iso;

    /** @var string Workcountry description */
    public $description;

    public static $definition = [
        'table' => 'workcountry',
        'primary' => 'id_workcountry',
        'fields' => [
            'name' => ['type' => self::TYPE_STRING],
            'iso' => ['type' => self::TYPE_STRING],
            'description' => ['type' => self::TYPE_STRING],
        ]
    ];

    public static function getList()
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'];
        return Db::getInstance()->executeS($sql);
    }

    public static function getByIso($iso):Workcountry
    {
        $id = Db::getInstance()->getValue('SELECT id_workcountry 
                        FROM ' . _DB_PREFIX_ . 'workcountry 
                        WHERE iso="' . $iso . '"');
        return new Workcountry($id);
    }
}
