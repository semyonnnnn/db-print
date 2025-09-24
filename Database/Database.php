<?php
namespace Database;

use ErrorException;
class Database
{
    protected array $env;
    protected string $server;
    protected string $username;
    protected string $password;
    protected string $db;
    protected string $dsn;
    private array $tables_allowed;
    protected \PDO $pdo;

    //eloquent emulation

    private string|null $sql = '';
    private ?array $array_of_values = null;
    private string|null $PDO_MODE;
    private string|null $FETCH_METHOD = null;
    private array $PDO_MODES_VALUES = [
        'FETCH_ASSOC' => \PDO::FETCH_ASSOC,        // Associative array ['col' => value]
        'FETCH_NUM' => \PDO::FETCH_NUM,          // Numeric array [0 => value]
        'FETCH_BOTH' => \PDO::FETCH_BOTH,         // Both numeric & associative (default)
        'FETCH_OBJ' => \PDO::FETCH_OBJ,          // Anonymous object $row->col
        'FETCH_LAZY' => \PDO::FETCH_LAZY,         // Combines FETCH_BOTH + object access
        'FETCH_KEY_PAIR' => \PDO::FETCH_KEY_PAIR,     // First column = key, second = value
        'FETCH_UNIQUE' => \PDO::FETCH_UNIQUE,       // First column as key, ignores duplicates
        'FETCH_GROUP' => \PDO::FETCH_GROUP,        // Groups rows by first column
        'FETCH_COLUMN' => \PDO::FETCH_COLUMN,       // Returns a single column as array
        'FETCH_CLASS' => \PDO::FETCH_CLASS,        // Maps row to a class instance
        'FETCH_INTO' => \PDO::FETCH_INTO,         // Maps row into existing object
        'FETCH_FUNC' => \PDO::FETCH_FUNC,         // Passes row to a function
    ];
    private array $FETCH_METHODS = [
        'fetch' => 'fetch',
        'fetchAll' => 'fetchAll'
    ];



    public function __construct()
    {
        $this->connect();
        $this->tables_allowed = require path('Database/tables_allowed');
    }

    public function __destruct()
    {
        unset(
            $this->sql,
            $this->where_in,
            $this->where,
            $this->PDO_MODE,
            $this->FETCH_METHOD,
            $this->array_of_values
        );
    }

