<?php
	require_once('PHPGenerate.php');
	require_once('DBInfo.php');
	require_once('PDOConnect.php');
	
	/**
	 * @author Gary Drocella
	 * @date 08/15/14
	 * 
	 */
	class DAOGenerator {
		public static function main() {
			
			$dbh = new PDO('mysql:host=localhost;port=3306;dbname=dynamic_js_db', 'root', 'xxxx');
			$pdoConn = new PDOConnect($dbh);
			$dbInfo = new DBInfo("dynamic_js_db");
			$dbInfo->fromConnection($pdoConn);
			echo "<h2>Analyzing Database...</h2><br />";
			$dbInfo->analyze();
	
			echo "<h2>Generating DAO's...</h2><br />";
			$phpGen = new PHPGenerate($dbInfo);
			$phpGen->generate();
			
		}
	}
	
	DAOGenerator::main();
?>