<?php
	if ($_POST || $_GET)
	{
		$testParameter = '';
		if (isset($_POST['test']))
		{
			$testParameter = $_POST['test'];
		}
		else if (isset($_GET['test']))
		{
			$testParameter = $_GET['test'];
		}
		
		if ($testParameter == 'true')
		{
			if (extension_loaded('mysqli'))
			{
				echo 'true';
			}	
			else
			{
				echo 'false';
			}
			return;
		}
	}
		
	if ($_POST)
	{	
		//give $checkPassword a value if you would like the mysql bridge to require
		//a password before continuing.  In the RazorSQL connection wizard,
		//enter the password into the service password field.
		$checkPassword = 'radmin';
		if ($checkPassword != null)
		{
			if (isset($_POST['service_password']))
			{
				$requestPassword = $_POST['service_password'];
				if ($checkPassword != $requestPassword)
				{
					echo '|ERROR|Invalid password for RazorSQL MySQL Bridge';
					return;
				}
			}
			else
			{
				echo '|ERROR|Invalid password for RazorSQL MySQL Bridge';
				return;
			}	
		}
		
		$action = $_POST['action'];
		if ($action == null)
		{
			echo'|ERROR|RazorSQL MySQL Service: No action found in request.';
			return;
		}
		
		$host = $_POST['host'];
		$port = $_POST['port'];
		$user = $_POST['user'];
		$password = $_POST['password'];
		$database = $_POST['database'];
		
		if ($host == null)
		{
			$host = 'localhost';
		}
		if ($port == null)
		{
			$port = '3306';
		}
		
		$link = mysqli_connect ($host, $user, $password, $database, $port);
		if (!$link)
		{
			die('|ERROR|Could not connect: ' . $link->error);
		}
		
		$state = '';
		
		if ($action == 'Statement::executeQuery')
		{
			$query = check_quotes($_POST['query']);
			$tableName = null;
			if (isset($_POST['tableName']))
			{
				$tableName = $_POST['tableName'];
			}
			$fetchSize = $_POST['fetchSize'];
			$fetchAll = $_POST['fetchAll'];
			$state = statementExecuteQuery($query, $fetchSize, $tableName, $fetchAll);
		}
		else if ($action == 'Statement::executeUpdate')
		{
			$query = check_quotes($_POST['query']);
			$state = statementExecuteUpdate($query);
		}
		else if ($action == 'Connection::getMetaData')
		{
			$state = connectionGetMetaData($user);
		}
		
		mysqli_close ($link);
		
		echo $state;
	}
	else
	{
		echo '|ERROR|RazorSQL MySQL Bridge: No valid data found in request.';
		return;
	}	
	
	function connectionGetMetaData($user)
	{
		global $link;
		$info = $link->server_info;	
		$majorVersion = substr($info, 0, strpos($info, '.'));
		
		$minor = substr($info, strpos($info,'.')+1, strlen($info));
		$minorVersion = substr($minor, 0, strpos($minor, '.'));
		
		$state = 'getDatabaseProductName=MySQL!~!getDatabaseProductVersion=' . $info .
			'!~!getUserName=' . $user . '!~!getDatabaseMajorVersion=' . $majorVersion . '!~!getDatabaseMinorVersion=' .$minorVersion .
			'!~!getDriverMajorVersion=1!~!getDriverMinorVersion=2!~!getDriverName=RazorSQL MySQL PHP Bridge';
			
		return $state;
	}	
	
	function statementExecuteQuery($query, $fetchSize, $tableName, $fetchAll)
	{	
		global $link;
		$query = str_replace("<r_newline>", "\n", $query);
		$result = $link->query($query);
		if (!$result) 
		{
			$message = '|ERROR|' . $link->error;
			return $message;
		}
		else
		{
			if ($fetchSize == null || $fetchSize < 0 || $fetchAll == 'true')
			{
				//max records to return is 10 million
				$fetchSize = 10000000;
			}
		
			$i = 0;
			$columnNames = '|COLUMNS|';
			$values = '|VALUES|';
			
			$i = 0;
			while ($i < $result->field_count)
			{
				$meta = $result->fetch_field();
				$columnNames .= $meta->name . '!~!';
				$i = $i + 1;
			}
			$i = 0;
			
			while ( ($row = $result->fetch_row()) && $i < $fetchSize) 
			{
				$count = count($row);
				$y = 0;
				while ($y < $count)
				{
					$c_row = current($row);
					$c_row = str_replace("\n", "<r_newline>", $c_row);
					$values .= $c_row . '!~!';
					next($row);
					$y = $y + 1;
				}
				$values .= '|END_ROW|';
				$i = $i + 1;
			}
			mysqli_free_result($result);
		}
		
		$retValue = $columnNames . $values;
		
		if ($tableName != null)
		{
			$result = $link->query('show columns from ' .$tableName);
			if ($result)
			{
				$columnNames = '|SHOW_NAMES|';
				$values = '|SHOW_VALUES|';
				
				$i = 0;
				while ($i < $result->fetch_count)
				{
					$meta = $result->fetch_field();
					$columnNames .= $meta->name . '!~!';
					$i = $i + 1;
				}
				
				$i = 0;
				while ( ($row = $result->fetch_row()) && $i < $fetchSize) 
				{
					$count = count($row);
					$y = 0;
					while ($y < $count)
					{
						$values .= current($row) . '!~!';
						next($row);
						$y = $y + 1;
					}
					$values .= '|END_ROW|';
					$i = $i + 1;
				}
				$retValue .= $columnNames . $values;
				mysqli_free_result($result);
			}
		}
		return ($retValue);
	}
	
	function statementExecuteUpdate ($query)
	{
		global $link;
		$query = str_replace("<r_newline>", "\n", $query);
		$result = $link->query($query);
		if (!$result) 
		{
			$message = '|ERROR|' . $link->error;
			return $message;
		}
		
		$message = '|UPDATED_ROWS|' . $link->affected_rows;	
		return $message;
	}
	
	function check_quotes($value)
	{
		if (get_magic_quotes_gpc()) 
		{
			$value = stripslashes($value);
		}
		return $value;
	}
?>