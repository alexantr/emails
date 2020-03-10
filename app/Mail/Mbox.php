<?php
/**
 * Class to read mbox mail files.
 *
 * @category Mail
 * @package  Mail_Mbox
 * @author   Roberto Berto <darkelder@php.net>
 * @author   Christian Weiske <cweiske@php.net>
 * @license  http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version  CVS: $Id: Mbox.php 290486 2009-11-10 20:42:51Z cweiske $
 * @link     http://pear.php.net/package/Mail_Mbox
 */


/**
 * Class to read mbox mail files.
 *
 * An mbox mail file is contains plain emails concatenated in one
 * big file. Since each mail starts with "From ", and ends with a newline,
 * they can be separated from each other.
 *
 * This class takes a mbox filename in the constructor, generates an
 * index where the mails start and end when calling open() and returns
 * single mails with get(), using the positions in the index.
 *
 * With the help of this class, you also can add(), remove() and update()
 * messages in the mbox file. When calling one of this methods, the class
 * checks if the file has been modified since the index was created -
 * changing the file with the wrong positions in the index would very likely
 * corrupt it.
 * This check is not done when retrieving single messages via get(), as this
 * would slow down the process if you retrieve thousands of mails. You can,
 * however, call hasBeenModified() before using get() to check for modification
 * yourself. If the method returns true, you should close() and re-open() the
 * file.
 *
 * If something strange happens and you don't know why, activate debugging with
 * setDebug(true). You also can modify the temporary directory in which changed
 * mboxes are stored when adding/removing/modifying by using setTmpDir('/path/');
 *
 * See @link tags for specifications.
 *
 * @category Mail
 * @package  Mail_Mbox
 * @author   Roberto Berto <darkelder@php.net>
 * @author   Christian Weiske <cweiske@php.net>
 * @license  http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @link     http://pear.php.net/package/Mail_Mbox
 * @link http://en.wikipedia.org/wiki/Mbox
 * @link http://www.qmail.org/man/man5/mbox.html
 */
class Mail_Mbox
{
    /**
     * File resource / handle
     *
     * @var resource
     */
    protected $_resource = null;

    /**
     * Message index. Each mail has its own subarray,
     * which contains the start position and end position
     * as first and second subindex.
     *
     * @var array
     */
    protected $_index = null;

    /**
     * Timestamp at which the file has been modified last.
     *
     * @var int
     */
    protected $_lastModified = null;

    /**
     * Directory in which the temporary mbox files are created.
     * Even if it's a unix directory, it does work on windows as
     * the only function it's used in is tempnam which automatically
     * chooses the right temp directory if this here doesn't exist.
     * So this variable is for special needs only.
     *
     * @var     string
     * @see     getTmpDir()
     * @see     setTmpDir()
     */
    public $tmpdir = '/tmp';

    /**
     * Determines if the file is automatically re-opened and its
     * structure is parsed after modifying it. Setting this to false
     * makes you responsible for calling open() by hand, but is
     * *a lot* faster when appending many messages.
     *
     * @var     bool
     */
    public $autoReopen = true;
    /**
     * @var string
     */
    protected $_file;


    /**
     * Create a new Mbox class instance.
     * After creating it, you should use open().
     *
     * @param string $file Filename to open.
     */
    public function __construct($file)
    {
        $this->_file = $file;
    }

    /**
     * Open the mbox file
     *
     * Also, this function will process the Mbox and create a cache
     * that tells each message start and end bytes.
     *
     * @param bool $create
     * @return bool True if all went ok, PEAR_Error on failure
     * @throws \Exception
     */
    public function open($create = false)
    {
        // check if file exists else return pear error
        if (!is_file($this->_file)) {
            if ($create) {
                return $this->_create();
            } else {
                throw new Exception('Cannot open the mbox file "' . $this->_file . '": file does not exist.');
            }
        }

        // opening the file
        $this->_lastModified = filemtime($this->_file);
        $this->_resource = fopen($this->_file, 'r');
        if (!is_resource($this->_resource)) {
            throw new Exception('Cannot open the mbox file: maybe without permission.');
        }

        // process the file and get the messages bytes offsets
        $this->_process();

        return true;
    }

