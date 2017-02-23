<?php
	require_once('Generate.php');
	
	/**
	 * @author Gary
	 * @date 08/14/14
	 * Time 5:04pm
	 */
	class PHPGenerate implements Generate {
		
		var $dbInfo;
		
		public function __construct($dbInfo) {		
			$this->dbInfo = $dbInfo;
		}
		
		public function generate() {
			$tables = $this->dbInfo->getTables();
	
			foreach($tables as $table) {
				echo "<h2>Generating DAO " . $table->getTableName() . "</h2><br />";
				self::generateDAO($table);
			}
		}
		
		protected function generateDAO($table) {
			
			$tablename = ucfirst($table->getTableName());
			$fh = fopen("./gen-dao/" . $tablename . "DAO.php", "w");
			
			fwrite($fh, "<?PHP\n");
			fwrite($fh, "\tclass ". $tablename . "DAO {\n");
			
			self::generateInstanceVars($fh);
			self::generateConstructor($fh);
			
			self::generateRetrieveByPkIfTableHasOne($fh, $table);
			self::generatePrivateFilterGenFunction($fh);
			self::generateRetrieveByAttributeMapInRange($fh, $table);
			self::generateRetrieveAllInRange($fh,$table);
			self::generateRetrieveAllFunction($fh, $table);
			self::generateRetrieveByAttributeMap($fh, $table);
			self::generateCreationByList($fh, $table);
			self::generateUpdateByAttributeMap($fh, $table);
			self::generateDeleteByAttributeMap($fh, $table);
			
			fwrite($fh, "\t}\n");
			fwrite($fh, "?>\n");
			
			fclose($fh);
		}
		
		private function generateInstanceVars($fh) {
			fwrite($fh, "\t\tvar \$dbh;\n");
			
		}
		
		private function generateConstructor($fh) {
			fwrite($fh, "\t\tfunction __construct(\$dbh) {\n");
			fwrite($fh, "\t\t\$this->dbh=\$dbh;\n");
			fwrite($fh,"\t\t}\n");
		}
		
		private function generateRetrieveByPkIfTableHasOne($fh, $table) {
			if($fh == null || $table->getPrimaryKey() == null) {
				return;
			}
			
			$tablename = $table->getTableName();
			$pkey = $table->getPrimaryKey();
			$tablenameCamel = ucfirst($table->getTableName());
			$pkeyCamel = ucfirst($table->getPrimaryKey());
			$pkeyAttr = $table->getAttributeMap();
			$pkeyAttr = $pkeyAttr[$pkey];

			fwrite($fh, "\t\tpublic function get" . $tablenameCamel . "By" . $pkeyCamel . "(\$pk) {\n");
			fwrite($fh, "\t\t\t\$dbh=\$this->dbh;\n");
			fwrite($fh, "\t\t\t\$q=\$dbh->prepare(\"SELECT * FROM $tablename WHERE $pkey= ?\");\n");

			$type = strtolower($pkeyAttr->getAttributeName());
			if(strpos($type, "char") >= 0 || strpos($type, "binary") >= 0 || strpos($type, "blob") >= 0 || strpos($type, "text") >= 0) {
				fwrite($fh, "\t\t\t\$q->execute(array(\"\$pk\"));\n");
			} 
			else if(strpos($type, "int") >= 0 || strpos($type, "decimal") >= 0 || strpos($type, "float") >= 0 || strpos($type, "double") >= 0 || strpos($type, "numeric") >= 0) {
				fwrite($fh, "\t\t\t\$q->execute(array(\$pk));\n");
			}
				
			fwrite($fh, "\t\t\t\$returnTuples=array();\n");
			fwrite($fh, "\t\t\twhile((\$rs=\$q->fetch(PDO::FETCH_OBJ))) {\n");
			fwrite($fh, "\t\t\t\tarray_push(\$returnTuples,\$rs);\n");
			fwrite($fh, "\t\t\t}\n");
			fwrite($fh, "\t\t\treturn \$returnTuples;\n");
			fwrite($fh, "\t\t}\n");
		}
		
		private function generateRetrieveByAttributeMap($fh, $table) {
			if($fh == null || $table == null) {
				return;
			}
			
			$tablename = $table->getTableName();
			$tablenameCamel = ucfirst($tablename);
			
			
			fwrite($fh, "\t\tpublic function get" . $tablenameCamel . "ByAttributeMap(\$fkMap) {\n");
			fwrite($fh, "\t\t\t\$dbh=\$this->dbh;\n");
			fwrite($fh, "\t\t\t\$qStr=\"SELECT * FROM $tablename WHERE \" . self::generateFilter(\$fkMap);\n");
			fwrite($fh, "\t\t\t\$q=\$dbh->prepare(\$qStr);\n");
			fwrite($fh, "\t\t\t\$q->execute(array_values(\$fkMap));\n");
				
			fwrite($fh, "\t\t\t\$returnTuples=array();\n");
			fwrite($fh, "\t\t\twhile((\$rs=\$q->fetch(PDO::FETCH_OBJ))) {\n");
			fwrite($fh, "\t\t\t\tarray_push(\$returnTuples,\$rs);\n");
			fwrite($fh, "\t\t\t}\n");
			fwrite($fh, "\t\t\treturn \$returnTuples;\n");
			fwrite($fh, "\t\t}\n");
		}
		
		private function generateCreationByList($fh, $table) {
			if($fh == null || $table == null) {
				return;
			}
			
			$tablename = $table->getTableName();
			$attrMap = $table->getAttributeMap();
			$tableNameCamel = ucfirst($tablename);
			
			fwrite($fh, "\t\tpublic function insert" . $tableNameCamel . "(\$map) {\n");
			fwrite($fh, "\t\t\t\$dbh=\$this->dbh;\n");
			
			fwrite($fh, "\t\t\t\$genQuery = \"INSERT INTO $tablename %s VALUES %s\";\n");
			fwrite($fh, "\t\t\t\$colNames = \"(\";\n");
			fwrite($fh, "\t\t\t\$colVals = \"(\";\n");
			fwrite($fh, "\t\t\t\$valArr = array();\n");
				
			fwrite($fh, "\t\t\tforeach(\$map as \$k => \$v) {\n");
			fwrite($fh, "\t\t\t\t\$colNames .= \"\$k ,\";\n");
			fwrite($fh, "\t\t\t\t\$colVals .= \"? ,\";\n");
			fwrite($fh, "\t\t\t\tarray_push(\$valArr,\$v);\n");
			fwrite($fh, "\t\t\t}\n");
				
			fwrite($fh, "\t\t\t\$colNames = substr(\$colNames, 0, strlen(\$colNames)-1) . \")\";\n");
			fwrite($fh, "\t\t\t\$colVals = substr(\$colVals, 0, strlen(\$colVals)-1) . \")\";\n");
			fwrite($fh, "\t\t\t\$genQuery = sprintf(\$genQuery, \$colNames, \$colVals);\n");
			fwrite($fh, "\t\t\t\$q=\$dbh->prepare(\$genQuery);\n");
			fwrite($fh, "\t\t\t\$q->execute(\$valArr);\n");
			
			
			fwrite($fh, "\t\t}\n");
		}
		
		private function generateUpdateByAttributeMap($fh, $table) {
			if($fh == null || $table == null) {
				return; 
			}
			// reminder use str_replace to replace "AND" with ","
			$tablename = $table->getTableName();
			$attrMap = $table->getAttributeMap();
			$tableNameCamel = ucfirst($tablename);
			
			fwrite($fh, "\t\tpublic function update" . $tableNameCamel . "(\$updateMap, \$filterMap) {\n");
			fwrite($fh, "\t\t\t\$dbh=\$this->dbh;\n");
			fwrite($fh, "\t\t\t\$genQuery = \"UPDATE $tablename SET %s WHERE %s\";\n");
			fwrite($fh, "\t\t\t\$toUpdate = str_replace(\"AND\", \",\", self::generateFilter(\$updateMap));\n");
			fwrite($fh, "\t\t\t\$toFilter = self::generateFilter(\$filterMap);\n");
			fwrite($fh, "\t\t\t\$genQuery = sprintf(\$genQuery, \$toUpdate, \$toFilter);\n");
			fwrite($fh, "\t\t\t\$q=\$dbh->prepare(\$genQuery);\n");
			fwrite($fh, "\t\t\t\$q->execute(array_merge(array_values(\$updateMap),array_values(\$filterMap)));\n");
			
			fwrite($fh, "\t\t}\n");
		}
		
		private function generateDeleteByAttributeMap($fh, $table) {
			if($fh == null || $table == null) {
				return;
			}
			
			$tablename = $table->getTableName();
			$attrMap = $table->getAttributeMap();
			$tableNameCamel = ucfirst($tablename);
			
			fwrite($fh, "\t\tpublic function delete" . $tableNameCamel . "(\$deleteMap) {\n");
			fwrite($fh, "\t\t\t\$dbh=\$this->dbh;\n");
			fwrite($fh, "\t\t\t\$genQuery = \"DELETE FROM $tablename WHERE \" . self::generateFilter(\$deleteMap);\n");
			fwrite($fh, "\t\t\t\$q=\$dbh->prepare(\$genQuery);\n");
			fwrite($fh, "\t\t\t\$q->execute(array_values(\$deleteMap));\n");
			
			fwrite($fh, "\t\t}\n");
		}
		
		/**
		 * generates a sql filter 
		 * @param unknown $map - assumes key is table attribute 
		 */
		private function generatePrivateFilterGenFunction($fh) {
			fwrite($fh, "\t\tprotected function generateFilter(\$map) {\n");
			fwrite($fh, "\t\t\t\$filterStr = \"\";\n");
			fwrite($fh, "\t\t\tforeach(\$map as \$k => \$v) {\n");
			fwrite($fh,	"\t\t\t\t\$filterStr .= \" \$k= ? AND\";\n");
			fwrite($fh, "\t\t\t}\n");
			fwrite($fh, "\t\t\t\$filterStr = substr(\$filterStr,0,strlen(\$filterStr)-4);");
			fwrite($fh, "\t\t\treturn \$filterStr;\n");
			fwrite($fh, "\t\t}\n");
		}
		
		
		private function generateRetrieveAllFunction($fh, $table) {
			if($fh == null || $table == null) {
				return;
			}
			$tablename = $table->getTableName();
			$attrMap = $table->getAttributeMap();
			$tableNameCamel = ucfirst($tablename);
			
			fwrite($fh, "\t\tpublic function getAll" . $tableNameCamel . "s() {\n");
			fwrite($fh, "\t\t\t\$dbh=\$this->dbh;\n");
			fwrite($fh, "\t\t\t\$q = \$dbh->prepare('SELECT * FROM $tablename');\n");
			fwrite($fh, "\t\t\t\$q->execute();\n");
			fwrite($fh, "\t\t\t\$returnTuples=array();\n");
			fwrite($fh, "\t\t\twhile((\$rs=\$q->fetch(PDO::FETCH_OBJ))) {\n");
			fwrite($fh, "\t\t\t\tarray_push(\$returnTuples,\$rs);\n");
			fwrite($fh, "\t\t\t}\n");
			fwrite($fh, "\t\t\treturn \$returnTuples;\n");
			fwrite($fh, "\t\t}\n");
		}
		
		private function generateRetrieveAllInRange($fh, $table) {
			if($fh == null || $table == null) {
				return;
			}
			
			$tablename = $table->getTableName();
			$attrMap = $table->getAttributeMap();
			$tableNameCamel = ucfirst($tablename);
			
			fwrite($fh, "\t\tpublic function getAll" . $tableNameCamel . "sInRange(\$r1, \$r2) {\n");
			fwrite($fh, "\t\t\t\$dbh=\$this->dbh;\n");
			fwrite($fh, "\t\t\t\$q = \$dbh->prepare('SELECT * FROM $tablename LIMIT ? OFFSET ?');\n");
			fwrite($fh, "\t\t\t\$q->execute(array(\$r1, \$r2));\n");
			
			fwrite($fh, "\t\t\t\$returnTuples=array();\n");
			fwrite($fh, "\t\t\twhile((\$rs=\$q->fetch(PDO::FETCH_OBJ))) {\n");
			fwrite($fh, "\t\t\t\tarray_push(\$returnTuples,\$rs);\n");
			fwrite($fh, "\t\t\t}\n");
			fwrite($fh, "\t\t\treturn \$returnTuples;\n");
			fwrite($fh, "\t\t}\n");
		}
		
		private function generateRetrieveByAttributeMapInRange($fh, $table) {
			if($fh == null || $table == null) {
				return;
			}
			
			$tablename = $table->getTablename();
			$attrMap = $table->getAttributeMap();
			$tableNameCamel = ucfirst($tablename);
			
			fwrite($fh, "\t\tpublic function get" . $tableNameCamel . "ByAttributeMapInRange(\$fkMap, \$r1, \$r2) {\n");
			fwrite($fh, "\t\t\t\$dbh=\$this->dbh;\n");
			fwrite($fh, "\t\t\t\$qStr=\"SELECT * FROM $tablename WHERE \" . self::generateFilter(\$fkMap) . \" LIMIT ? OFFSET ?\";\n");
			fwrite($fh, "\t\t\t\$q=\$dbh->prepare(\$qStr);\n");
			fwrite($fh, "\t\t\t\$vals=array_values(\$fkMap);\n");
			fwrite($fh, "\t\t\tarray_push(\$vals, \$r1, \$r2);\n");
			fwrite($fh, "\t\t\t\$q->execute(\$vals);\n");
				
			fwrite($fh, "\t\t\t\$returnTuples=array();\n");
			fwrite($fh, "\t\t\twhile((\$rs=\$q->fetch(PDO::FETCH_OBJ))) {\n");
			fwrite($fh, "\t\t\t\tarray_push(\$returnTuples,\$rs);\n");
			fwrite($fh, "\t\t\t}\n");
			fwrite($fh, "\t\t\treturn \$returnTuples;\n");
			fwrite($fh, "\t\t}\n");
		}
		
	}
?>