<?php

include_once 'Block.php';

/**
 * class for templates
 */
class Template
{
    private $template_content;
    private $templates_directory;

    private $dir_prefix = '';

    /*
        Дополнения из большого конфига
        $_config['tpl_ifs']
        $_config['tpl_vars']
    */
    public $tpl_global_addons;

    private $template_global_data = array(
        'vars' => array(),
        'ifs' => array(),
        'blocks' => array(),
        'switches' => array()
    );

    const COMPILED_DIRECTORY = 'Compiled';

    public function __construct($_config)
    {
        $dir_to_template_files = $_config['tpl_dir'];

        $this->dir_prefix = $_SERVER['DOCUMENT_ROOT'];

        if (!empty($_config['current_dir'])) {
            $this->dir_prefix = $_config['current_dir'];
        }

        if (empty($dir_to_template_files))
        {
            trigger_error(__METHOD__ . ' -> __construct: Need directory with template files', E_USER_WARNING);
            $this->__destruct();
            return false;
        }
        else
        {
            if ($dir_to_template_files[0] != '/')
            {
                $dir_to_template_files = '/' . $dir_to_template_files;
            }

            $l = strlen($dir_to_template_files);
            if ($dir_to_template_files[$l - 1] == '/')
            {
                $dir_to_template_files = substr($dir_to_template_files, 0, $l - 1);
            }

            $this->templates_directory = $dir_to_template_files;
            /*
             * check existing Compiled folder and rules on it
             */
            if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')
            {
                // trigger_error('Check directory "' . $this->dir_prefix . $this->templates_directory . '/' . self::COMPILED_DIRECTORY . '"', E_USER_NOTICE);
                if (!is_dir($this->dir_prefix . $this->templates_directory . '/' . self::COMPILED_DIRECTORY))
                {
                    if (!mkdir($this->dir_prefix . $this->templates_directory . '/' . self::COMPILED_DIRECTORY, 0777))
                    {
                        trigger_error(__METHOD__ . ' -> __construct: no dir "' . self::COMPILED_DIRECTORY . '" into templates directory and script can\'t create it', E_USER_WARNING);
                        $this->__destruct();
                        return false;
                    }
                }
            }
            return true;
        }
    }

    public function __get($var)
    {
        return $this->{$var};
    }

    public function __set($var, $value)
    {
        return $this->{$var} = $value;
    }

    /*
     * internal spec routines
     */
    private function filter_string($str)
    {
        if (preg_match('/^[A-Za-z0-9_\.]+$/', $str))
        {
            return $str;
        }
        else
        {
            $str = preg_replace('#[^A-Za-z0-9_.]#', '_', $str);
            $str = preg_replace('#_+#', '_', $str);

            if (preg_match('/[0-9]/', $str[0]))
            {
                $str = '_' . $str;
            }

            trigger_error('Wrong variable, we convert it into ' . $str . '', E_USER_NOTICE);

            return $str;
        }
    }

    private function prepare_array($array)
    {
        $str = array('"key" => "value"');

        foreach ($array as $key => $value)
        {
            $str[] = '"' . $key . '" => "' . trim($value) . '"';
        }

        return implode(', ', $str);
    }

    /*
     *	single operation
     */
    public function define_var($var_name, $var_value)
    {
        if (!empty($var_name))
        {
            $var_name = $this->filter_string($var_name);

            if (in_array(gettype($var_value), array('array', 'object', 'resource')))
            {
                $var_value = $this->prepare_array($var_value);
            }
            if (isset($this->template_global_data['vars'][$var_name]))
            {
                trigger_error('define_var detect duplicate key "' . $var_name . '", and override it', E_USER_NOTICE);

                /*
                 * do NOT return here - we must override it!
                 */
            }
            return $this->template_global_data['vars'][$var_name] = $var_value;
        }
        else
        {
            trigger_error('define_var can\'t define variable without name', E_USER_WARNING);

            return false;
        }
    }

    public function define_vars($array_of_defines)
    {
        // When foreach first starts executing, the internal array pointer is automatically reset to the first element of the array.
        foreach ($array_of_defines as $key => $value)
        {
            if (isset($this->template_global_data['vars'][$key]))
            {
                trigger_error('define_vars detect duplicate key "' . $key . '", and override it', E_USER_NOTICE);

                /*
                 * do NOT return here - we must override it!
                 */
            }

            $result[] = $this->define_var($key, $value);
        }

        return true;
    }

