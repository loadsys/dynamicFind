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
		$fields = array();
		$virtual = $ret = $_field = $_cond = false;
		if ($this->_check($method, $s)) {
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
				$results = $model->find('all', array(
					'conditions' => array(
						$model->alias.'.'.$_cond => $cond
					),
					'recursive' => -1
				));
				if (!empty($results)) {
					$_values = Set::extract('/'.$model->alias.'/'.$_field, $results);
					$_keys = Set::extract('/'.$model->alias.'/'.$model->primaryKey, $results);
					if (count($_values) > 1) {
						$ret = $_values[0];
					} else {
						$ret = array_combine($_keys, $_values);
					}
				}
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

}

?>
