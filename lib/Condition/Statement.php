<?php
/**
 * @author Johannes Müller <dev@straight-shoota.de>
 * @version 0.1
 */
interface Condition_Statement {
	/**
	 * Renders this condition as a SQL string.
	 * If it contains more than one sub statement, the whole string should be in parenthesis.
	 * @return string
	 * @abstract
	 */
	//abstract function getSql();
} 