    public function define_if($if_name, $if_condition = false)
    {
        if (!empty($if_name))
        {
            $if_name = $this->filter_string($if_name);

            if (isset($this->template_global_data['ifs'][$if_name]))
            {
                trigger_error('define_if detect duplicate key "' . $if_name . '", and override it', E_USER_NOTICE);

                /*
                 * do NOT return here - we must override it!
                 */
            }
            return $this->template_global_data['ifs'][$if_name] = (empty($if_condition) ? false : $if_condition);
        }
        else
        {
            trigger_error('define_if can\'t define If without name', E_USER_WARNING);

            return false;
        }
    }

    public function define_switch($switch_name, $switch_variable = '')
    {
        if (!empty($switch_name))
        {
            $switch_name = $this->filter_string($switch_name);

            if (isset($this->template_global_data['switches'][$switch_name]))
            {
                trigger_error('define_switch detect duplicate key "' . $switch_name . '", and override it', E_USER_NOTICE);

                /*
                 * do NOT return here - we must override it!
                 */
            }
            return $this->template_global_data['switches'][$switch_name]['value'] = (empty($switch_variable) ? '' : $switch_variable);
        }
        else
        {
            trigger_error('define_switch can\'t define Switch without name', E_USER_WARNING);

            return false;
        }
    }

    public function define_switch_case($switch_name, $case_name, $case_condition = '')
    {
        if (!empty($switch_name) && !empty($case_name))
        {
            $switch_name = $this->filter_string($switch_name);
            $case_name = $this->filter_string($case_name);

            if (isset($this->template_global_data['switches'][$switch_name]['cases'][$case_name]))
            {
                trigger_error('define_switch_case detect duplicate key "' . $case_name . '" in switch "' . $switch_name . '", and override it', E_USER_NOTICE);

                /*
                 * do NOT return here - we must override it!
                 */
            }
            return $this->template_global_data['switches'][$switch_name]['cases'][$case_name] = (empty($case_condition) ? '' : $case_condition);
        }
        else
        {
            trigger_error('Global Switch Case must have block name and case name', E_USER_WARNING);

            return false;
        }
    }

    /*
     *	block operation
     */
    public function exist_block($block_name)
    {
        if (empty($block_name))
        {
            trigger_error('exist_block can\'t check Block without name', E_USER_WARNING);
        }
        else
        {
            $block_name = $this->filter_string($block_name);

            if (strstr($block_name, '.'))
            {
                // not first level
                $blocks = explode('.', $block_name);
                $needed_block = end($blocks);
                return $this->exist_block($needed_block);
            }
            else
            {
                // first level
                return isset($this->template_global_data['blocks'][$block_name]);
            }
        }
    }

    /*
     * function for recursion with raw data
     * $block - is raw block data
     */
    private function define_block_raw($block, $block_path, $array_of_defines = array())
    {
        $block_path_array = explode('.', $block_path);
        $current_index = $block->get_current_block_index();

        if (!isset($block->{'element_data'}[$current_index]))
        {
            trigger_error('define_block_raw not exist element ' . $current_index . ' by block "' . $block->{'block_name'} . '", create it', E_USER_NOTICE);
            /*
             * no need return because we create non-existing block
             */
            $block->{'element_data'}[$current_index] = array(
                'vars' => array(),
                'children' => array()
            );
        }

        /*
         * if we see an error like this: 'Indirect modification of overloaded property Block::$element_data has no effect in /.../class_template_engine.php on line ...'
         * it mean thet we need modify magic __get method in class of element like 'public function &__get($var)'
         *
         * here we detect pre-last block, if its true - we need just add the last block of values
         */
        if ($block->{'block_name'} == $block_path_array[count($block_path_array)-2])
        {
            $current_block_name = $block_path_array[count($block_path_array)-1];

            if (isset($block->{'element_data'}[$current_index]['children'][$current_block_name]))
            {
                return $block->{'element_data'}[$current_index]['children'][$current_block_name]->define_block_vars($array_of_defines);
            }
            else
            {
                return $block->{'element_data'}[$current_index]['children'][$current_block_name] = new Block($current_block_name, $array_of_defines);
            }
        }
        else
        {
            /*
             * but if its not pre-last block - we need get next block and call recursion
             */
            $searched_key = array_search($block->{'block_name'}, $block_path_array);
            $next_block_name = $block_path_array[$searched_key + 1];

            return $this->define_block_raw($block->{'element_data'}[$current_index]['children'][$next_block_name], $block_path, $array_of_defines);
        }

        /*
         * debug
         *
        echo '<p>$block: <pre>'.var_export($block,true).'</pre></p>';
        echo '<p>$current_index: <span>'.var_export($current_index,true).'</span></p>';
        echo '<p>$block_path: <span>'.var_export($block_path,true).'</span></p>';
        echo '<p>$array_of_defines: <pre>'.var_export($array_of_defines,true).'</pre></p>';

        echo '<hr>' . PHP_EOL;

        if ($current_index > 5)
        {
            die();
        }
        */
    }

