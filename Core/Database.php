<?php
namespace Core;

use dibi;
use Dibi\Bridges\Tracy\Panel;
use Dibi\Connection;
use Tracy\Debugger;

class Database extends dibi {

	private static $connection;

	public static function getConnection():Connection {
		if(!self::$connection){
			self::$connection = self::connect([
				'driver' => 'mysqli',
				'host' => Config::get("database")['host'],
				'username' => Config::get("database")['user'],
				'password' => Config::get("database")['password'],
				'database' => Config::get("database")['database']
			]);
			if(Debugger::isEnabled()){
				$panel = new Panel(false);
				$panel->register(self::$connection,"Database");
			}
		}
		return self::$connection;
	}
}

spl_autoload_register(function ($class) {
	static $map = [
		'dibi' => 'dibi.php',
		'Dibi\Bridges\Nette\DibiExtension22' => 'Bridges/Nette/DibiExtension22.php',
		'Dibi\Bridges\Tracy\Panel' => 'Bridges/Tracy/Panel.php',
		'Dibi\Connection' => 'Connection.php',
		'Dibi\ConstraintViolationException' => 'exceptions.php',
		'Dibi\DataSource' => 'DataSource.php',
		'Dibi\DateTime' => 'DateTime.php',
		'Dibi\Driver' => 'interfaces.php',
		'Dibi\DriverException' => 'exceptions.php',
		'Dibi\Drivers\FirebirdDriver' => 'Drivers/FirebirdDriver.php',
		'Dibi\Drivers\MsSqlDriver' => 'Drivers/MsSqlDriver.php',
		'Dibi\Drivers\MsSqlReflector' => 'Drivers/MsSqlReflector.php',
		'Dibi\Drivers\MySqlDriver' => 'Drivers/MySqlDriver.php',
		'Dibi\Drivers\MySqlReflector' => 'Drivers/MySqlReflector.php',
		'Dibi\Drivers\MySqliDriver' => 'Drivers/MySqliDriver.php',
		'Dibi\Drivers\OdbcDriver' => 'Drivers/OdbcDriver.php',
		'Dibi\Drivers\OracleDriver' => 'Drivers/OracleDriver.php',
		'Dibi\Drivers\PdoDriver' => 'Drivers/PdoDriver.php',
		'Dibi\Drivers\PostgreDriver' => 'Drivers/PostgreDriver.php',
		'Dibi\Drivers\Sqlite3Driver' => 'Drivers/Sqlite3Driver.php',
		'Dibi\Drivers\SqliteReflector' => 'Drivers/SqliteReflector.php',
		'Dibi\Drivers\SqlsrvDriver' => 'Drivers/SqlsrvDriver.php',
		'Dibi\Drivers\SqlsrvReflector' => 'Drivers/SqlsrvReflector.php',
		'Dibi\Event' => 'Event.php',
		'Dibi\Exception' => 'exceptions.php',
		'Dibi\Expression' => 'Expression.php',
		'Dibi\Fluent' => 'Fluent.php',
		'Dibi\ForeignKeyConstraintViolationException' => 'exceptions.php',
		'Dibi\HashMap' => 'HashMap.php',
		'Dibi\HashMapBase' => 'HashMap.php',
		'Dibi\Helpers' => 'Helpers.php',
		'Dibi\IDataSource' => 'interfaces.php',
		'Dibi\Literal' => 'Literal.php',
		'Dibi\Loggers\FileLogger' => 'Loggers/FileLogger.php',
		'Dibi\Loggers\FirePhpLogger' => 'Loggers/FirePhpLogger.php',
		'Dibi\NotImplementedException' => 'exceptions.php',
		'Dibi\NotNullConstraintViolationException' => 'exceptions.php',
		'Dibi\NotSupportedException' => 'exceptions.php',
		'Dibi\PcreException' => 'exceptions.php',
		'Dibi\ProcedureException' => 'exceptions.php',
		'Dibi\Reflection\Column' => 'Reflection/Column.php',
		'Dibi\Reflection\Database' => 'Reflection/Database.php',
		'Dibi\Reflection\ForeignKey' => 'Reflection/ForeignKey.php',
		'Dibi\Reflection\Index' => 'Reflection/Index.php',
		'Dibi\Reflection\Result' => 'Reflection/Result.php',
		'Dibi\Reflection\Table' => 'Reflection/Table.php',
		'Dibi\Reflector' => 'interfaces.php',
		'Dibi\Result' => 'Result.php',
		'Dibi\ResultDriver' => 'interfaces.php',
		'Dibi\ResultIterator' => 'ResultIterator.php',
		'Dibi\Row' => 'Row.php',
		'Dibi\Strict' => 'Strict.php',
		'Dibi\Translator' => 'Translator.php',
		'Dibi\Type' => 'Type.php',
		'Dibi\UniqueConstraintViolationException' => 'exceptions.php',
	];
	if (isset($map[$class])) {
		require_once __DIR__.'/Dibi/'.$map[$class];
	}
});