<?php

/**
	* Implementation of the HashMap array Object
	* (C) Copyright 2012 Simonyi peng, China 
	* 
	* Licensed under the GNU Public License (GPL)  
	* 
	*/
 
class HashMap 
{
 
    protected $_values = array();
 
    /**
			* ���캯��
			* @param string $values  ��ʼ������
			*/
    public function __construct($values = array()) {
		  if (!empty($values)) {
		  	$this->_values = $values;
   		}
    }

    public function __get($name) {
		   return $this->get($name);
    }
 
    public function __set($name,$value) {
   			return $this->put($name,$value);
    }
 
    /**
			* ���Map����	
			*/
    public function clear() {
   			$this->_values = array();
    }
 
    /**
			* ���Map���Ƿ�ָ��key
			* @param string $key  ����
			* @return boolen
			*/
    public function containsKey($key) {
		   return array_key_exists($key, $this->_values);
    }
 
    /**
			* Map�Ƿ����ָ��value
			* @param string $value  ��ֵ
			* @return boolen
			*/
    public function containsValue($value) {
			  return in_array($value, $this->_values);
    }
 
 
    /**
			* ����Keyȡ��value
			* @param string $key  ����
			* @return mixed
			*/
    public function get($key) {
		   if ($this->containsKey($key)) {
				  return $this->_values[$key];
		   } else {
				  return null;
		   }
    }
 
    /**
			* �Ƿ�Ϊ��
			* @return boolen
			*/
    public function isEmpty() {
		   return empty($this->_values);
    }
 
    /**
			* ת��Ϊ����
			* @return array
			*/
    public function toArray() {
   			return $this->_values;
    }
 
    /**
			* ����key����
			* @return array
			*/
    public function keySet() {
   			return array_keys($this->_values);
    }
 
    /**
			* ����key-value�ԣ�����ֵ��ͬʱ����
			* ������ڵĻ�����֮ǰ��ֵ
			* @param string $key  ��ֵ
			* @param string $value  ֵ
			* @return mixed
			*/
    public function put($key, $value) {
		   $oldValue = $this->get($key);
		   $this->_values[$key] =&$value;
		   return $oldValue;
    }
 
 
    /**
			* ����һ��ʸ����Map
			* @param array $list  ����
			*/
    public function putAll($list) {
		   if (is_array($list)) {
				  foreach ($list as $key => $value) {
						 $this->put($key, $value);
		  		}
		   }
    }
 
    /**
			* �Ƴ�ָ���ļ�ֵ
			* �����Ƴ���ֵ
			* @param mixed $key ��ֵ
			* @return string
			*/
    public function remove($key) {
	   		$value = $this->get($key);
  	 		if (!is_null($value)) { unset($this->_values[$key]); }
   			return $value;
    }
 
    /**
			* ȡ��Map�ĳ���
			* @return integer
			*/
    public function size() {
		   return count($this->_values);
    }
 
    /**
			* ����ֵʸ��
			* @access public
			* @return array
			*/
    public function values() {
   			return array_values($this->_values);
    }
 
}

?>