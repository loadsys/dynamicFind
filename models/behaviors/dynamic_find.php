<?php
/**
 * Dynamic Find Behavior
 *
 * Adds more convenience methods to models. Models that actsAs DynamicFind will
 * have access to find[field]by[field] methods.
 */
class DynamicFindBehavior extends ModelBehavior {
	/**
	 * Handles the mapping of find[field]by[field] methods to the $this->_find(method)
	 *
	 * @var array
	 * @access public
	 */
	var $mapMethods = array(
		'/^(find){1}[^(all|by)]{1}(.)+(by){1}(.)+$/' => '_find'
	);

	var $settings = array();

	var $_cache = array();

	var $_allowed = array(
		'whitelist' => false,
		'blacklist' => false,
		'log' => false
	);

	/**
	 * Initialization method for the behavior. Can accept options from models. The
	 * options can include the following keys: whitelist, blacklist and log
	 *
	 * @param object $model
	 * @param array $options
	 * @access public
	 * @return void
	 */
	function setup(&$model, $options = array()) {
		$a = $model->alias;
		extract($this->_allowed);
		extract(array_intersect_key($options, $this->_allowed));
		$this->settings[$a] = compact('whitelist', 'blacklist', 'log');
		$this->_cache[$a] = array();
	}

	/** 
	 * Handles all of the find[field]by[field] methods
	 *
	 * @param object $model
	 * @param string $method
	 * @param mixed $condition
	 */
	function _find(&$model, $method, $cond = null) {
		$a = $model->alias;
		$s = isset($this->settings[$a]) ? $this->settings[$a] : $this->_allowed;
		$fields = $ret = array();
		$virtual = $_field = $_cond = false;
		if ($this->_check($method, $s)) {
			$ret = $this->_cache($method, $a, $cond);
			if (is_array($ret) && empty($ret)) {
				$tmp = array_merge(array_keys($model->schema()), array_keys($model->virtualFields));
				foreach ($tmp as $field) {
					$fields[strtolower(str_replace('_', '', $field))] = $field;
				}
				$search = substr($method, (strpos($method, 'find') + 4), (strpos($method, 'by') - 4));
				if (array_key_exists($search, $fields)) {
					$_field = $fields[$search];
				}
				$search = substr($method, strpos($method, 'by') + 2);
				if (array_key_exists($search, $fields)) {
					$_cond = $fields[$search];
				}
				if (
					$_field &&
					$_cond &&
					$model->hasField($_field, true) &&
					$model->hasField($_cond, true)
				) {
					$result = $model->find('first', array(
						'conditions' => array(
							$model->alias.'.'.$_cond => $cond
						),
						'recursive' => -1
					));
					if (array_key_exists($_field, $result[$model->alias])) {
						$ret = $result[$model->alias][$_field]
					}
				}
			}
			if (!is_array($ret)) {
				$ret = array(
					$model->alias => array(
						$_field => 
					)
				);
			}
		}
		return $ret;
	}

	/**
	 * Takes the method being called and the settings for the model it was called on
	 * and determines based on the whitelist and blacklist whether or not to continue.
	 *
	 * @param string $method
	 * @param array $settings
	 * @access public
	 * @return bool
	 */
	function _check($method, $settings) {
	
	}

	/**
	 * Sets and retrieves data from the _cache property based on the method called, 
	 * the model alias, and the condition. If the 4th param is set, then data is 
	 * saved into the array.
	 *
	 * @param string $method
	 * @param string $alias
	 * @param mixed $condition
	 * @param mixed $result
	 * @access public
	 * @return mixed
	 */
	function _cache($method, $alias, $condition, $result = null) {
		$ret = array();
		if (is_array($condition)) {
			$condition = serialize($condition);
		}
		if ($result === null) {
			if (isset($this->_cache[$alias][$method][$condition])) {
				$ret = $this->_cache[$alias][$method][$condition];
			}
		} else {
			$existing = $this->_cache[$alias];
			$new = array($method => array($condition => $result));
			$this->_cache[$alias] = Set::merge($new, $existing);
		}
		return $ret;
	}
}

?>
