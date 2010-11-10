<?php
require_once 'Statement.php';
// more includes at the botttom!

/**
 * @author Johannes Müller <dev@straight-shoota.de>
 * @version 0.1
 * @abstract
 */
abstract class Condition
implements Condition_Statement {
	/**
	 * @var string junctor for a conjunction = AND
	 */
	const CONJUNCTION = "AND";
	/**
	 * @var string junctor for a disjunction = OR
	 */
	const DISJUNCTION = "OR";
	
	/**
	 * @var string binary operator =
	 */
	const EQUAL 			= "=";
	/**
	 * @var string binary operator >
	 */
	const GREATER			= ">";
	/**
	 * @var string binary operator <
	 */
	const LOWER				= "<";
	/**
	 * @var string binary operator >=
	 */
	const GREATER_OR_EQUAL	= ">=";
	/**
	 * @var string binary operator <=
	 */
	const LOWER_OR_EQUAL	= "<=";
	/**
	 * @var string binary operator !=
	 */
	const NOT_EQUAL			= "!=";
	/**
	 * @var string binary operator LIKE
	 */
	const LIKE				= "LIKE";
	/**
	 * @var string binary operator NOT LIKE
	 */
	const NOT_LIKE			= "NOT LIKE";
	
	/**
	 * @var string unary operator IS NULL
	 */
	const IS_NULL			= 'IS NULL';
	/**
	 * @var string unary operator IS NOT NULL
	 */
	const IS_NOT_NULL		= 'IS NOT NULL';
	/**
	 * @var string unary operator true
	 */
	const IS_TRUE			= " = true";
	/**
	 * @var string unary operator false
	 */
	const IS_FALSE			= " = false";
	
	
	/**
	 * Returns the SQL representation of this statement
	 * @see getSql()
	 */
	public function __toString() {
		return $this->getSql();
	}
	
	static $phraseSeparators	= array(';');
	static $paramIndicators		= array(':');
	static $valueSeparator		= ',';
	
	/**
	 * Parses a search string and returns a statement structure representing all conditions.
	 * <h3>Format of a search string</h3>
	 * <pre>param1:[!<>]value1a[,[!<>]value1b[,[!<>]value1c...]][;param2:[!<>]value2]...</pre>
	 * @param string $searchString
	 * @param array $cols
	 * @param array $fullTextCols
	 * @return Condition_Junction
	 * @static
	 */
	public static function parseSearchString($searchString, array $cols, array $fullTextCols = array()){
		if(! count($fullTextCols)) {
			foreach($cols as $name => $col) {
				if(isset($col['fullSearch']) && $col['fullSearch']){
					$fullTextCols[] = $name;
				}
			}
		}
		
		$conditions = new Condition_Junction(self::CONJUNCTION);
		//var_dump($cols);
		foreach(preg_split('/(' . implode('|', self::$phraseSeparators) . ')/', $searchString) as $phrase){
			$t = preg_split('/(' . implode('|', self::$paramIndicators) . ')/', $phrase, 2);
			//print '$t ' . $phrase; var_dump($t);
			$aliasCol = false;
			if(count($t) > 1) {
				list($param, $values) = $t;
				if(! isset($cols[$param])){
					// there is no database field by that name... maybe there is an alias on any field
					foreach($cols as $colName => $col){
						if(! isset($col['additional']) || ! is_array($col['additional'])) {
							continue;
						}
						foreach($col['additional'] as $alias => $condition){
							if($alias == $param){
								$aliasCol	= $col;
								$aliasCol['col']	= $colName;
								$aliasCol['name']	= $alias;
								$aliasCol['condition']	= $condition;
								break;
							}
						}
					}
					if(! $aliasCol) {
						continue;
					}
				}
			}else{
				$param	= $fullTextCols;
				if(! strpos($t[0], '%')){
					$values	= explode(' ', $t[0]);
					foreach($values as $i => $v){
						$values[$i] = '%' . $v . '%';
					}
				}else{
					$values	= $t[0];
				}
			}
			
			if(! is_array($values)){
				$values = explode(self::$valueSeparator, $values);
			}
			$operator = self::EQUAL;
			foreach($values as $i => $v){
				if(! strlen($v)){
					unset($values[$i]);
					continue;
				}
				if(strpos(',><-!', $v{0})){
					switch($v{0}){
						case '>':
							$operator = self::GREATER;
						break;
						case '<':
							$operator = self::LOWER;
						break;
						case '!':
						case '-':
							$operator = self::NOT_EQUAL;
					}
					$v = substr($v, 1);
					$values[$i] = $v;
				}
				
				if(is_numeric($v)){
					if($v + 0 === intval($v)){
						$values[$i] = intval($v);
					}else{
						$values[$i] = floatval($v);
					}
				}elseif(is_string($v)){
					if($operator == self::EQUAL){
						$operator = self::LIKE;
					}elseif($operator == self::NOT_EQUAL){
						$operator = self::NOT_LIKE;
					}
				}
			}
			if(count($values) == 1){
				$values = $values[0];
			}elseif(! count($values)){
				continue;
			}
			if(! $aliasCol){
				$condition = new Condition_Comparation($operator, $param, $values);
				//var_dump($condition);
			}else{
				$condition = $aliasCol['condition'];
				$condition->setValue($values);
				$condition->setOperator($operator);
			}
			$conditions->add($condition);
		}
		return $conditions;
	}
}
require_once 'Comparation.php';
require_once 'Junction.php';
require_once 'Field.php';
//print Condition::parseSearchString("age:>20;name:johannes,thomas,philipp;status:1;müller,meier,schmidt", array('firstname', 'lastname', 'town'))->getSql();
//print Condition::parseSearchString("age:>20;status:1;", array(), array('firstname', 'lastname', 'town'))->getSql();