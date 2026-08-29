<?php 

require_once "connection.php";

class PostModel{

	/*=============================================
	POST to create data in any table
	=============================================*/

	static public function postData($table, $data){

		$columns = "";
		$params = "";

		foreach ($data as $key => $value) {
			
			$columns .= $key.",";
			
			$params .= ":".$key.",";
			
		}

		$columns = substr($columns, 0, -1);
		$params = substr($params, 0, -1);


		$sql = "INSERT INTO $table ($columns) VALUES ($params)";

		$link = Connection::connect();
		$stmt = $link->prepare($sql);

		foreach ($data as $key => $value) {

			// an empty string reaches a date column as 0000-00-00
			if ($data[$key] === "" && SchemaGuard::isDateColumn($table, $key)) {

				$stmt->bindValue(":".$key, null, PDO::PARAM_NULL);

			} else {

				$stmt->bindParam(":".$key, $data[$key], PDO::PARAM_STR);
			}
		
		}

		if($stmt -> execute()){

			$response = array(

				"lastId" => $link->lastInsertId(),
				"comment" => "The process was successful"
			);

			return $response;
		
		}else{

			return $link->errorInfo();

		}


	}

}