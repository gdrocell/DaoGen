<?php
	require_once('PDOConnect.php');
	require_once('SqlTable.php');
	
	/**
	 * @author Gary
	 * @date 08/13/14
	 * Time 10:18 pm
	 */
	class DBInfo {
		var $tables;
		var $dbConn;
		var $dbName;
		
		function __construct($dbName) {
			$this->dbName = $dbName;
			$this->tables = array();
		}
		
		function fromConnection($conn) {
			$this->dbConn = $conn;
		}
		
		function analyze() {
			$dbh = $this->dbConn->getConnection();
			
			$q = $dbh->prepare("SHOW tables");
			$q->execute();
			
			$tableNameAttr = "Tables_in_" . $this->dbName;
			
			while(($rs = $q->fetch(PDO::FETCH_ASSOC))) {
				$table = $rs[$tableNameAttr];
				$sqlTable = new SqlTable($table);
				$sqlTable->dbhFromConnection($this->dbConn);
				
				$sqlTable->analyze();
				
				$this->tables[$table] = $sqlTable;
			}
			
		}
		
		function getDbName() {
			return $this->dbName;
		}
		
		function setDbName($dbName) {
			$this->dbName = $dbName;
		}
		
		function getTables() {
			return $this->tables;
		}
	}
?>