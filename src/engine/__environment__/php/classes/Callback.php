<?php

// Use dependencies.
use Cocur\BackgroundProcess\BackgroundProcess;

/*
 * Callback
 *
 * Loads an index callback as a separate PHP process
 * that can be started, stopped, and queried as needed.
 */
 class Callback extends BackgroundProcess {

  // Set timeout (in seconds), where `0` dsiabled timeout altogether.
  const CALLBACK_TIMEOUT = 36000;

  // The output location.
  const CALLBACK_OUTPUT = CONFIG['engine']['cache']['logs'];

  // The callback ID.
  public $id;

  // The callback's prepared source file.
  public $callback;

  // The callback's command.
  public $command;

  // Constructor
  function __construct( string $id, string $file, array $constants = [] ) {

    // Save the callback ID.
    $this->id = $id;

    // Determine the cache path that would've been used for previous callback calls.
    $path = cleanpath(Cache::path(CONFIG['engine']['cache']['tmp'])."/$id.php");

    // Delete any prepared files if found.
    if( Cache::exists($path) ) Cache::delete($path);

    // Prepare the callback file for execution.
    $this->callback = $this->prepare($file, $constants);

    // Convert the callback to a command.
    $this->command = (DEVELOPMENT ? 'php' : '/opt/rh/rh-php70/root/usr/bin/php').' -f '.$this->callback['path'];

    // Initialize the callback as a background process.
    parent::__construct($this->command);

    // Ensure that the output location exists.
    if( !Cache::isDirectory(self::CALLBACK_OUTPUT) ) Cache::make(self::CALLBACK_OUTPUT, 0777);

   }

  // Fire the callback.
  public function fire() {

    // Kill any existing processes with the given ID.
    self::kill($this->id);

    // Run the process.
    $this->run(self::CALLBACK_OUTPUT.'/'.$this->id);

    // Verify that the process is running.
    if( $this->isRunning() ) {

      // Capture the process' PID.
      $pid = $this->getPid();

      // Add the process to the callback log.
      Index::callback($this->id, $pid, true);

      // Return the process' PID.
      return $pid;

    }

  }

  // Prepare the callback for execution.
  protected function prepare( string $file, array $constants = [] ) {

    // Get the contents of the callback file as the callback command.
    $command = trim(preg_replace('/\?\>$/', '', preg_replace('/^\<\?php/', '', file_get_contents($file))));

    // Assign constants within the callback command.
    foreach( $constants as $constant => $value ) {

      // Define the constant within the callback $command.
      $command = 'define("'.strtoupper($constant).'", '.var_export($value, true).');'."\n".$command;

    }

    // Set callback command's timeout.
    $command = 'set_time_limit('.self::CALLBACK_TIMEOUT.');'."\n".$command;

    // Compile the command as valid PHP.
    $php = "<?php $command ?>";

    // Save the PHP temporarily as a file.
    $tmp = Cache::tmp($php, $this->id.'.php', 0777);

    // Return the prepared temporary file.
    return $tmp;

  }

  // Kill an existing callback.
  public static function kill( string $id ) {

    // If the process exists and is running, kill it now.
    if( ($pid = Index::callback($id)) !== false ) {

      // Get the process by PID.
      $process = BackgroundProcess::createFromPID($pid);

      // If the process is still running, then kill it now.
      if( $process->isRunning() ) $process->stop();

      // Remove any callback log entries for the related process.
      Index::callback($id, $pid, false);

    }

  }

 }

?>
