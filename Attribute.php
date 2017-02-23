<?php
	/**
	 * @author Gary Drocella
	 * @date 08/13/14
	 * Time: 06:31 pm
	 */
	class Attribute {
		
		var $attributeName;
		var $attributeType;
		var $key;
		var $nullable;
		var $default;
		var $extra;
		
		function __constructor() {
			
		}
		
		function fromValues($attributeName, $attributeType, $key, $nullable, $default, $extra) {
			$this->attributeName = $attributeName;
			$this->attributeType = $attributeType;
			$this->key = $key;
			$this->nullable = $nullable;
			$this->default = $default;
			$this->extra = $extra;
		}
		
		public function getAttributeName() {
			return $this->attributeName;
		}
		
		public function getAttributeType() {
			return $this->attributeType;
		}
		
		public function getNullable() {
			return $this->nullable;
		}
		
		public function getDefault() {
			return $this->default;
		}
		
		public function getExtra() {
			return $this->extra;
		}
		
		public function getKey() {
			return $this->key;
		}
		
		public function setAttributeName($attributeName) {
			$this->attributeName = $attributeName;
		}
		
		public function setAttributeType($attributeType) {
			$this->attributeType = $attributeType;
		}
		
		public function setNullable($nullable) {
			$this->nullable = $nullable;
		}
		
		public function setDefault($default) {
			$this->default = $default;
		}
		
		public function setExtra($extra) {
			$this->extra = $extra;
		}
		
		public function setKey($key) {
			$this->key = $key;
		}
	}
?>