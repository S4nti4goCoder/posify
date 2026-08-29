<?php 

require_once "connection.php";
require_once "get.model.php";

class DeleteModel{

	/*=============================================
	DELETE to remove data from any table
	=============================================*/

	static public function deleteData($table, $id, $nameId){

		/*=============================================
		Check the id
		=============================================*/

		$response = GetModel::getDataFilter($table, $nameId, $nameId, $id, null,null,null,null);
		
		if(empty($response)){

			return null;

		}

		/*=============================================
		Delete the records
		=============================================*/

		$sql = "DELETE FROM $table WHERE $nameId = :$nameId";

		$link = Connection::connect();
		$stmt = $link->prepare($sql);

		$stmt->bindParam(":".$nameId, $id, PDO::PARAM_STR);

		try {

			$stmt->execute();

			return array("comment" => "The process was successful");

		} catch (PDOException $e) {

			/*=============================================
			1451 means another table still points at this row. PHP 8 throws
			instead of returning false, so the old else branch was dead
			=============================================*/

			if ((int) $e->errorInfo[1] === 1451) {

				/*=============================================
				MySQL names the child table in the message, and that is the
				only place it appears: (`db`.`sales`, CONSTRAINT `fk_...`
				=============================================*/

				$child = "";

				if (preg_match('/`[^`]+`\.`([a-z_]+)`,\s*CONSTRAINT/i', $e->getMessage(), $hit)) {

					$child = $hit[1];
				}

				return array("error" => "in_use", "child" => $child);
			}

			throw $e;
		}

	}

}