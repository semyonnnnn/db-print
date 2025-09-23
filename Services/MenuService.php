<?php
use Database\Database;

class MenuService
{
    private string $table_kodes = "p_oktmo_v";
    private string $table_names = "p_oktmo";
    private Database $db;
    private array $menu_oktmo;
    public function __construct()
    {
        $this->db = new Database();
        $this->menu_oktmo();
    }


    private function menu_oktmo()
    {
        $data_kodes = $this->db->select($this->table_kodes)->get();
        foreach ($data_kodes as $item) {
            $kodzprn = $item['kodzprn'];
            if ($item['kodzprv'] == 0) {
                $this->menu_oktmo[$kodzprn] = [
                    'item' => $item,
                    'children' => []
                ];
            }
        }

        foreach ($data_kodes as $item) {
            $kodzprv = $item['kodzprv'];
            $kodzprn = $item['kodzprn'];

            if ($kodzprv != 0 && isset($this->menu_oktmo[$kodzprv])) {
                $this->menu_oktmo[$kodzprv]['children'][$kodzprn] = $item;
            }

        }
        $data_names = $this->db->select($this->table_names)->get();


        foreach ($this->menu_oktmo as $outer_key => $outer_value) {
            foreach ($data_names as $data_item) {
                if ($data_item['kodzpr'] == $outer_key) {
                    $name = $data_item['namezpr'];
                    $this->menu_oktmo[$outer_key]['item']['name'] = $name;
                }
            }

            foreach ($outer_value['children'] as $child_key => $child_value) {
                foreach ($data_names as $data_item) {
                    if ($data_item['kodzpr'] == $child_key) {
                        $name = $data_item['namezpr'];
                        $this->menu_oktmo[$outer_key]['children'][$child_key]['name'] = $name;
                    }
                }
            }
        }
    }

    public function get_menu_oktmo()
    {
        return $this->menu_oktmo;
    }
}