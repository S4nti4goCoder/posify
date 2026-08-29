<?php

require_once "connection.php";

/**
 * Read side of the generic query builder.
 *
 * SchemaGuard checks every identifier against the catalog before it is
 * interpolated. Values are bound. A method returns null when an identifier
 * does not exist, which the controllers turn into a 404.
 */
class GetModel
{

	/*=============================================
	GET without filter
	=============================================*/

	static public function getData($table, $select, $orderBy, $orderMode, $startAt, $endAt)
	{
		$tables = [$table];

		$safeSelect = SchemaGuard::safeSelect($tables, $select);
		$order      = SchemaGuard::safeOrderBy($tables, $orderBy, $orderMode);
		$limit      = SchemaGuard::safeLimit($startAt, $endAt);

		if ($safeSelect === null || $order === null || $limit === null) {

			return null;
		}

		return GetModel::run("SELECT $safeSelect FROM $table" . $order . $limit, []);
	}

	/*=============================================
	GET with filter
	=============================================*/

	static public function getDataFilter($table, $select, $linkTo, $equalTo, $orderBy, $orderMode, $startAt, $endAt)
	{
		$tables = [$table];
		$params = [];

		$safeSelect = SchemaGuard::safeSelect($tables, $select);
		$where      = GetModel::filterClause($tables, $linkTo, $equalTo, $params);
		$order      = SchemaGuard::safeOrderBy($tables, $orderBy, $orderMode);
		$limit      = SchemaGuard::safeLimit($startAt, $endAt);

		if ($safeSelect === null || $where === null || $order === null || $limit === null) {

			return null;
		}

		return GetModel::run("SELECT $safeSelect FROM $table" . $where . $order . $limit, $params);
	}

	/*=============================================
	GET across related tables
	=============================================*/

	static public function getRelData($rel, $type, $select, $orderBy, $orderMode, $startAt, $endAt)
	{
		$tables = array_map("trim", explode(",", (string) $rel));
		$types  = array_map("trim", explode(",", (string) $type));

		$joins      = SchemaGuard::safeJoins($tables, $types);
		$safeSelect = SchemaGuard::safeSelect($tables, $select);
		$order      = SchemaGuard::safeOrderBy($tables, $orderBy, $orderMode);
		$limit      = SchemaGuard::safeLimit($startAt, $endAt);

		if ($joins === null || $safeSelect === null || $order === null || $limit === null) {

			return null;
		}

		return GetModel::run("SELECT $safeSelect FROM $tables[0] $joins" . $order . $limit, []);
	}

	/*=============================================
	GET across related tables, with filter
	=============================================*/

	static public function getRelDataFilter($rel, $type, $select, $linkTo, $equalTo, $orderBy, $orderMode, $startAt, $endAt)
	{
		$tables = array_map("trim", explode(",", (string) $rel));
		$types  = array_map("trim", explode(",", (string) $type));
		$params = [];

		$joins      = SchemaGuard::safeJoins($tables, $types);
		$safeSelect = SchemaGuard::safeSelect($tables, $select);
		$where      = GetModel::filterClause($tables, $linkTo, $equalTo, $params);
		$order      = SchemaGuard::safeOrderBy($tables, $orderBy, $orderMode);
		$limit      = SchemaGuard::safeLimit($startAt, $endAt);

		if ($joins === null || $safeSelect === null || $where === null || $order === null || $limit === null) {

			return null;
		}

		return GetModel::run("SELECT $safeSelect FROM $tables[0] $joins" . $where . $order . $limit, $params);
	}

	/*=============================================
	GET for the search box
	=============================================*/

	static public function getDataSearch($table, $select, $linkTo, $search, $orderBy, $orderMode, $startAt, $endAt)
	{
		$tables = [$table];
		$params = [];

		$safeSelect = SchemaGuard::safeSelect($tables, $select);
		$where      = GetModel::searchClause($tables, $linkTo, $search, $params);
		$order      = SchemaGuard::safeOrderBy($tables, $orderBy, $orderMode);
		$limit      = SchemaGuard::safeLimit($startAt, $endAt);

		if ($safeSelect === null || $where === null || $order === null || $limit === null) {

			return null;
		}

		return GetModel::run("SELECT $safeSelect FROM $table" . $where . $order . $limit, $params);
	}

	/*=============================================
	GET for the search box, across related tables
	=============================================*/

	static public function getRelDataSearch($rel, $type, $select, $linkTo, $search, $orderBy, $orderMode, $startAt, $endAt)
	{
		$tables = array_map("trim", explode(",", (string) $rel));
		$types  = array_map("trim", explode(",", (string) $type));
		$params = [];

		$joins      = SchemaGuard::safeJoins($tables, $types);
		$safeSelect = SchemaGuard::safeSelect($tables, $select);
		$where      = GetModel::searchClause($tables, $linkTo, $search, $params);
		$order      = SchemaGuard::safeOrderBy($tables, $orderBy, $orderMode);
		$limit      = SchemaGuard::safeLimit($startAt, $endAt);

		if ($joins === null || $safeSelect === null || $where === null || $order === null || $limit === null) {

			return null;
		}

		return GetModel::run("SELECT $safeSelect FROM $tables[0] $joins" . $where . $order . $limit, $params);
	}

