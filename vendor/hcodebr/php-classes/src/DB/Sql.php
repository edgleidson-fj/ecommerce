<?php 

namespace Hcode\DB;

class Sql {

	const HOSTNAME = "127.0.0.1";
	const USERNAME = "root";
	const PASSWORD = "5017";
	const DBNAME = "db_ecommerce";

	private $conn;

	public function __construct(){

		$this->conn = new \PDO("mysql:dbname=".Sql::DBNAME.";host=".Sql::HOSTNAME, Sql::USERNAME, Sql::PASSWORD);
	}//Fim  __construct().


	private function setParams($statement, $parameters = array()){

		foreach ($parameters as $key => $value) {			
			$this->bindParam($statement, $key, $value);
		}
	}//Fim setParams().


	private function bindParam($statement, $key, $value){
		$statement->bindParam($key, $value);
	}//Fim bindParam.


	public function query($rawQuery, $params = array()){
		$stmt = $this->conn->prepare($rawQuery);

		$this->setParams($stmt, $params);

		$stmt->execute();
	}//Fim query().


	public function select($rawQuery, $params = array()):array {
		$stmt = $this->conn->prepare($rawQuery);

		$this->setParams($stmt, $params);

		$stmt->execute();

		return $stmt->fetchAll(\PDO::FETCH_ASSOC);
	}//Fim select().

}
 ?>