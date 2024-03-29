<?php

/**
 * This file is part of the Dibi, smart database abstraction layer (https://dibiphp.com)
 * Copyright (c) 2005 David Grudl (https://davidgrudl.com)
 */

use Dibi\Connection;
use Dibi\Type;


/**
 * Static container class for Dibi connections.
 */
abstract class dibi
{
	use Dibi\Strict;

	const
		AFFECTED_ROWS = 'a',
		IDENTIFIER = 'n';

	/** version */
	const
		VERSION = '3.2.3',
		REVISION = 'released on 2018-09-17';

	/** sorting order */
	const
		ASC = 'ASC',
		DESC = 'DESC';

	/** @deprecated */
	const
		TEXT = Type::TEXT,
		BINARY = Type::BINARY,
		BOOL = Type::BOOL,
		INTEGER = Type::INTEGER,
		FLOAT = Type::FLOAT,
		DATE = Type::DATE,
		DATETIME = Type::DATETIME,
		TIME = Type::TIME,
		FIELD_TEXT = Type::TEXT,
		FIELD_BINARY = Type::BINARY,
		FIELD_BOOL = Type::BOOL,
		FIELD_INTEGER = Type::INTEGER,
		FIELD_FLOAT = Type::FLOAT,
		FIELD_DATE = Type::DATE,
		FIELD_DATETIME = Type::DATETIME,
		FIELD_TIME = Type::TIME;

	/** @var string|null  Last SQL command @see dibi::query() */
	public static $sql;

	/** @var float|null  Elapsed time for last query */
	public static $elapsedTime;

	/** @var float  Elapsed time for all queries */
	public static $totalTime;

	/** @var int  Number or queries */
	public static $numOfQueries = 0;

	/** @var string  Default dibi driver */
	public static $defaultDriver = 'mysqli';

	/** @var Dibi\Connection[]  Connection registry storage for Dibi\Connection objects */
	private static $registry = [];