    /**
     * Creates the file
     *
     * @return boolean True if it was created, false if it already
     *                 existed. PEAR_Error in case it could not
     *                 be created.
     *
     * @throws \Exception
     */
    protected function _create()
    {
        if (is_file($this->_file)) {
            return false;
        }

        //We should maybe try to check if the directory
        // is writable here. But that's too much fuss for now.
        touch($this->_file);

        if (is_file($this->_file)) {
            return true;
        }

        //error
        throw new Exception('File could not be created');
    }

    /**
     * Re-opens the file and parses the messages again.
     * Used by other methods to be able to be able to prevent
     * re-opening the file.
     *
     * @return mixed See open() for return values. Returns true if
     *               $this->autoReopen is false.
     */
    protected function _reopen()
    {
        if ($this->autoReopen) {
            return $this->open();
        }
        return true;
    }

    /**
     * Close a Mbox
     *
     * Close the Mbox file opened by open()
     *
     * @return mixed true on success, else PEAR_Error
     * @throws \Exception
     */
    public function close()
    {
        if (!is_resource($this->_resource)) {
            throw new Exception('Cannot close the mbox file because it was not open.');
        }

        if (!fclose($this->_resource)) {
            throw new Exception('Cannot close the mbox, maybe file is being used (?)');
        }

        return true;
    }

    /**
     * Get number of messages in this mbox
     *
     * @return int Number of messages on Mbox (starting on 1,
     *             0 if no message exists)
     */
    public function size()
    {
        if ($this->_index !== null) {
            return sizeof($this->_index);
        } else {
            return 0;
        }
    }

    /**
     * Get a message from the mbox
     *
     * Note: Message numbers start from 0.
     *
     * @param int $message The number of the message to retrieve
     *
     * @return string Return the message, PEAR_Error on error
     * @throws \Exception
     */
    public function get($message)
    {
        // checking if we have bytes locations for this message
        if (!is_array($this->_index[$message])) {
            throw new Exception('Message does not exist.');
        }

        // getting bytes locations
        $bytesStart = $this->_index[$message][0];
        $bytesEnd = $this->_index[$message][1];

        if (!is_resource($this->_resource)) {
            throw new Exception('Mbox resource is not valid. Maybe you need to re-open it?');
        }

        // seek to start of message
        if (fseek($this->_resource, $bytesStart) == -1) {
            throw new Exception('Cannot read message bytes');
        }

        if ($bytesEnd - $bytesStart <= 0) {
            throw new Exception('Message byte length is negative');
        }

        // reading and returning message
        // (bytes to read = difference of bytes locations)
        $msg = fread($this->_resource, $bytesEnd - $bytesStart);
        return $this->_unescapeMessage($msg);
    }

    /**
     * Remove a message from Mbox and save it.
     *
     * Note: messages start with 0.
     *
     * @param int $message The number of the message to remove, or
     *                     array of message ids to remove
     *
     * @return mixed Return true else PEAR_Error
     * @throws \Exception
     */
    public function remove($message)
    {
        if ($this->hasBeenModified()) {
            throw new Exception('File has been modified since loading. Re-open the file.');
        }

        // convert single message to array
        if (!is_array($message)) {
            $message = [$message];
        }

        // checking if we have bytes locations for this message
        foreach ($message as $msg) {
            if (!isset($this->_index[$msg])
                || !is_array($this->_index[$msg])
            ) {
                throw new Exception('Message ' . $msg . 'does not exist.');
            }
        }

        // changing umask for security reasons
        $umaskOld = umask(077);
        // creating temp file
        $ftempname = tempnam($this->tmpdir, 'Mail_Mbox');
        // returning to old umask
        umask($umaskOld);

        $ftemp = fopen($ftempname, 'w');
        if ($ftemp === false) {
            throw new Exception('Cannot create a temp file "' . $ftempname . '".');
        }

        // writing only undeleted messages
        $messages = $this->size();

        for ($x = 0; $x < $messages; $x++) {
            if (in_array($x, $message)) {
                continue;
            }

            $messageThis = $this->_escapeMessage($this->get($x));
            if (is_string($messageThis)) {
                fwrite($ftemp, $messageThis, strlen($messageThis));
            }
        }

        // closing file
        $this->close();
        fclose($ftemp);

        return $this->_move($ftempname, $this->_file);
    }