    public function define_block($block_name, $array_of_defines = array())
    {
        if (empty($block_name))
        {
            trigger_error('define_block can\'t define Block without name', E_USER_WARNING);

            return false;
        }
        else
        {
            $block_name = $this->filter_string($block_name);

            if (strstr($block_name, '.'))
            {
                // not first level - we need check levels and assign each block to it parent
                $blocks = explode('.', $block_name);

                if (isset($this->template_global_data['blocks'][$blocks[0]]) && !empty($this->template_global_data['blocks'][$blocks[0]]))
                {
                    return $this->define_block_raw($this->template_global_data['blocks'][$blocks[0]], $block_name, $array_of_defines);
                }
                else
                {
                    trigger_error('define_block can\'t define blocks for a non-existing block "' . $blocks[0] . '"', E_USER_WARNING);

                    return false;
                }

            }
            else
            {
                // first level
                if (isset($this->template_global_data['blocks'][$block_name]))
                {
                    return $this->template_global_data['blocks'][$block_name]->define_block_vars($array_of_defines);
                }
                else
                {
                    return $this->template_global_data['blocks'][$block_name] = new Block($block_name, $array_of_defines);
                }
            }
        }
    }

    private function define_block_if_raw($block_name, $block_path, $if_name, $if_condition = false)
    {
        $eval_data_count = count($block_path) - 1;
        $eval_data = '$this->template_global_data[\'blocks\'][\'' . $block_name . '\']->{\'element_data\'}';

        if (!empty($block_path)) {
            foreach ($block_path as $block_path_child) {
                eval('$lastiteration = sizeof(' . $eval_data . ') - 1;');
                $eval_data .= '[' . $lastiteration . '][\'children\'][\'' . $block_path_child . '\']->{\'element_data\'}';
            }
        }

        eval('$lastiteration = sizeof(' . $eval_data . ') - 1;');
        $eval_data .= '[' . $lastiteration . '][\'ifs\'][\'' . $if_name . '\'] = (integer)$if_condition;';

        eval($eval_data);

        return true;
    }

    public function define_block_if($block_name, $if_name, $if_condition = false)
    {
        if (empty($block_name) || empty($if_name))
        {
            trigger_error(__METHOD__ . ' can\'t define If without block_name and without if_name', E_USER_WARNING);

            return false;
        }
        else
        {
            $if_path_array = explode('.', $block_name);
            $block_name = array_shift($if_path_array);

            return $this->define_block_if_raw($block_name, $if_path_array, $if_name, $if_condition);
        }
    }

    public function define_block_switch($block_name, $switch_name, $switch_condition = false)
    {
    }

    public function define_block_switch_case($block_name, $switch_name, $case_name, $case_condition)
    {
    }

    /*
        Не надо забывать, что блоки превращаются в foreach, в котором всё относительно
    */
    private static function multi_block_callback($matches)
    {
        $matches_first_elem = array_shift($matches);

        foreach($matches as $match_key => $match_value)
        {
            $matches[$match_key] = rtrim($match_value, '.');
        }

        return '<?php if ( isset($' . $matches[count($matches)-2] . '[\'children\'][\'' . $matches[count($matches)-1] . '\']) ) foreach ($' . $matches[count($matches)-2] . '[\'children\'][\'' . $matches[count($matches)-1] . '\']->{\'element_data\'} as $' . $matches[count($matches)-1] . '_key => $' . $matches[count($matches)-1] . ') { ?>';
    }

