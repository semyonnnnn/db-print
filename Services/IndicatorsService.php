<?php
use Database\Database;
class IndicatorsService
{
    private Database $db;
    private int $oktmo;
    private int $year;


    private array $tables;
    public $data;

    public function __construct(int $oktmo, int $year)
    {

        $oktmo = (string) $oktmo;
        if (!in_array(strlen($oktmo), [8, 11])) {
            throw new InvalidArgumentException('OKTMO must be 8 or 11 chars!');
        }
        $this->oktmo = (int) $oktmo;
        $this->year = $year;
        $this->db = new Database();

        $this->tables = $this->this_is_where_fun_begins();

        $this->data = ['oktmo' => $oktmo, 'names' => $this->fetch_names()];

    }

    private function this_is_where_fun_begins()
    {
        $dirty_list = $this->db->select('p_oktmo_fd')->where("kodzpr", $this->oktmo)->get();

        if (empty($dirty_list)) {
            return '';
        }
        // dd($dirty_list);

        $union_all_parts = [];

        foreach ($dirty_list as $item) {
            $namefd = (int) $item['namefd'];
            $union_all_parts[] = "SELECT '$namefd' AS namefd WHERE EXISTS (SELECT 1 FROM [dbo].[fd_"
                . (int) $namefd
                . "] WHERE god in($this->year) AND oktmo = $this->oktmo)";
        }

        $query = implode(" UNION ALL ", $union_all_parts);

        // dd($query);

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        // dd($tables);
        return $tables;
    }

    private function fetch_names()
    {
        $flat_names = $this->db->select('s_pok', ['namepok', 'kodpok', 'kodei', 'namepole'])->where_in('kodpok', $this->tables)->get();
        $parent_codes = $this->db->select('s_pok_v', 'kodpokv', true)->where_in('kodpokn', $this->tables)->get();


        foreach ($parent_codes as &$item) {
            $item = $item['kodpokv'];
        }
        $parent_names = $this->db->select('s_pok', ['namepok', 'kodpok', 'kodei', 'namepole'])->where_in('kodpok', $parent_codes)->get();

        $tables_string = implode(",", $this->tables);

        foreach ($parent_names as &$item) {
            // dd('im here');
            $the_thing = $this->db->select('s_pok_v', ['kodpokn', 'kodpokv'])->where('kodpok', $item['kodpok']);
            // dd($the_thing);
            $query = "SELECT [kodpokn], [kodpokv] FROM [munst1165].[dbo].[s_pok_v] WHERE kodpokv = {$item['kodpok']} and kodpokn in ($tables_string)";
            $children = $this->query_runner($query);



            $item['children'] = $children;
            foreach ($item['children'] as &$child) {
                foreach ($flat_names as $f_item) {
                    if ($f_item['kodpok'] == $child['kodpokn']) {
                        $child['namepok'] = $f_item['namepok'];
                        $child['kodei'] = $f_item['kodei'];
                        $child['namepole'] = trim($f_item['namepole'], " ");
                    }
                }
            }
        }


        $zns = [];
        $fds = [];

        $kodeis = [];


        foreach ($parent_names as &$parent) {
            foreach ($parent['children'] as &$child) {
                $kodeis[] = $child['kodei'] ?? null;

                $zns[] = 'zn' . $child['kodpokn'];
                $fds[] = 'fd_' . $child['kodpokn'];
            }

        }

        $sql_parts_zns = [];
        foreach ($zns as $i => $zn) {
            $table = $fds[$i];
            $sql_parts_zns[] = "SELECT '$zn' as zn, $zn as value from $table where oktmo = $this->oktmo and god = $this->year";

        }

        // dd($this->db->fetch_all_key_pairs_unique('kodei', 'nameei', $kodeis, 's_ei', 'kodei'));

        $kodeis = $this->db->fetch_unique_kp('kodei', 'nameei', $kodeis, 's_ei', 'kodei')->get();


        $query_zns = implode(" UNION ALL ", $sql_parts_zns);


        $stmt_zns = $this->db->prepare($query_zns);
        $stmt_zns->execute();
        $values_zns = $stmt_zns->fetchAll(PDO::FETCH_ASSOC);


        foreach ($parent_names as &$parent) {
            foreach ($parent['children'] as &$child) {

                $child['nameei'] = $kodeis[$child['kodei']];
                foreach ($values_zns as $zn) {
                    $namepole = trim($child['namepole'], " ");
                    if ($zn['zn'] == $namepole) {
                        $child['numbers'][] = $zn['value'];
                    }
                }
            }
        }

        return $parent_names;
    }

    private function query_runner(string $query): array
    {
        if (!$this->tables) {
            echo 'TABLES ARE EMPTY!';
            die();
        }
        $stmt = $this->db->prepare($query);
        $stmt->execute($this->tables);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }
}