<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Connection'
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database type. ie: mysql.  Currently supported:
				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Active Record class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|				 NOTE: For MySQL and MySQLi databases, this setting is only used
| 				 as a backup if your server is running PHP < 5.2.3 or MySQL < 5.0.7
|				 (and in table creation queries made with DB Forge).
| 				 There is an incompatibility in PHP with mysql_real_escape_string() which
| 				 can make your site vulnerable to SQL injection if you are using a
| 				 multi-byte character set and are running versions lower than these.
| 				 Sites using Latin-1 or UTF-8 database character set and collation are unaffected.
|	['swap_pre'] A default table prefix that should be swapped with the dbprefix
|	['autoinit'] Whether or not to automatically initialize the database.
|	['stricton'] TRUE/FALSE - forces 'Strict Mode' connections
|							- good for ensuring strict SQL while developing
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the 'default' group).
|
| The $active_record variables lets you determine whether or not to load
| the active record class
*/

/* set up the database name depending on branch code stored in wrightetmathon.ini*/
/* read the ini file to get the branch code : add host_name dde 04/2019 */
$database_found_flag		=	'N';
$inifile 					=	fopen("/var/www/html/wrightetmathon.ini", "r") or die("Unable to open wrightetmathon.ini file! You have a major problem. Contact your system administrator.");
while(!feof($inifile) AND $database_found_flag == 'N' ) 
{
	$_SESSION['hostname']	= "" ;
	$line					=	fgets($inifile);
	if (strpos($line, 'hostname') !== false) 
	{
		$hostname_array		=	array();
		$hostname_array		=	explode('=', $line);
		$hostname_array[1] = trim($hostname_array[1]);
        if ($hostname_array[1] <>  '127.0.0.1'  && $hostname_array[1] <> 'localhost') 
		{
            $_SESSION['hostname'] = $hostname_array[1];
        }
	}
	if (strpos($line, 'database') !== false) 
	{
		$database_array		=	array();
		$database_array		=	explode('=', $line);
		$database_found_flag	=	'Y';
	}
}

fclose($inifile);

if ($database_found_flag == 'N')
{
	die("Cannot continue - no database name found in wrightetmathon.ini file. Contact your system administrator");
}

$database_name				=	trim($database_array[1]);

$database_name				=	preg_replace("/[^a-zA-Z0-9\s]/", "", $database_name);

$active_group = 'default';
$active_record = TRUE;

$hostname = isset($hostname_array[1]) ? trim($hostname_array[1]) : '';
$hostname				=	str_replace('"', "", $hostname);
if ($hostname == null)
{
	$hostname = "localhost";
}

$db['default']['hostname'] = $hostname;
$db['default']['username'] = 'admin';
$db['default']['password'] = 'Son@Risa&11';
$db['default']['database'] = $database_name;
$db['default']['dbdriver'] = 'mysqli';
$db['default']['dbprefix'] = 'ospos_';
$db['default']['pconnect'] = FALSE;
$db['default']['db_debug'] = TRUE;
$db['default']['cache_on'] = FALSE;
$db['default']['cachedir'] = '';
$db['default']['char_set'] = 'utf8';
$db['default']['dbcollat'] = 'utf8_general_ci';
$db['default']['swap_pre'] = '';
$db['default']['autoinit'] = TRUE;
$db['default']['stricton'] = FALSE;


/* End of file database.php */
/* Location: ./application/config/database.php */
