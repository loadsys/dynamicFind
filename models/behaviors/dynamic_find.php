<?php
/**
 * Dynamic Find Behavior
 *
 * Adds more convenience methods to models. Models that actsAs DynamicFind will
 * have access to find[field]by[field] methods.
 */
class DynamicFindBehavior extends ModelBehavior {
/**
 * Handles the mapping of find[field]By[field] and findAll[field]By[field] 
 * methods to the $this->_find(method)
 *
 * @var array
 * @access public
 */
	var $mapMethods = array(
		'/^(find){1}(all|by)?(.+)(by){1}(.+)$/' => '_find',
	);

/**
 * settings
 * 
 * @var array
 * @access public
 */
	var $settings = array();
		
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
		$this->settings[$a] = $options;
	}

/** 
 * Handles all of the find[field]by[field] methods
 *
 * @param object $model
 * @param string $method
 * @param mixed $condition
 */
	function _find(&$model, $method, $cond = null) {
		preg_match('/^(find){1}(all|by)?(.+)(by){1}(.+)$/', $method, $matches);
		$type = 'all';
		$retrieve_field = $matches[3];
		$search_field = $matches[5];
		if (is_array($cond)) {
			if ($matches[2] !== 'all') {
				$type = $cond[1];
			} else {
				$cond[1];
			}
			$cond = $cond[0];
		}
		$a = $model->alias;
		$s = isset($this->settings[$a]) ? $this->settings[$a] : $this->_allowed;
		$schema = array();
		$tmp = array_merge(array_keys($model->schema()), array_keys($model->virtualFields));
		foreach ($tmp as $field) {
			$schema[strtolower(str_replace('_', '', $field))] = $field;
		}
		if (
			$model->hasField($schema[$search_field], true) &&
			$model->hasField($schema[$retrieve_field], true)
		) {
			$options = array(
				'conditions' => array(
					$model->alias.'.'.$schema[$search_field] => $cond
				),
				'recursive' => -1
			);
			if ($type == 'all') {
				$options['fields'] = array($model->alias.'.'.$schema[$retrieve_field]);
			}
			$ret = $model->find($type, $options);
		}
		return $ret;
	}

}