    /*
        Не надо забывать, что блочный if всегда внутри foreach, в котором всё относительно
    */
    private static function multi_block_if_callback($matches)
    {
        $matches_first_elem = array_shift($matches);

        foreach($matches as $match_key => $match_value)
        {
            $matches[$match_key] = rtrim($match_value, '.');
        }

        return '<?php if (isset($' . $matches[count($matches)-2] . '[\'ifs\'][\'' . $matches[count($matches) - 1] . '\']) && $' . $matches[count($matches)-2] . '[\'ifs\'][\'' . $matches[count($matches) - 1] . '\'] == true) { ?>';
    }

    private static function multi_block_vars_callback($matches)
    {
        $matches_first_elem = array_shift($matches);

        foreach($matches as $match_key => $match_value)
        {
            $matches[$match_key] = rtrim($match_value, '.');
        }

        return '<?php echo $' . $matches[count($matches)-2] . '[\'vars\'][\'' . $matches[count($matches)-1] . '\']; ?>';
    }

    /*
     *	routines
     */
    public function compile($template_content)
    {
        $template_content = preg_replace('#\{([a-zA-Z0-9\-_]+)\}#is', '<?php if (isset($this->template_global_data[\'vars\'][\'\1\'])) echo $this->template_global_data[\'vars\'][\'\1\']; ?>', $template_content);
        $template_content = preg_replace('#<!-- IF ([a-zA-Z0-9\-_]+) -->#is', '<?php if (isset($this->template_global_data[\'ifs\'][\'\1\']) && $this->template_global_data[\'ifs\'][\'\1\']) { ?>', $template_content);

        /*
        $template_content = preg_replace('#<!-- SWITCH ([a-z0-9\-_]+) -->#is', '<?php if ( isset($this->template_global_data[\'switches\'][\'\1\']) ) switch ($this->template_global_data[\'switches\'][\'\1\'][\'value\']): ?>', $template_content);
        $template_content = preg_replace('#<!-- CASE ([a-z0-9\-_]+)\.([a-zA-Z0-9\-_]+) -->#is', '<?php case \'\2\': ?>', $template_content);
        */

        /*
         * start single block tags
         */
        $template_content = preg_replace('#<!-- BLOCK ([a-z0-9\-_]+) -->#is', '<?php if ( isset($this->template_global_data[\'blocks\'][\'\1\']) ) foreach ($this->template_global_data[\'blocks\'][\'\1\']->{\'element_data\'} as \$\1_key => \$\1) { ?>', $template_content);
        $template_content = preg_replace('#<!-- IF ([a-z0-9\-_]+).([a-z0-9\-_]+) -->#is', '<?php if (isset($\1[\'ifs\'][\'\2\']) && $\1[\'ifs\'][\'\2\']) { ?>', $template_content);
        $template_content = preg_replace('#\{([a-z0-9\-_]+).([a-z0-9\-_]+)\}#is', '<?php echo \$\1[\'vars\'][\'\2\']; ?>', $template_content);

        /*
         * start multi blocks tags
         */
        $template_content = preg_replace_callback('#<!-- BLOCK ([a-z0-9\-_]+\.)+([a-z0-9\-_]+) -->#is', '\Engine\Template\Engine::multi_block_callback', $template_content);
        $template_content = preg_replace_callback('#<!-- IF ([a-z0-9\-_]+\.)+([a-z0-9\-_]+) -->#is', '\Engine\Template\Engine::multi_block_if_callback', $template_content);
        $template_content = preg_replace_callback('#\{([a-z0-9\-_]+\.)+([a-zA-Z0-9\-_]+)\}#is', '\Engine\Template\Engine::multi_block_vars_callback', $template_content);

        /*
         * close and middle tags
         */
        $template_content = str_replace(array(
            '<!-- ENDBLOCK -->',
            '<!-- ENDIF -->'
        ), '<?php } ?>', $template_content);
        /*
        $template_content = str_replace('<!-- ENDCASE -->', '<?php break; ?>', $template_content);
        $template_content = str_replace('<!-- ENDSWITCH -->', '<?php endswitch; ?>', $template_content);
        */
        $template_content = str_replace('<!-- ELSE -->', '<?php } else { ?>', $template_content);

        $template_content = preg_replace('#<!-- INCLUDE ([a-z0-9\.\-_]+) -->#is', '<?php $this->parse(\'\1\'); ?>', $template_content);
        /*
         * make code most readable
         */
        $template_content = str_replace(PHP_EOL, PHP_EOL . PHP_EOL, $template_content);

        return $template_content;
    }

