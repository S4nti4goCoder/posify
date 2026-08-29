<?php

/**
 * Checks SQL identifiers against the database catalog.
 *
 * Table and column names cannot be bound, so the builder has to interpolate
 * them. They are only interpolated after this class proves they exist.
 *
 * null means the caller sent something invalid. An empty string means the
 * caller asked for nothing.
 */
final class SchemaGuard
{
    /** @var array<string,string[]> columns per table, one lookup per request */
    private static array $cache = [];

    /** @var array<string,array<string,string>> column => data type, same lookup */
    private static array $types = [];

    /** @return string[] empty when the table does not exist */
    public static function columnsOf(string $table): array
    {
        if (array_key_exists($table, self::$cache)) {

            return self::$cache[$table];
        }

        /*=============================================
        Shape check first, so junk never reaches the database
        =============================================*/

        if (preg_match('/^[A-Za-z0-9_]+$/', $table) !== 1) {

            self::$types[$table] = [];

            return self::$cache[$table] = [];
        }

        $stmt = Connection::connect()->prepare(
            "SELECT COLUMN_NAME AS item, DATA_TYPE AS kind
               FROM information_schema.columns
              WHERE table_schema = :schema
                AND table_name   = :table"
        );

        $stmt->execute([
            ":schema" => Connection::infoDatabase()["database"],
            ":table"  => $table
        ]);

        $columns = [];
        $types   = [];

        foreach ($stmt->fetchAll(PDO::FETCH_OBJ) as $row) {

            $columns[] = $row->item;
            $types[$row->item] = $row->kind;
        }

        self::$types[$table] = $types;

        return self::$cache[$table] = $columns;
    }

    public static function isDateColumn(string $table, string $column): bool
    {
        self::columnsOf($table);

        return in_array(
            self::$types[$table][$column] ?? "",
            ["date", "datetime", "timestamp", "time", "year"],
            true
        );
    }

    public static function tableExists(string $table): bool
    {
        return self::columnsOf($table) !== [];
    }

    /**
     * Columns reachable from every table in the query.
     *
     * @param  string[] $tables
     * @return string[] empty if any table is unknown
     */
    public static function columnsOfAll(array $tables): array
    {
        $columns = [];

        foreach ($tables as $table) {

            if (!self::tableExists((string) $table)) {

                return [];
            }

            $columns = array_merge($columns, self::columnsOf((string) $table));
        }

        return array_values(array_unique($columns));
    }

    /** @param string[] $tables */
    public static function isColumn(array $tables, $column): bool
    {
        if ($column === null || trim((string) $column) === "") {

            return false;
        }

        return in_array(trim((string) $column), self::columnsOfAll($tables), true);
    }

    /**
     * Checks a comma separated column list, "*" included.
     *
     * @param string[] $tables
     */
    public static function safeSelect(array $tables, $select): ?string
    {
        $select = ($select === null || trim((string) $select) === "") ? "*" : trim((string) $select);

        if (self::columnsOfAll($tables) === []) {

            return null;
        }

        if ($select === "*") {

            return "*";
        }

        $names = array_map("trim", explode(",", $select));
        $known = self::columnsOfAll($tables);

        foreach ($names as $name) {

            if (!in_array($name, $known, true)) {

                return null;
            }
        }

        return implode(",", $names);
    }

    /**
     * " ORDER BY column ASC", or "" when no ordering was asked for.
     *
     * @param string[] $tables
     */
    public static function safeOrderBy(array $tables, $orderBy, $orderMode): ?string
    {
        $requested = $orderBy !== null && trim((string) $orderBy) !== "";

        if (!$requested) {

            return "";
        }

        if (!self::isColumn($tables, $orderBy)) {

            return null;
        }

        $mode = strtoupper(trim((string) $orderMode));

        if ($mode !== "ASC" && $mode !== "DESC") {

            return null;
        }

        return " ORDER BY " . trim((string) $orderBy) . " " . $mode;
    }

    /** " LIMIT 0, 20" from integers only, or "" when no limit was asked for */
    public static function safeLimit($startAt, $endAt): ?string
    {
        if ($startAt === null && $endAt === null) {

            return "";
        }

        if (!is_numeric($startAt) || !is_numeric($endAt)) {

            return null;
        }

        return " LIMIT " . max(0, (int) $startAt) . ", " . max(0, (int) $endAt);
    }

    /**
     * Builds the INNER JOIN chain.
     *
     * Join columns come from the caller supplied "type" list, so each derived
     * name is checked before it reaches SQL.
     *
     * @param  string[] $relArray  tables, the first one drives the query
     * @param  string[] $typeArray suffix per table, same order
     */
    public static function safeJoins(array $relArray, array $typeArray): ?string
    {
        if (count($relArray) < 2 || count($typeArray) !== count($relArray)) {

            return null;
        }

        $baseTable   = $relArray[0];
        $baseColumns = self::columnsOf($baseTable);

        if ($baseColumns === []) {

            return null;
        }

        $joins = "";

        foreach ($relArray as $key => $table) {

            if (!self::tableExists($table)) {

                return null;
            }

            if ($key === 0) {

                continue;
            }

            $foreignKey = "id_" . $typeArray[$key] . "_" . $typeArray[0];
            $primaryKey = "id_" . $typeArray[$key];

            if (!in_array($foreignKey, $baseColumns, true)) {

                return null;
            }

            if (!in_array($primaryKey, self::columnsOf($table), true)) {

                return null;
            }

            $joins .= "INNER JOIN " . $table
                . " ON " . $baseTable . "." . $foreignKey
                . " = " . $table . "." . $primaryKey . " ";
        }

        return $joins;
    }
}