    /**
     * Update a message
     *
     * Note: messages start with 0.
     *
     * @param int $message The number of Message to update
     * @param string $content The new content of the Message
     *
     * @return mixed Return true if all is ok, else PEAR_Error
     * @throws \Exception
     */
    public function update($message, $content)
    {
        if (!$this->_isValid($content)) {
            throw new Exception('Message is invalid');
        }

        if ($this->hasBeenModified()) {
            throw new Exception('File has been modified since loading. Re-open the file.');
        }

        // checking if we have bytes locations for this message
        if (!is_array($this->_index[$message])) {
            throw new Exception('Message does not exist.');
        }

        // creating temp file
        $ftempname = tempnam($this->tmpdir, 'Mail_Mbox');
        $ftemp = fopen($ftempname, 'w');
        if ($ftemp === false) {
            throw new Exception('Cannot create temp file "' . $ftempname . '" .');
        }

        $messages = $this->size();

        for ($x = 0; $x < $messages; $x++) {
            if ($x == $message) {
                $messageThis = $content;
            } else {
                $messageThis = $this->get($x);
            }

            if (is_string($messageThis)) {
                $messageThis = $this->_escapeMessage($messageThis);
                fwrite($ftemp, $messageThis, strlen($messageThis));
            }
        }

        // closing file
        $this->close();
        fclose($ftemp);

        return $this->_move($ftempname, $this->_file);
    }

    /**
     * Insert a message
     *
     * PEAR::Mail_Mbox will insert the message according its offset.
     * 0 means before the actual message 0. 3 means before the message 3
     * (Remember: message 3 is the fourth message). The default is put
     * AFTER the last message (offset = null).
     *
     * @param string $content The content of the new message
     * @param int $offset Before the offset. Default: last message (null)
     *
     * @return mixed Return true else PEAR_Error object
     * @throws \Exception
     */
    public function insert($content, $offset = null)
    {
        if (!$this->_isValid($content)) {
            throw new Exception('Message is invalid');
        }

        if ($this->hasBeenModified()) {
            throw new Exception('File has been modified since loading. Re-open the file.');
        }

        // optimize insert() to use append whenever possible
        if ($offset < 0 || $offset == $this->size() || $this->size() == 0) {
            return $this->append($content);
        }

        // creating temp file
        $ftempname = tempnam($this->tmpdir, 'Mail_Mbox');
        $ftemp = fopen($ftempname, 'w');
        if ($ftemp === false) {
            throw new Exception('Cannot create temp file "' . $ftempname . '".');
        }

        // writing only undeleted messages
        $messages = $this->size();
        $content = $this->_escapeMessage($content);

        if ($messages == 0 && $offset !== null) {
            fwrite($ftemp, $content, strlen($content));
        } else {
            for ($x = 0; $x < $messages; $x++) {
                if ($offset !== null && $x == $offset) {
                    fwrite($ftemp, $content, strlen($content));
                }
                $messageThis = $this->_escapeMessage($this->get($x));

                if (is_string($messageThis)) {
                    fwrite($ftemp, $messageThis, strlen($messageThis));
                }
            }
        }

        if ($offset === null) {
            fwrite($ftemp, $content, strlen($content));
        }

        // closing file
        $this->close();
        fclose($ftemp);

        return $this->_move($ftempname, $this->_file);
    }

    /**
     * Appends a message at the end of the file.
     *
     * This method is also used by insert() since it's faster.
     *
     * @param string $content The content of the new message
     *
     * @return mixed Return true else PEAR_Error object
     * @throws \Exception
     */
    public function append($content)
    {
        if (!$this->_isValid($content)) {
            throw new Exception('Message is invalid');
        }

        $this->close();
        $content = $this->_escapeMessage($content);

        $fp = fopen($this->_file, 'a');
        if ($fp === false) {
            throw new Exception('Cannot open file "' . $this->_file . '" for appending.');
        }

        if (fwrite($fp, $content, strlen($content)) === false) {
            throw new Exception('Cannot write to file "' . $this->_file . '".');
        }

        return $this->_reopen();
    }

    /**
     * Checks if the given message is valid.
     * If it was invalid and we'd add it to the file,
     * it would get unreadable
     *
     * @param string $content Message to be added or updated
     *
     * @return boolean True if it is valid, false if not
     */
    function _isValid($content)
    {
        if (substr($content, 0, 5) != 'From ') {
            return false;
        }

        return true;
    }

