<?php
namespace Framework\Syscrack\Game;

/**
 * Lewis Lancaster 2017
 *
 * Class Processes
 *
 * @package Framework\Syscrack\Game
 */

use Framework\Application\Settings;
use Framework\Application\Utilities\Factory;
use Framework\Application\Utilities\FileSystem;
use Framework\Database\Tables\Processes as Database;
use Framework\Exceptions\SyscrackException;
use Framework\Syscrack\Game\Structures\Process;

class Processes
{

    /**
     * @var Factory
     */

    protected $factory;

    /**
     * @var Database
     */

    protected $database;

    /**
     * Processes constructor.
     *
     * @param bool $autoload
     */

    public function __construct( $autoload=true )
    {

        $this->factory = new Factory( Settings::getSetting('syscrack_processes_namespace') );

        $this->database = new Database();

        if( $autoload )
        {

            $this->getProcessesClasses();
        }
    }

    /**
     * Returns true if a process exists
     *
     * @param $processid
     *
     * @return bool
     */

    public function processExists( $processid )
    {

        if( $this->database->getProcess( $processid ) == null )
        {

            return false;
        }

        return true;
    }

    /**
     * Gets a process
     *
     * @param $processid
     *
     * @return \Illuminate\Support\Collection|null
     */

    public function getProcess( $processid )
    {

        return $this->database->getProcess( $processid );
    }

    /**
     * Gets all of the users processes
     *
     * @param $userid
     *
     * @return \Illuminate\Support\Collection|null
     */

    public function getUserProcesses( $userid )
    {

        return $this->database->getUserProcesses( $userid );
    }

    /**
     * Gets all the computers processes
     *
     * @param $computerid
     */

    public function getComputerProcesses( $computerid )
    {

        $this->database->getComputerProcesses( $computerid );
    }

    /**
     * Creates a new process and adds it to the database
     *
     * @param $timecompleted
     *
     * @param $computerid
     *
     * @param $userid
     *
     * @param $process
     *
     * @param array $data
     *
     * @return int
     */

    public function createProcess( $timecompleted, $computerid, $userid, $process, array $data )
    {

        if( $this->findProcessClass( $process ) == false )
        {

            throw new SyscrackException();
        }

        $result = $this->callProcessMethod( $this->findProcessClass( $process ), 'onCreation', array(
            'timecompleted' => $timecompleted,
            'computerid'    => $computerid,
            'userid'        => $userid,
            'process'       => $process,
            'data'          => $data
        ));

        if( $result == false )
        {

            return false;
        }

        return $this->addToDatabase( $timecompleted, $computerid, $userid, $process, $data );
    }

    /**
     * Adds a process to the database
     *
     * @param $timecompleted
     *
     * @param $computerid
     *
     * @param $userid
     *
     * @param $process
     *
     * @param array $data
     *
     * @return int
     */

    public function addToDatabase( $timecompleted, $computerid, $userid, $process, array $data )
    {

        $array = array(
            'timecompleted' => $timecompleted,
            'timestarted'   => time(),
            'computerid'    => $computerid,
            'userid'        => $userid,
            'process'       => $process,
            'data'          => json_encode( $data )
        );

        return $this->database->insertProcess( $array );
    }

    /**
     * Completes the process
     *
     * @param $processid
     */

    public function completeProcess( $processid )
    {

        $process = $this->getProcess( $processid );

        if( empty( $process ) )
        {

            throw new SyscrackException();
        }

        $result = $this->callProcessMethod( $this->findProcessClass( $process ), 'onCompletion', array(
            'timecompleted' => $process->timecompleted,
            'timestarted'   => $process->timestarted,
            'computerid'    => $process->computerid,
            'userid'        => $process->userid,
            'process'       => $process->process,
            'data'          => json_decode( $process->data, true )
        ));

        if( $result == false )
        {

            throw new SyscrackException();
        }

        $this->database->trashProcess( $processid );
    }

    /**
     * Returns true if the user has processes
     *
     * @param $userid
     *
     * @return bool
     */

    public function userHasProcesses( $userid )
    {

        if( $this->database->getUserProcesses( $userid ) == null )
        {

            return false;
        }

        return true;
    }

    /**
     * Returns true if the computer has processes
     *
     * @param $computerid
     *
     * @return bool
     */

    public function computerHasProcesses( $computerid )
    {

        if( $this->database->getComputerProcesses( $computerid ) == null )
        {

            return false;
        }

        return true;
    }

    /**
     * Returns true if we have this process class
     *
     * @param $process
     *
     * @return bool
     */

    public function hasProcessClass( $process )
    {

        if( $this->factory->hasClass( $process ) == false )
        {

            return false;
        }

        return true;
    }

    /**
     * Finds a process class
     *
     * @param $process
     *
     * @return mixed|null
     */

    public function findProcessClass( $process )
    {

        return $this->factory->findClass( $process );
    }

    /**
     * Calls a method inside the process class
     *
     * @param Process $process
     *
     * @param string $method
     *
     * @param array $data
     *
     * @return mixed
     */

    private function callProcessMethod( Process $process, $method='onCreation', array $data )
    {

        if( $process instanceof Process === false )
        {

            throw new SyscrackException();
        }

        if( $this->isCallable( $process, $method ) == false )
        {

            throw new SyscrackException();
        }

        return call_user_func_array( array( $process, $method ), $data );
    }

    /**
     * Returns true if the function is callable
     *
     * @param $process
     *
     * @param $method
     *
     * @return bool
     */

    private function isCallable( $process, $method )
    {

        $class = new \ReflectionClass( $process );

        if( empty( $class ) )
        {

            return false;
        }

        if( $class->getMethod( $method )->isPublic() == false )
        {

            return false;
        }

        return true;
    }

    /**
     * Gets all the processes
     *
     * @return array|Structures\Software|null|\stdClass
     */

    private function getProcessesClasses()
    {

        $files = FileSystem::getFilesInDirectory( Settings::getSetting('syscrack_processes_location') );

        if( empty( $files ) )
        {

            return null;
        }

        foreach( $files as $file )
        {

            $this->factory->createClass( FileSystem::getFileName( $file ) );
        }

        return $this->factory->getAllClasses();
    }

}