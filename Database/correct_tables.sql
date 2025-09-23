SELECT *
  FROM [munst1165].[dbo].[s_prizn]
  DECLARE @sql NVARCHAR(MAX);
SET @sql = N'';

-- build list using UNION ALL instead of VALUES
SELECT @sql = @sql +
    'IF EXISTS (SELECT 1 FROM [dbo].[fd_' + CAST(namefd AS VARCHAR(20)) + '] WHERE god = 2019 AND oktmo = 65608400)
         PRINT ''dbo.fd_' + CAST(namefd AS VARCHAR(20)) + ''';' + CHAR(10)
FROM (
    SELECT 8112013 AS namefd UNION ALL
    SELECT 8122003 UNION ALL
    -- ...
    SELECT 8112001
) AS t;

-- execute dynamic checks
EXEC sp_executesql @sql;