    public function parse($template_name, $destination_mark = 0)
    {
        if (empty($template_name))
        {
            trigger_error(__METHOD__ . ' -> parse: can\'t parse empty filename', E_USER_WARNING);

            return false;
        }
        else
        {
            // condition for compile or recompile template
            if (
                !is_file($this->dir_prefix . $this->templates_directory . '/' . self::COMPILED_DIRECTORY . '/' . $template_name)
                ||
                !filesize($this->dir_prefix . $this->templates_directory . '/' . self::COMPILED_DIRECTORY . '/' . $template_name)
                ||
                (
                    filemtime($this->dir_prefix . $this->templates_directory . '/' . $template_name) > filemtime($this->dir_prefix . $this->templates_directory . '/Compiled/' . $template_name)
                )
            )
            {
                if (
                    is_readable($this->dir_prefix . $this->templates_directory . '/' . $template_name)
                    &&
                    $template_content = file_get_contents($this->dir_prefix . $this->templates_directory . '/' . $template_name)
                )
                {
                    if (file_put_contents($this->dir_prefix . $this->templates_directory . '/' . self::COMPILED_DIRECTORY . '/' . $template_name, $this->compile($template_content)) === FALSE)
                    {
                        trigger_error(__METHOD__ . ' -> parse: can\'t create ' . self::COMPILED_DIRECTORY . ' template file "' . $template_name . '" in "' . $this->dir_prefix . $this->templates_directory . '/' . self::COMPILED_DIRECTORY . '/' . $template_name . '"', E_USER_WARNING);

                        return false;
                    }
                    else
                    {
                        /*
                         * do nothing that the function execute correctly
                         */
                        // return true;
                    }
                }
                else
                {
                    trigger_error(__METHOD__ . ' -> parse: can\'t load template file "' . $template_name . '" for parsing', E_USER_WARNING);

                    return false;
                }
            }

            // let out compile template
            if (empty($destination_mark))
            {
                include($this->dir_prefix . $this->templates_directory . '/' . self::COMPILED_DIRECTORY . '/' . $template_name);

                return true;
            }
            else
            {
                ob_start();
                include($this->dir_prefix . $this->templates_directory . '/' . self::COMPILED_DIRECTORY . '/' . $template_name);
                $template_content = ob_get_contents();
                ob_end_clean();

                return $template_content;
            }
        }
    }

    public function refresh_global_vars($_config)
    {
        if (!empty($_config['template_global_data'])) {
            // \AutoConf::configure_array($this->template_global_data, $_config['template_global_data']);

            foreach($this->template_global_data as $key => $value) {
                if (!empty($_config['template_global_data'][$key])) {
                    $this->template_global_data[$key] = array_merge($this->template_global_data[$key], $_config['template_global_data'][$key]);
                }
            }

            /*
            echo '<pre>' . var_export($_config['template_global_data'], true) . '</pre>' . PHP_EOL;
            echo '<pre>' . var_export($this->template_global_data, true) . '</pre>' . PHP_EOL;
            die('Die in "' . __FILE__ . '" on line ' . __LINE__ . PHP_EOL);
            */
        }

        return true;
    }

    public function export()
    {
        return $this->template_global_data;
    }

    public function __destruct()
    {
        unset($this->template_content);
        unset($this->templates_directory);
        unset($this->template_global_data);
    }

    public function __toString()
    {
        return var_export($this, true);
    }

    public function Dump()
    {
        return var_export($this, true);
    }
}

?>