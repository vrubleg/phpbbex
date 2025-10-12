<?php
/**
* @package phpBBex Support Toolkit
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

// Load functions_admin.php if required
require_once(PHPBB_ROOT_PATH . 'includes/functions_admin.php');

class critical_repair
{
	/**
	* @var Array Tools that are autoran
	*/
	var $autorun_tools = [];

	/**
	* @var Array Tools that are manually invoked
	*/
	var $manual_tools = [];

	/**
	* @var string Location for the tools
	*/
	var $tool_path;

	/**
	* initialize critical repair.
	* This method loads all critical repair tools
	* @return void
	*/
	function initialize()
	{
		$this->tool_path = STK_ROOT_PATH . 'includes/critical_repair/';
		$filelist = filelist($this->tool_path, '', 'php');

		foreach ($filelist as $directory => $tools)
		{
			if ($directory != 'autorun/')
			{
				if (sizeof($tools))
				{
					foreach ($tools as $tool)
					{
						$this->manual_tools[] = substr($tool, 0, strpos($tool, '.'));
					}
				}
			}
			else
			{
				if (sizeof($tools))
				{
					foreach ($tools as $tool)
					{
						$this->autorun_tools[] = substr($tool, 0, strpos($tool, '.'));
					}
				}
			}
		}

		return true;
	}

	/**
	* Run a manual critical repair tol
	* @param	String	$tool The name (file/class) of the tool
	* @return	mixed	The result of the tool
	*/
	function run_tool($tool)
	{
		if (!(in_array($tool, $this->manual_tools)))
		{
			return false;
		}

		require($this->tool_path . $tool . '.php');

		$tool_name = 'erk_' . $tool;
		$run_tool = new $tool_name();
		return $run_tool->run();
	}

	/**
	* Run all the automatic critical repair tools
	* @return void
	*/
	function autorun_tools()
	{
		foreach ($this->autorun_tools as $tool)
		{
			require($this->tool_path . 'autorun/' . $tool . '.php');

			$tool_name = 'erk_' . $tool;
			$run_tool = new $tool_name();
			$run_tool->run();
			unset($run_tool);
		}

		return true;
	}

	/**
	 * Trigger an error message, this method *must* be called when an ERK tool
	 * encounters an error. You can not rely on msg_handler!
	 * @param	String|Array	$msg				The error message or an string array containing multiple lines
	 * @param	Boolean			$redirect_stk		Show a backlink to the STK, otherwise to the ERK
	 * @return	void
	 */
	function trigger_error($msg, $redirect_stk = false)
	{
		if (!is_array($msg))
		{
			$msg = [$msg];
		}

		// Send headers
		header('HTTP/1.1 503 Service Unavailable');
		header('Content-type: text/html; charset=UTF-8');

		// Build the page
		?>
<!DOCTYPE html>
<html dir="ltr">
	<head>
		<meta charset="utf-8" />
		<title>Emergency Repair Kit</title>
		<link href="<?php echo STK_ROOT_PATH; ?>style/style.css" rel="stylesheet" media="screen" />
	</head>
	<body id="errorpage">
		<div id="wrap">
			<div id="page-header">
				<h1>Emergency Repair Kit</h1>
				<p>
					<a href="<?php echo STK_ROOT_PATH; ?>">Support Toolkit index</a> &bull;
					<a href="<?php echo PHPBB_ROOT_PATH; ?>">Board index</a>
				</p>
			</div>
			<div id="page-body">
				<div id="acp">
					<div class="panel">
						<div id="content">
							<h1>Emergency Repair Kit</h1>
							<?php
							foreach ($msg as $m)
							{
								echo "<p>{$m}</p>";
							}
							?>
							<p>
								<?php
								if ($redirect_stk)
								{
									echo '<a href="' . STK_ROOT_PATH . '">Click here to reload the STK</a>';
								}
								else
								{
									echo '<a href="' . STK_ROOT_PATH . 'erk.php">Click here to reload the ERK</a>';
								}
								?>
							</p>
						</div>
					</div>
				</div>
			</div>
			<div id="page-footer">
				Powered by <a href="//phpbbex.com/">phpBBex</a>
			</div>
		</div>
	</body>
</html>
<?php
		// Make sure we exit, can't use any phpBB stuff here
		exit;
	}
}
