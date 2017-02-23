
<?php
	require_once('PDOConnect.php');
	require_once('Attribute.php');
	
	/**
	 * Encapsulates Information about a MySql Table 
	 * @author Gary
	 * @date 08/12/14
	 * Time 10:13pm
	 */
	class SqlTable {
		var $dbh;
		var $attributeMap;
		var $primaryKey;
		var $foreignKeys;
		var $tableName;
		
		function __construct($tableName) {
			$this->tableName = $tableName;
			$this->attributeMap = array();
			$this->foreignKeys = array();
		}
		
		public function dbhFromConnection($conn) {
			$this->dbh = $conn->getConnection();
		}
		
		public function analyze() {
			if(empty($this->dbh) || empty($this->tableName)) {
				return;
			}
			$dbh = $this->dbh;
			$q = $dbh->prepare("DESCRIBE " . $this->tableName); //,  array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
			echo "Table Name: " . $this->tableName . "<br />";
			//$q->bindParam(1, $this->tableName);
			$q->execute();
			
			while(($rs = $q->fetch(PDO::FETCH_ASSOC))) {
				$attrName = $rs["Field"];
				$attrType = $rs["Type"];
				$key = $rs["Key"];
				$nullabel = $rs["Null"];
				$default = $rs["Default"];
				$extra = $rs["Extra"];
				
				$attr = new Attribute();
				$attr->fromValues($attrName, $attrType, $key, $nullabel, $default, $extra);
				
				$this->attributeMap[$attrName] = $attr;
				
				if($key == "PRI") {
					$this->primaryKey = $attrName;
				}
				else if($key == "MUL") {
					array_push($this->foreignKeys, $attrName);
				}
			}
			
			
		}
		
		public function getAttributeMap() {
			return $this->attributeMap;
		}
		
		public function getPrimaryKey() {
			return $this->primaryKey;
		}
		
		public function getForeignKeys() {
			return $this->foreignKeys;
		}
		
		public function getTableName() {
			return $this->tableName;
		}
	
	}
?>