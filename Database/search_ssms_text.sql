DECLARE @SearchStr NVARCHAR(100);
SET @SearchStr = 'Население';

DECLARE @SQL NVARCHAR(MAX);
SET @SQL = '';

DECLARE @schema NVARCHAR(128), @table NVARCHAR(128), @column NVARCHAR(128);

DECLARE cur CURSOR FOR
SELECT 
    TABLE_SCHEMA, 
    TABLE_NAME, 
    COLUMN_NAME
FROM INFORMATION_SCHEMA.COLUMNS
WHERE DATA_TYPE IN (
    'char', 'nchar', 'varchar', 'nvarchar', 'text', 'ntext'
)


OPEN cur;
FETCH NEXT FROM cur INTO @schema, @table, @column;

WHILE @@FETCH_STATUS = 0
BEGIN
    SET @SQL = @SQL + 
    'IF EXISTS (SELECT 1 FROM [' + @schema + '].[' + @table + '] WHERE [' + @column + '] LIKE ''%' + @SearchStr + '%'') 
     PRINT ''Found in: ' + @schema + '.' + @table + '.' + @column + ''';' + CHAR(13);

    FETCH NEXT FROM cur INTO @schema, @table, @column;
END

CLOSE cur;
DEALLOCATE cur;

EXEC sp_executesql @SQL;