	public static abstract function getConnection():Connection;

	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new LogicException('Cannot instantiate static class ' . get_class($this));
	}


	/********************* connections handling ****************d*g**/


	/**
	 * Creates a new Connection object and connects it to specified database.
	 * @param  array|string   connection parameters
	 * @param  string  connection name
	 * @return Dibi\Connection
	 * @throws Dibi\Exception
	 */
	public static function connect($config = [], $name = '0')
	{
		return new Dibi\Connection($config, $name);
	}


	/**
	 * Disconnects from database (doesn't destroy Connection object).
	 * @return void
	 */
	public static function disconnect()
	{
		static::getConnection()->disconnect();
	}


	/**
	 * Returns true when connection was established.
	 * @return bool
	 */
	public static function isConnected()
	{
		return (static::getConnection() !== null) && static::getConnection()->isConnected();
	}


	/********************* monostate for active connection ****************d*g**/


	/**
	 * Generates and executes SQL query - Monostate for Dibi\Connection::query().
	 * @param  array|mixed      one or more arguments
	 * @return Dibi\Result|int   result set or number of affected rows
	 */
	public static function query($args)
	{
		$args = func_get_args();
		return static::getConnection()->query($args);
	}


	/**
	 * Executes the SQL query - Monostate for Dibi\Connection::nativeQuery().
	 * @param  string           SQL statement.
	 * @return Dibi\Result|int   result set or number of affected rows
	 */
	public static function nativeQuery($sql)
	{
		return static::getConnection()->nativeQuery($sql);
	}


	/**
	 * Generates and prints SQL query - Monostate for Dibi\Connection::test().
	 * @param  array|mixed  one or more arguments
	 * @return bool
	 */
	public static function test($args)
	{
		$args = func_get_args();
		return static::getConnection()->test($args);
	}


	/**
	 * Generates and returns SQL query as DataSource - Monostate for Dibi\Connection::test().
	 * @param  array|mixed      one or more arguments
	 * @return Dibi\DataSource
	 */
	public static function dataSource($args)
	{
		$args = func_get_args();
		return static::getConnection()->dataSource($args);
	}


	/**
	 * Executes SQL query and fetch result - Monostate for Dibi\Connection::query() & fetch().
	 * @param  array|mixed    one or more arguments
	 * @return array
	 */
	public static function fetch($args)
	{
		$args = func_get_args();
		return static::getConnection()->query($args)->fetch();
	}


	/**
	 * Executes SQL query and fetch results - Monostate for Dibi\Connection::query() & fetchAll().
	 * @param  array|mixed    one or more arguments
	 * @return array[]
	 */
	public static function fetchAll($args)
	{
		$args = func_get_args();
		return static::getConnection()->query($args)->fetchAll();
	}


	/**
	 * Executes SQL query and fetch first column - Monostate for Dibi\Connection::query() & fetchSingle().
	 * @param  array|mixed    one or more arguments
	 * @return mixed
	 * @throws Dibi\Exception
	 */
	public static function fetchSingle($args)
	{
		$args = func_get_args();
		return static::getConnection()->query($args)->fetchSingle();
	}


	/**
	 * Executes SQL query and fetch pairs - Monostate for Dibi\Connection::query() & fetchPairs().
	 * @param  array|mixed    one or more arguments
	 * @return array
	 * @throws Dibi\Exception
	 */
	public static function fetchPairs($args)
	{
		$args = func_get_args();
		return static::getConnection()->query($args)->fetchPairs();
	}


	/**
	 * Gets the number of affected rows.
	 * Monostate for Dibi\Connection::getAffectedRows()
	 * @return int  number of rows
	 */
	public static function count($args)
	{
		$args = func_get_args();
		$args[0] = "SELECT COUNT(*) AS count FROM ".$args[0];
		return static::getConnection()->query($args)->fetchSingle();
	}


	/**
	 * Gets the number of affected rows.
	 * Monostate for Dibi\Connection::getAffectedRows()
	 * @return int  number of rows
	 * @throws Dibi\Exception
	 */
	public static function getAffectedRows()
	{
		return static::getConnection()->getAffectedRows();
	}


	/**
	 * @deprecated
	 */
	public static function affectedRows()
	{
		trigger_error(__METHOD__ . '() is deprecated, use getAffectedRows()', E_USER_DEPRECATED);
		return static::getConnection()->getAffectedRows();
	}


	/**
	 * Retrieves the ID generated for an AUTO_INCREMENT column by the previous INSERT query.
	 * Monostate for Dibi\Connection::getInsertId()
	 * @param  string     optional sequence name
	 * @return int
	 */
	public static function getInsertId($sequence = null)
	{
		return static::getConnection()->getInsertId($sequence);
	}

	/** @deprecated */
	public static function lastId($sequence = null)
	{
		return self::getInsertId($sequence);
	}


	/**
	 * @deprecated
	 */
	public static function insertId($sequence = null)
	{
		trigger_error(__METHOD__ . '() is deprecated, use getInsertId()', E_USER_DEPRECATED);
		return static::getConnection()->getInsertId($sequence);
	}


	/**
	 * Begins a transaction - Monostate for Dibi\Connection::begin().
	 * @param  string  optional savepoint name
	 * @return void
	 * @throws Dibi\Exception
	 */
	public static function begin($savepoint = null)
	{
		static::getConnection()->begin($savepoint);
	}


	/**
	 * Commits statements in a transaction - Monostate for Dibi\Connection::commit($savepoint = null).
	 * @param  string  optional savepoint name
	 * @return void
	 * @throws Dibi\Exception
	 */
	public static function commit($savepoint = null)
	{
		static::getConnection()->commit($savepoint);
	}


	/**
	 * Rollback changes in a transaction - Monostate for Dibi\Connection::rollback().
	 * @param  string  optional savepoint name
	 * @return void
	 * @throws Dibi\Exception
	 */
	public static function rollback($savepoint = null)
	{
		static::getConnection()->rollback($savepoint);
	}


	/**
	 * Gets a information about the current database - Monostate for Dibi\Connection::getDatabaseInfo().
	 * @return Dibi\Reflection\Database
	 */
	public static function getDatabaseInfo()
	{
		return static::getConnection()->getDatabaseInfo();
	}


	/**
	 * Import SQL dump from file - extreme fast!
	 * @param  string  filename
	 * @return int  count of sql commands
	 */
	public static function loadFile($file)
	{
		return Dibi\Helpers::loadFromFile(static::getConnection(), $file);
	}


	/********************* fluent SQL builders ****************d*g**/


	/**
	 * @return Dibi\Fluent
	 */
	public static function command()
	{
		return static::getConnection()->command();
	}


	/**
	 * @param  mixed    column name
	 * @return Dibi\Fluent
	 */
	public static function select($args)
	{
		$args = func_get_args();
		return call_user_func_array([static::getConnection(), 'select'], $args);
	}


	/**
	 * @param  string   table
	 * @param  array
	 * @return Dibi\Fluent
	 */
	public static function update($table, $args)
	{
		return static::getConnection()->update($table, $args);
	}


	/**
	 * @param  string   table
	 * @param  array
	 * @return Dibi\Fluent
	 */
	public static function insert($table, $args)
	{
		return static::getConnection()->insert($table, $args);
	}


	/**
	 * @param  string   table
	 * @return Dibi\Fluent
	 */
	public static function delete($table)
	{
		return static::getConnection()->delete($table);
	}


	/********************* substitutions ****************d*g**/


	/**
	 * Returns substitution hashmap - Monostate for Dibi\Connection::getSubstitutes().
	 * @return Dibi\HashMap
	 */
	public static function getSubstitutes()
	{
		return static::getConnection()->getSubstitutes();
	}


	/********************* misc tools ****************d*g**/


	/**
	 * Prints out a syntax highlighted version of the SQL command or Result.
	 * @param  string|Result
	 * @param  bool  return output instead of printing it?
	 * @return string|null
	 */
	public static function dump($sql = null, $return = false)
	{
		return Dibi\Helpers::dump($sql, $return);
	}


	/**
	 * Strips microseconds part.
	 * @param  \DateTime|\DateTimeInterface
	 * @return \DateTime|\DateTimeInterface
	 */
	public static function stripMicroseconds($dt)
	{
		$class = get_class($dt);
		return new $class($dt->format('Y-m-d H:i:s'), $dt->getTimezone());
	}
}
