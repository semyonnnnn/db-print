<?php

require_once path('db/Database');

class Database_debug extends Database
{

    private $table_schema = 'dbo';
    public function query_all()
    {
        $query = "SELECT TABLE_NAME
                      FROM INFORMATION_SCHEMA.TABLES
                      WHERE TABLE_TYPE='BASE TABLE'";

        $stmt = $this->pdo->query($query);
        $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $tables;
    }

    public function table_names()
    {
        $query = "SELECT TABLE_NAME
                  FROM INFORMATION_SCHEMA.TABLES
                  WHERE TABLE_SCHEMA = '{$this->table_schema}'";

        $stmt = $this->pdo->query($query);
        $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $tn = [];

        foreach ($tables as $table) {
            array_push($tn, $table['TABLE_NAME']);
        }

        return $tn;
    }

    public function schema_names()
    {
        $query = "SELECT SCHEMA_NAME
              FROM INFORMATION_SCHEMA.SCHEMATA";

        $stmt = $this->pdo->query($query);
        $schemas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sn = [];

        foreach ($schemas as $schema) {
            array_push($sn, $schema['SCHEMA_NAME']);
        }

        return $sn;
    }


    public function simple_struct()
    {
        $tn = $this->table_names();
        $db_structure = [];
        foreach ($tn as $tableName) {

            // Step 3: Get columns for this table
            $colQuery = "SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, IS_NULLABLE
                 FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = '{$this->table_schema}' AND TABLE_NAME = :tableName";
            $colStmt = $this->pdo->prepare($colQuery);
            $colStmt->execute(['tableName' => $tableName]);
            $columns = $colStmt->fetchAll(PDO::FETCH_ASSOC);

            // Step 4: Store it in db_structure
            $db_structure[$tableName] = $columns;
        }

        $simple_struct = [];

        foreach ($db_structure as $tableName => $tableColumns) {
            // array_push($simple_struct, $tableName);
            $tableName = [$tableName => []];

            //convert array of string into [['string'] => [...], ...]
            foreach ($tableColumns as $column) {
                $tableName[] = $column['COLUMN_NAME'] . " / DATA_TYPE:" . $column['DATA_TYPE'];
            }
            array_push($simple_struct, $tableName);
        }

        return $simple_struct;
    }

    public function get_all_data()
    {
        $tables = $this->table_names();

        foreach ($tables as $tableName) {
            $query = "SELECT * FROM [$tableName]";
            $stmt = $this->pdo->query($query);
            // $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $rows = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                var_dump($row);
            }
        }

    }

    public function all_table_names()
    {
        $query = "SELECT TABLE_SCHEMA, TABLE_NAME
              FROM INFORMATION_SCHEMA.TABLES
              WHERE TABLE_TYPE='BASE TABLE'";

        $stmt = $this->pdo->query($query);
        // $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $tables = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($tables, $row);
        }

        $result = [];
        foreach ($tables as $table) {
            $result[$table['TABLE_SCHEMA']][] = $table['TABLE_NAME'];
        }

        return $result;
    }

    public function foreign_keys()
    {
        $query = "
        SELECT  
            fk.name AS FK_name,
            sch1.name AS parent_schema,
            tp.name AS parent_table,
            cp.name AS parent_column,
            sch2.name AS referenced_schema,
            tr.name AS referenced_table,
            cr.name AS referenced_column
        FROM sys.foreign_keys AS fk
        INNER JOIN sys.foreign_key_columns AS fkc 
            ON fk.object_id = fkc.constraint_object_id
        INNER JOIN sys.tables AS tp 
            ON fkc.parent_object_id = tp.object_id
        INNER JOIN sys.schemas AS sch1 
            ON tp.schema_id = sch1.schema_id
        INNER JOIN sys.columns AS cp 
            ON fkc.parent_object_id = cp.object_id AND fkc.parent_column_id = cp.column_id
        INNER JOIN sys.tables AS tr 
            ON fkc.referenced_object_id = tr.object_id
        INNER JOIN sys.schemas AS sch2 
            ON tr.schema_id = sch2.schema_id
        INNER JOIN sys.columns AS cr 
            ON fkc.referenced_object_id = cr.object_id AND fkc.referenced_column_id = cr.column_id
        ORDER BY parent_schema, parent_table, FK_name
    ";

        $stmt = $this->pdo->query($query);
        $fks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        var_dump(($fks));
    }

    public function getAllTableNames()
    {
        $query = "SELECT TABLE_NAME
              FROM INFORMATION_SCHEMA.TABLES
              WHERE TABLE_TYPE = 'BASE TABLE' 
              AND TABLE_SCHEMA = 'dbo'";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$this->db]);
        $contents = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $db_allowed = path('db/tables_allowed');

        // Convert to clean array syntax
        $arrayString = "['" . implode("', '", $contents) . "']";

        $phpCode = <<<HTML_END
                    <?php
                    return $arrayString;
                    HTML_END;

        file_put_contents($db_allowed, $phpCode);

        return $contents;
    }

}