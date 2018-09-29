<?php

class Block
{
    private $element_data = array();

    public function __construct($block_name, $array_of_params, $parent = '', $child = '')
    {
        if (!empty($block_name))
        {
            $this->block_name = $block_name;

            if (!empty($array_of_params))
            {
                $this->element_data[]['vars'] = $array_of_params;
            }
            return true;
        }
        else
        {
            trigger_error('Block must have a name', E_USER_WARNING);

            return false;
        }
    }

    public function get_current_block_index()
    {
        $arr = array_keys($this->element_data);
        return end($arr);
    }

    /*
     * block name is this element
     */
    public function define_block_vars($array_of_data)
    {
        /*
        if (!empty($array_of_data))
        {
        */
        return $this->element_data[]['vars'] = $array_of_data;
        /*
        }
        else
        {
            trigger_error('Block data must not be empty', E_USER_WARNING);

            return false;
        }
        */
    }

    /*
     * block name is this element
     */
    public function define_block_if($if_name, $if_condition = false)
    {
        if (!empty($if_name))
        {
            {
                $index = $this->get_current_block_index();
                return $this->element_data[$index]['ifs'][$if_name] = (empty($if_condition) ? false : $if_condition);
            }
        }
        else
        {
            trigger_error('Block If must have a name', E_USER_WARNING);

            return false;
        }
    }

    /*
     * block name is this element
     */
    public function define_block_switch($switch_name, $switch_condition = false)
    {
        return false;
    }

    public function define_block_switch_case($switch_name, $case_name, $case_condition = false)
    {
        return false;
    }

    public function &__get($var)
    {
        return $this->{$var};
    }

    public function __set($var, $value)
    {
        return $this->{$var} = $value;
    }

    function __destruct()
    {
        unset($this->element_data);
    }

    public function __toString()
    {
        return var_export($this, true);
    }
}

?>