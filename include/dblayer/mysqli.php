<?php

/**
 * CED Portal MySQLi Database Layer
 * Adapted from FluxBB
 */

class DBLayer
{
    private $link_id;
    private $query_result;
    private $in_transaction = false;
    
    public $prefix;
    public $num_queries = 0;
    public $saved_queries = array();
    
    function __construct($db_host, $db_username, $db_password, $db_name, $db_prefix, $p_connect)
    {
        $this->prefix = $db_prefix;
        
        // Connect to database
        if ($p_connect)
            $this->link_id = @mysqli_connect('p:'.$db_host, $db_username, $db_password);
        else
            $this->link_id = @mysqli_connect($db_host, $db_username, $db_password);
        
        if (!$this->link_id)
            error('Unable to connect to MySQL. MySQL reported: '.mysqli_connect_error(), __FILE__, __LINE__);
        
        // Select database
        if (!@mysqli_select_db($this->link_id, $db_name))
            error('Unable to select database. MySQL reported: '.mysqli_error($this->link_id), __FILE__, __LINE__);
        
        // Set charset
        $this->set_charset('utf8mb4');
        
        return $this->link_id;
    }
    
    function start_transaction()
    {
        ++$this->num_queries;
        $this->in_transaction = true;
        
        return @mysqli_query($this->link_id, 'START TRANSACTION');
    }
    
    function end_transaction()
    {
        ++$this->num_queries;
        $this->in_transaction = false;
        
        if (@mysqli_query($this->link_id, 'COMMIT'))
            return true;
        else
        {
            @mysqli_query($this->link_id, 'ROLLBACK');
            return false;
        }
    }
    
    function query($sql, $unbuffered = false)
    {
        if (strlen($sql) > 140000)
            error('Insane query. SQL query length: '.strlen($sql).' bytes.');
        
        if (defined('CED_SHOW_QUERIES'))
            $q_start = microtime(true);
        
        ++$this->num_queries;
        
        if ($unbuffered)
            $this->query_result = @mysqli_query($this->link_id, $sql, MYSQLI_USE_RESULT);
        else
            $this->query_result = @mysqli_query($this->link_id, $sql);
        
        if ($this->query_result)
        {
            if (defined('CED_SHOW_QUERIES'))
                $this->saved_queries[] = array($sql, sprintf('%.5f', microtime(true) - $q_start));
            
            return $this->query_result;
        }
        else
        {
            if (defined('CED_SHOW_QUERIES'))
                $this->saved_queries[] = array($sql, 0);
            
            return false;
        }
    }
    
    function result($query_id = 0, $row = 0, $col = 0)
    {
        if ($query_id)
        {
            if ($row !== 0 && @mysqli_data_seek($query_id, $row) === false)
                return false;
            
            $cur_row = @mysqli_fetch_row($query_id);
            
            if ($cur_row === false)
                return false;
            
            return $cur_row[$col];
        }
        else
            return false;
    }
    
    function fetch_assoc($query_id = 0)
    {
        return ($query_id) ? @mysqli_fetch_assoc($query_id) : false;
    }
    
    function fetch_row($query_id = 0)
    {
        return ($query_id) ? @mysqli_fetch_row($query_id) : false;
    }
    
    function num_rows($query_id = 0)
    {
        return ($query_id) ? @mysqli_num_rows($query_id) : false;
    }
    
    function affected_rows()
    {
        return ($this->link_id) ? @mysqli_affected_rows($this->link_id) : false;
    }
    
    function insert_id()
    {
        return ($this->link_id) ? @mysqli_insert_id($this->link_id) : false;
    }
    
    function get_num_queries()
    {
        return $this->num_queries;
    }
    
    function get_saved_queries()
    {
        return $this->saved_queries;
    }
    
    function free_result($query_id = false)
    {
        if (!$query_id)
            $query_id = $this->query_result;
        
        return ($query_id) ? @mysqli_free_result($query_id) : false;
    }
    
    function escape($str)
    {
        return mysqli_real_escape_string($this->link_id, $str);
    }
    
    function error()
    {
        $result['error_sql'] = @current(@end($this->saved_queries));
        $result['error_no'] = @mysqli_errno($this->link_id);
        $result['error_msg'] = @mysqli_error($this->link_id);
        
        return $result;
    }
    
    function close()
    {
        if ($this->link_id)
        {
            if ($this->in_transaction)
            {
                if (defined('CED_SHOW_QUERIES'))
                    $this->saved_queries[] = array('COMMIT', 0);
                
                @mysqli_query($this->link_id, 'COMMIT');
            }
            
            return @mysqli_close($this->link_id);
        }
        else
            return false;
    }
    
    function set_charset($charset)
    {
        return mysqli_set_charset($this->link_id, $charset);
    }
    
    function get_version()
    {
        $result = $this->query('SELECT VERSION()');
        
        return array(
            'name' => 'MySQL',
            'version' => preg_replace('/^([^-]+).*$/', '\\1', $this->result($result))
        );
    }
    
    function table_exists($table_name, $no_prefix = false)
    {
        $result = $this->query('SHOW TABLES LIKE \''.($no_prefix ? '' : $this->prefix).$this->escape($table_name).'\'');
        return $this->num_rows($result) > 0;
    }
    
    function field_exists($table_name, $field_name, $no_prefix = false)
    {
        $result = $this->query('SHOW COLUMNS FROM '.($no_prefix ? '' : $this->prefix).$table_name.' LIKE \''.$this->escape($field_name).'\'');
        return $this->num_rows($result) > 0;
    }
    
    function index_exists($table_name, $index_name, $no_prefix = false)
    {
        $result = $this->query('SHOW INDEX FROM '.($no_prefix ? '' : $this->prefix).$table_name);
        while ($cur_index = $this->fetch_assoc($result))
        {
            if (strtolower($cur_index['Key_name']) == strtolower($index_name))
                return true;
        }
        
        return false;
    }
}