    protected function connect()
    {
        try {
            $this->env = parse_ini_file(path_not_php('.env'));

            $this->server = $this->env['SERVER'];
            $this->username = $this->env['USERNAME'];
            $this->password = $this->env['PASSWORD'];
            $this->db = $this->env['DATABASE'];

            // Add Encrypt=optional;TrustServerCertificate=Yes
            $this->dsn = "sqlsrv:Server=$this->server;Database=$this->db;Encrypt=No;TrustServerCertificate=Yes";
            $this->pdo = new \PDO($this->dsn, $this->username, $this->password);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);


        } catch (\PDOException $e) {
            echo "❌ Connection failed: " . $e->getMessage() . "\n";
        }
    }

    public function prepare(string $query): \PDOStatement
    {
        return $this->pdo->prepare($query);
    }

    public function select(string $table, array|string $columns = "*", bool $distinct = false)
    {
        if (!in_array($table, $this->tables_allowed)) {
            return;
        }

        $actual_columns_parts = [];
        $actual_columns = '';

        if (gettype($columns) == 'string') {
            $actual_columns = $columns;
        } else {
            foreach ($columns as $column) {
                $actual_columns_parts[] = "[$column]";
            }
            $actual_columns = implode(",", $actual_columns_parts);
        }

        $distinct = $distinct ? 'DISTINCT' : '';

        $this->sql = "SELECT $distinct $actual_columns from $table";
        return $this;
    }

    public function where($column, $value, $connector = null, $fetch_method = "fetchAll")
    {
        if (!preg_match('/\bselect\b/i', $this->sql)) {
            throw new ErrorException("OH NO SELECT MISSING");
        }
        if (!$value) {
            throw new ErrorException("NO VALUE");
        }
        $where_exists = preg_match('/\bwhere\b/i', $this->sql);

        $sql = '';
        if ($where_exists) {
            $sql = "$column = :value" . ($connector !== null ? " $connector" : '');

        } else {
            $sql = "WHERE $column = :value" . ($connector !== null ? " $connector" : '');
        }
        $this->sql = $this->sql . " " . $sql;

        $this->array_of_values = ['value' => $value];

        $this->FETCH_METHOD = $this->FETCH_METHODS[$fetch_method];
        $this->PDO_MODE = "FETCH_ASSOC";

        return $this;
    }

    public function where_in($column, $values, $connector = null, $fetch_method = "fetchAll")
    {
        if (!preg_match('/\bselect\b/i', $this->sql)) {
            throw new ErrorException("OH NO SELECT MISSING");
        }
        if (!$values) {
            throw new ErrorException("NO VALUES");
        }

        $where_exists = preg_match('/\bwhere\b/i', $this->sql);


        $values_count = count($values);
        $placeholders = implode(",", array_fill(0, $values_count, "?"));

        $sql = '';
        if ($where_exists) {
            $sql = "$column in ($placeholders)" . ($connector !== null ? " $connector" : '');

        } else {
            $sql = "WHERE $column in ($placeholders)" . ($connector !== null ? " $connector" : '');
        }
        // dd('im here');
        $this->sql = $this->sql . " " . $sql;



        $this->array_of_values = isset($this->array_of_values) ? array_values(array_merge($this->array_of_values, $values)) : $values;
        $this->sql = $this->aov_setter($this->sql);

        $this->FETCH_METHOD = $this->FETCH_METHODS[$fetch_method];
        $this->PDO_MODE = "FETCH_ASSOC";

        return $this;
    }

    private function aov_setter($sql): string
    {
        $pattern = "/:\S+/";

        $arr = explode(' ', $sql);
        foreach ($arr as $key => &$word) {
            if (preg_match($pattern, $word)) {
                $word = "?";
            }
        }

        $sql = implode(" ", $arr);
        return $sql;
    }

    public function fetch_kp(string $key, string $value, array $array_of_data, $table, $param, $fetch_method = 'fetchAll'): static
    {
        $placeholders = array_fill(0, count($array_of_data), "?");
        $placeholders = implode(",", $placeholders);

        $this->array_of_values = $array_of_data;
        $this->sql = "SELECT $key, $value from $table where $param in($placeholders)";
        $this->PDO_MODE = "FETCH_KEY_PAIR";
        $this->FETCH_METHOD = $this->FETCH_METHODS[$fetch_method];

        return $this;
    }

    public function fetch_unique_kp(string $key, string $value, array $array_of_data, $table, $param, $fetch_method = 'fetchAll')
    {
        if (!in_array($table, $this->tables_allowed)) {
            return;
        }
        $clean_arr_of_data = array_values(array_unique($array_of_data));
        $placeholders = array_fill(0, count($clean_arr_of_data), "?");
        $placeholders = implode(",", $placeholders);

        $this->array_of_values = $clean_arr_of_data;
        $this->sql = "SELECT $key, $value from $table where $param in($placeholders)";
        $this->PDO_MODE = "FETCH_KEY_PAIR";
        $this->FETCH_METHOD = $this->FETCH_METHODS[$fetch_method];

        return $this;
    }



    public function get()
    {

        //TODO: check if sql has where and where in if so
        //turn placeholders like :pl into ?
        //merge arrays
        //execute normally 

        $stmt = $this->pdo->prepare($this->sql);

        isset($this->array_of_values) && $this->array_of_values !== null ? $stmt->execute($this->array_of_values) : $stmt->execute();
        $CURRENT_FETCH_METHOD = $this->FETCH_METHOD ?? 'fetchAll';
        $CURRENT_PDO_MODE = $this->PDO_MODE ?? 'FETCH_ASSOC';


        $result = call_user_func_array([$stmt, $CURRENT_FETCH_METHOD], [$this->PDO_MODES_VALUES[$CURRENT_PDO_MODE]]);
        $this->__destruct();
        return $result;
    }
}