	/*=============================================
	GET by range
	=============================================*/

	static public function getDataRange($table, $select, $linkTo, $between1, $between2, $orderBy, $orderMode, $startAt, $endAt, $filterTo, $inTo)
	{
		$tables = [$table];
		$params = [];

		$safeSelect = SchemaGuard::safeSelect($tables, $select);
		$where      = GetModel::rangeClause($tables, $linkTo, $between1, $between2, $filterTo, $inTo, $params);
		$order      = SchemaGuard::safeOrderBy($tables, $orderBy, $orderMode);
		$limit      = SchemaGuard::safeLimit($startAt, $endAt);

		if ($safeSelect === null || $where === null || $order === null || $limit === null) {

			return null;
		}

		return GetModel::run("SELECT $safeSelect FROM $table" . $where . $order . $limit, $params);
	}

	/*=============================================
	GET by range, across related tables
	=============================================*/

	static public function getRelDataRange($rel, $type, $select, $linkTo, $between1, $between2, $orderBy, $orderMode, $startAt, $endAt, $filterTo, $inTo)
	{
		$tables = array_map("trim", explode(",", (string) $rel));
		$types  = array_map("trim", explode(",", (string) $type));
		$params = [];

		$joins      = SchemaGuard::safeJoins($tables, $types);
		$safeSelect = SchemaGuard::safeSelect($tables, $select);
		$where      = GetModel::rangeClause($tables, $linkTo, $between1, $between2, $filterTo, $inTo, $params);
		$order      = SchemaGuard::safeOrderBy($tables, $orderBy, $orderMode);
		$limit      = SchemaGuard::safeLimit($startAt, $endAt);

		if ($joins === null || $safeSelect === null || $where === null || $order === null || $limit === null) {

			return null;
		}

		return GetModel::run("SELECT $safeSelect FROM $tables[0] $joins" . $where . $order . $limit, $params);
	}

	/*=============================================
	Equality WHERE: column checked, value bound
	=============================================*/

	static private function filterClause(array $tables, $linkTo, $equalTo, array &$params): ?string
	{
		$columns = array_map("trim", explode(",", (string) $linkTo));
		$values  = array_map("trim", explode(",", (string) $equalTo));

		if (count($columns) !== count($values)) {

			return null;
		}

		$conditions = [];

		foreach ($columns as $index => $column) {

			if (!SchemaGuard::isColumn($tables, $column)) {

				return null;
			}

			$conditions[] = $column . " = :filter" . $index;

			$params[":filter" . $index] = $values[$index];
		}

		return " WHERE " . implode(" AND ", $conditions);
	}

	/*=============================================
	Search WHERE: LIKE on the first column, equality on the rest
	=============================================*/

	static private function searchClause(array $tables, $linkTo, $search, array &$params): ?string
	{
		$columns = array_map("trim", explode(",", (string) $linkTo));
		$values  = array_map("trim", explode(",", (string) $search));

		if (count($columns) !== count($values)) {

			return null;
		}

		$conditions = [];

		foreach ($columns as $index => $column) {

			if (!SchemaGuard::isColumn($tables, $column)) {

				return null;
			}

			if ($index === 0) {

				$conditions[] = $column . " LIKE :search0";

				$params[":search0"] = "%" . $values[0] . "%";

				continue;
			}

			$conditions[] = $column . " = :search" . $index;

			$params[":search" . $index] = $values[$index];
		}

		return " WHERE " . implode(" AND ", $conditions);
	}

	/*=============================================
	Range WHERE, with an optional IN filter
	=============================================*/

	static private function rangeClause(array $tables, $linkTo, $between1, $between2, $filterTo, $inTo, array &$params): ?string
	{
		if (!SchemaGuard::isColumn($tables, $linkTo)) {

			return null;
		}

		$sql = " WHERE " . trim((string) $linkTo) . " BETWEEN :between1 AND :between2";

		$params[":between1"] = $between1;
		$params[":between2"] = $between2;

		$hasFilter = $filterTo !== null
			&& trim((string) $filterTo) !== ""
			&& $inTo !== null
			&& trim((string) $inTo) !== "";

		if (!$hasFilter) {

			return $sql;
		}

		if (!SchemaGuard::isColumn($tables, $filterTo)) {

			return null;
		}

		$placeholders = [];

		foreach (array_map("trim", explode(",", (string) $inTo)) as $index => $value) {

			$placeholders[] = ":in" . $index;

			$params[":in" . $index] = $value;
		}

		return $sql . " AND " . trim((string) $filterTo) . " IN (" . implode(",", $placeholders) . ")";
	}

	/*=============================================
	Shared execution
	=============================================*/

	static private function run(string $sql, array $params)
	{
		$stmt = Connection::connect()->prepare($sql);

		foreach ($params as $name => $value) {

			$stmt->bindValue($name, $value, PDO::PARAM_STR);
		}

		try {

			$stmt->execute();
		} catch (PDOException $exception) {

			error_log("Query failed: " . $exception->getMessage() . " | SQL: " . $sql);

			return null;
		}

		return $stmt->fetchAll(PDO::FETCH_CLASS);
	}
}