    /**
     * Move a file to another.
     *
     * Used internally to move the content of the temp file to the mbox file.
     * Note that we can't use rename() internally, as it behaves very, very
     * strange on windows.
     *
     * @param string $ftempname Source file - will be removed
     * @param string $filename Output file
     *
     * @return bool True if everything went fine, PEAR_Error when
     *                             an error happened.
     * @throws \Exception
     */
    protected function _move($ftempname, $filename)
    {
        if (!copy($ftempname, $filename)) {
            throw new Exception('Cannot copy "' . $ftempname . '" to "' . $filename . '".');
        }

        unlink($ftempname);

        // open another resource and substitute it to the old one
        $this->_file = $filename;
        return $this->_reopen();
    }

    /**
     * Process the Mbox
     *
     * Put start bytes and end bytes of each message into _index array
     *
     * @return bool True if all went ok, PEAR_Error on failure
     * @throws \Exception
     */
    protected function _process()
    {
        $this->_index = [];

        // sanity check
        if (!is_resource($this->_resource)) {
            throw new Exception('Resource is not valid. Maybe the file has not be opened?');
        }

        // going to start
        if (fseek($this->_resource, 0) == -1) {
            throw new Exception('Cannot read mbox');
        }

        // current start byte position
        $start = 0;
        // last start byte position
        //$laststart = 0;
        // there aren't any message
        $hasmessage = false;

        while ($line = fgets($this->_resource, 4096)) {
            // if line start with "From ", it is a new message
            if (0 === strncmp($line, 'From ', 5)) {
                // save last start byte position
                $laststart = $start;

                // new start byte position is the start of the line
                $start = ftell($this->_resource) - strlen($line);

                // if it is not the first message add message positions
                if ($start > 0) {
                    $this->_index[] = [$laststart, $start - 1];
                } else {
                    // tell that there is really a message on the file
                    $hasmessage = true;
                }
            }
        }

        // if there are just one message, or if it's the last one,
        // add it to messages positions
        if (($start == 0 && $hasmessage === true) || ($start > 0)) {
            $this->_index[] = [$start, ftell($this->_resource)];
        }

        return true;
    }

    /**
     * Quotes "From " lines in the midst of the message.
     * And quoted "From " lines, too :)
     * Also appends the trailing newline.
     * After escaping, the message can be written to file.
     *
     * @param string $message Message content
     *
     * @return string Escaped message
     *
     * @see    _unescapeMessage()
     */
    protected function _escapeMessage($message)
    {
        if (substr($message, -1) == "\n") {
            $message .= "\n";
        } else {
            $message .= "\n\n";
        }
        return preg_replace(
            "/\n([>]*From )/",
            "\n>$1",
            $message
        );
    }

    /**
     * Removes quoted "From " lines from the message
     *
     * @param string $message Message content
     *
     * @return string Unescaped message
     *
     * @see    _escapeMessage()
     */
    protected function _unescapeMessage($message)
    {
        return preg_replace(
            "/\n>([>]*From )/",
            "\n$1",
            //the -1 drops the last newline
            substr($message, 0, -1)
        );
    }

    /**
     * Checks if the file was modified since it has been loaded.
     * If this is true, the file needs to be re-opened.
     *
     * @return bool  True if it has been modified.
     */
    public function hasBeenModified()
    {
        return filemtime($this->_file) > $this->_lastModified;
    }



    /*
     * Dumb getter and setter
     */


    /**
     * Set the directory for temporary files.
     *
     * @param string $tmpdir The new temporary directory
     *
     * @return mixed  True if all is ok, PEAR_Error if $tmpdir
     *                is a dir but not writable
     *
     * @throws \Exception
     * @see Mail_Mbox::$tmpdir
     */
    public function setTmpDir($tmpdir)
    {
        if (is_dir($tmpdir) && !is_writable($tmpdir)) {
            throw new Exception('"' . $tmpdir . '" is not writable.');
        } else {
            $this->tmpdir = $tmpdir;
            return true;
        }
    }

    /**
     * Returns the temporary directory
     *
     * @return string The temporary directory
     */
    public function getTmpDir()
    {
        return $this->tmpdir;
    }

    /**
     * Sets if the mbox is reloaded after modification
     * automatically.
     *
     * @param bool $autoReopen If the mbox is reloaded automatically
     *
     * @return void
     * @see Mail_Mbox::$autoReopen
     */
    public function setAutoReopen($autoReopen)
    {
        $this->autoReopen = (bool)$autoReopen;
    }

    /**
     * Returns the automatically reopening setting
     *
     * @return bool If the mbox is reloaded automatically.
     *
     * @see Mail_Mbox::$autoReopen
     */
    public function getAutoReopen()
    {
        return $this->autoReopen;
    }
}
