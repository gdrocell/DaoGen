<?php
	class PDOConnect {
		var $dbh;
		function __construct($dbh) {
			$this->dbh = $dbh;
		}
		
		function getConnection() {
			return $this->dbh;
		}
		
		function setConnection($dbh) {
			$this->dbh = $dbh;
		}
	}
?>