DECLARE @tableName NVARCHAR(255);
DECLARE @sql NVARCHAR(MAX);
DECLARE @searchValue INT;

SET @searchValue = 8112013;

-- Temporary table to store results
CREATE TABLE #Results (
    TableName NVARCHAR(255),
    ExistsFlag BIT
);

-- Cursor over table names matching the pattern
DECLARE table_cursor CURSOR FOR
SELECT TABLE_SCHEMA + '.' + TABLE_NAME
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_NAME LIKE '%\_%\_fd' ESCAPE '\'
      AND TABLE_SCHEMA = 'dbo';

OPEN table_cursor;
FETCH NEXT FROM table_cursor INTO @tableName;

WHILE @@FETCH_STATUS = 0
BEGIN
    SET @sql = '
    IF EXISTS (SELECT 1 FROM ' + @tableName + ' WHERE namefd = ' + CAST(@searchValue AS NVARCHAR) + ')
        INSERT INTO #Results (TableName, ExistsFlag) VALUES (''' + @tableName + ''', 1)
    ELSE
        INSERT INTO #Results (TableName, ExistsFlag) VALUES (''' + @tableName + ''', 0)';
    
    EXEC sp_executesql @sql;
    
    FETCH NEXT FROM table_cursor INTO @tableName;
END

CLOSE table_cursor;
DEALLOCATE table_cursor;

-- Output results
SELECT * FROM #Results;

DROP TABLE #Results;
