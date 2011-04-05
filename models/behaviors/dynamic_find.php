<?php
/**
 *
 */
class DynamicFindBehavior extends ModelBehavior {
	/**
	 *
	 */
	public $mapMethods = array(
		'/^(find){1}[^(all|by)]{1}(.)+(by){1}(.)+$/' => '_find'
	);

	/** 
	 *
	 *
	 */
	public function _find(&$model, $method, $cond = null) {
		$fields = $ret = array();
		$virtual = $_field = $_cond = false;
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
				$ret = array(
					$model->alias => array(
						$_field => $result[$model->alias][$_field]
					)
				);
			}
		}
		return $ret;
	}
}

?>
