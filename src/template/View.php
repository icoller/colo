<?php
namespace colo\template;

/**
 * The View class represents output to be displayed. It provides
 * methods for managing view data and inserts the data into
 * view templates upon rendering.
 */
class View {
    /**
     * Location of view templates.
     *
     * @var string
     */
    public $path;

    /**
     * File extension.
     *
     * @var string
     */
    public $extension = '.php';

    /**
     * View variables.
     *
     * @var array
     */
    protected $vars = array();

    /**
     * Template file.
     *
     * @var string
     */
    private $template;

    private $replacecode = ['search' => [], 'replace' => []];


    /**
     * Constructor.
     *
     * @param string $path Path to templates directory
     */
    public function __construct($path = '.') {
        $this->path = $path;
    }


    public function parse_template($template) {

        if(empty($template)){
            return false;
        }

        //过滤PHP代码
        $var_regexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\-\>)?[a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)";
        $const_regexp = "([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)";

        $template = preg_replace("/([\n\r]+)\t+/s", "\\1", $template);

        // 将注释全部替换掉
        $template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template);

        // 时间
        $template = preg_replace_callback("/[\n\r\t]*\{date\((.+?)\)\}[\n\r\t]*/i", array($this, 'parse_template_callback_datetags_1'), $template);

        // 头像
        $template = preg_replace_callback("/[\n\r\t]*\{avatar\((.+?)\)\}[\n\r\t]*/i", array($this, 'parse_template_callback_avatartags_1'), $template);

        //执行php代码
        $template = preg_replace_callback("/[\n\r\t]*\{eval\}\s*(\<\!\-\-)*(.+?)(\-\-\>)*\s*\{\/eval\}[\n\r\t]*/is", array($this, 'parse_template_callback_evaltags_2'), $template);
        $template = preg_replace_callback("/[\n\r\t]*\{eval\s+(.+?)\s*\}[\n\r\t]*/is", array($this, 'parse_template_callback_evaltags_1'), $template);

        // 输出php
        $template = preg_replace("/\{(\\\$[a-zA-Z0-9_\-\>\[\]\'\"\$\.\x7f-\xff]+)\}/s", "<?=\\1?>", $template);
        $template = preg_replace_callback("/$var_regexp/s", array($this, 'parse_template_callback_addquote_1'), $template);
        $template = preg_replace_callback("/\<\?\=\<\?\=$var_regexp\?\>\?\>/s", array($this, 'parse_template_callback_addquote_1'), $template);

        // echo 输出;
        $template = preg_replace_callback("/[\n\r\t]*\{echo\s+(.+?)\}[\n\r\t]*/is", array($this, 'parse_template_callback_stripvtags_echo1'), $template);

        // if else判断
        $template = preg_replace_callback("/([\n\r\t]*)\{if\s+(.+?)\}([\n\r\t]*)/is", array($this, 'parse_template_callback_stripvtags_if123'), $template);
        $template = preg_replace_callback("/([\n\r\t]*)\{elseif\s+(.+?)\}([\n\r\t]*)/is", array($this, 'parse_template_callback_stripvtags_elseif123'), $template);
        $template = preg_replace("/\{else\}/i", "<? } else { ?>", $template);
        $template = preg_replace("/\{\/if\}/i", "<? } ?>", $template);

        // loop 循环
        $template = preg_replace_callback("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\}[\n\r\t]*/is", array($this, 'parse_template_callback_stripvtags_loop12'), $template);
        $template = preg_replace_callback("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}[\n\r\t]*/is", array($this, 'parse_template_callback_stripvtags_loop123'), $template);
        $template = preg_replace("/\{\/loop\}/i", "<? } ?>", $template);

        $template = preg_replace("/\{$const_regexp\}/s", "<?=\\1?>", $template);

        // 替换占位符
        if(!empty($this->replacecode)) {
            $template = str_replace($this->replacecode['search'], $this->replacecode['replace'], $template);
        }
        
        $template = preg_replace("/ \?\>[\n\r]*\<\? /s", " ", $template);
        $template = preg_replace("/\<\?(\s{1})/is", "<?php\\1", $template);
        $template = preg_replace("/\<\?\=(.+?)\?\>/is", "<?php echo \\1;?>", $template);

        return $template;
    }

    public function parse_template_callback_datetags_1($matches) {
        return $this->datetags($matches[1]);
    }

    public function parse_template_callback_avatartags_1($matches) {
        return $this->avatartags($matches[1]);
    }

    public function parse_template_callback_evaltags_2($matches) {
        return $this->evaltags($matches[2]);
    }

    public function parse_template_callback_evaltags_1($matches) {
        return $this->evaltags($matches[1]);
    }

    public function parse_template_callback_addquote_1($matches) {
        return $this->addquote('<?='.$matches[1].'?>');
    }

    public function parse_template_callback_stripvtags_echo1($matches) {
        return $this->stripvtags('<? echo '.$matches[1].'; ?>');
    }

    public function parse_template_callback_stripvtags_if123($matches) {
        return $this->stripvtags($matches[1].'<? if('.$matches[2].') { ?>'.$matches[3]);
    }

    public function parse_template_callback_stripvtags_elseif123($matches) {
        return $this->stripvtags($matches[1].'<? } elseif('.$matches[2].') { ?>'.$matches[3]);
    }

    public function parse_template_callback_stripvtags_loop12($matches) {
        return $this->stripvtags('<? if(is_array('.$matches[1].')) foreach('.$matches[1].' as '.$matches[2].') { ?>');
    }

    public function parse_template_callback_stripvtags_loop123($matches) {
        return $this->stripvtags('<? if(is_array('.$matches[1].')) foreach('.$matches[1].' as '.$matches[2].' => '.$matches[3].') { ?>');
    }

    // 载入模板
    public function parse_template_callback_getinto($matches) {
        return $this->getfile($this->getTemplate($matches[1]));
    }

    public function datetags($parameter) {
        $parameter = stripslashes($parameter);
        $i = count($this->replacecode['search']);
        $this->replacecode['search'][$i] = $search = "<!--DATE_TAG_$i-->";
        $this->replacecode['replace'][$i] = "<?php echo dgmdate($parameter);?>";
        return $search;
    }

    public function avatartags($parameter) {
        $parameter = stripslashes($parameter);
        $i = count($this->replacecode['search']);
        $this->replacecode['search'][$i] = $search = "<!--AVATAR_TAG_$i-->";
        $this->replacecode['replace'][$i] = "<?php echo avatar($parameter);?>";
        return $search;
    }

    public function evaltags($php) {
        $i = count($this->replacecode['search']);
        $this->replacecode['search'][$i] = $search = "<!--EVAL_TAG_$i-->";
        $this->replacecode['replace'][$i] = "<? $php?>";
        return $search;
    }

    public function addquote($var) {
        return str_replace("\\\"", "\"", preg_replace("/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", $var));
    }

    public function stripvtags($expr, $statement = '') {
        $expr = str_replace('\\\"', '\"', preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", $expr));
        $statement = str_replace('\\\"', '\"', $statement);
        return $expr.$statement;
    }

    /**
     * Gets a template variable.
     *
     * @param string $key Key
     * @return mixed Value
     */
    public function get($key) {
        return isset($this->vars[$key]) ? $this->vars[$key] : null;
    }

    /**
     * Sets a template variable.
     *
     * @param mixed $key Key
     * @param string $value Value
     */
    public function set($key, $value = null) {
        if (is_array($key) || is_object($key)) {
            foreach ($key as $k => $v) {
                $this->vars[$k] = $v;
            }
        }
        else {
            $this->vars[$key] = $value;
        }
    }

    /**
     * Checks if a template variable is set.
     *
     * @param string $key Key
     * @return boolean If key exists
     */
    public function has($key) {
        return isset($this->vars[$key]);
    }

    /**
     * Unsets a template variable. If no key is passed in, clear all variables.
     *
     * @param string $key Key
     */
    public function clear($key = null) {
        if (is_null($key)) {
            $this->vars = array();
        }
        else {
            unset($this->vars[$key]);
        }
    }

    public function getfile($tplfile){
        if(file_exists($tplfile)) {
            $template = '';
            $template = @file_get_contents($tplfile);
            if(empty($template)){
                return false;
            }
        }else{
            return false;
        }
        return $template;
    }

    /**
     * Renders a template.
     *
     * @param string $file Template file
     * @param array $data Template data
     * @throws \Exception If template not found
     */
    public function render($file, $data = null) {
        $this->template = $this->getTemplate($file);

        if (!file_exists($this->template)) {
            throw new \Exception("Template file not found: {$this->template}.");
        }

        if (is_array($data)) {
            $this->vars = array_merge($this->vars, $data);
        }

        extract($this->vars);

        $tpl = '';

        $tpl = $this->getfile($this->template);
        $tpl = preg_replace_callback("/[\n\r\t]*\<!--{template\s+([a-z0-9_:\/]+)\}[\n\r\t]*-->/is", array($this, 'parse_template_callback_getinto'), $tpl);
        $tpl = $this->parse_template($tpl);
        // 临时模板缓存目录
        $tmpPath = app()->get('cache.path').'/template/'.str_replace('/', '_', $this->template);

        if(!file_exists($tmpPath)){
            @file_put_contents($tmpPath, $tpl, LOCK_EX);
        }else{
            if ($this->isCached($tmpPath)) {
                @file_put_contents($tmpPath, $tpl, LOCK_EX);
            }
        }
        include $tmpPath ?? $this->template;
    }

    // 获取缓存时间
    public function isCached($path)
    {
        if (!file_exists($path)) {
            return false;
        }
        $cacheTime = app()->get('cache.views.time');
        if ($cacheTime <= 0) {
            return true;
        }
        if (time() - filemtime($path) < $cacheTime) {
            return false;
        }
        return true;
    }

    /**
     * Gets the output of a template.
     *
     * @param string $file Template file
     * @param array $data Template data
     * @return string Output of template
     */
    public function fetch($file, $data = null) {
        ob_start();

        $this->render($file, $data);
        $output = ob_get_clean();

        return $output;
    }

    /**
     * Checks if a template file exists.
     *
     * @param string $file Template file
     * @return bool Template file exists
     */
    public function exists($file) {
        return file_exists($this->getTemplate($file));
    }

    /**
     * Gets the full path to a template file.
     *
     * @param string $file Template file
     * @return string Template file location
     */
    public function getTemplate($file) {
        $ext = $this->extension;

        if (!empty($ext) && (substr($file, -1 * strlen($ext)) != $ext)) {
            $file .= $ext;
        }

        if ((substr($file, 0, 1) == '/')) {
            return $file;
        }
        
        return $this->path.'/'.$file;
    }

    /**
     * Displays escaped output.
     *
     * @param string $str String to escape
     * @return string Escaped string
     */
    public function e($str) {
        echo htmlentities($str);
    }
}