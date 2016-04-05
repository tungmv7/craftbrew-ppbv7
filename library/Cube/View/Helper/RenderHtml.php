<?php

/**
 *
 * Cube Framework $Id$ 8ZTO26QQp4N3pYH865/g6K40glow5lMA/MbDWFLWCgU=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.1
 */
/**
 * processes an input text and renders it as html
 * parses code that has the following format:
 * <%=action:{action}.{controller}.{module}%>
 * => Helper::Action(action, controller, module)
 * <%=url:{param-key},{param-value};{param-key},{param-value}...
 * => Helper::Url  - array of params
 * <%=href:{uri}%>
 * => Helper::Url  - string uri param
 */

namespace Cube\View\Helper;

class RenderHtml extends AbstractHelper
{

    /**
     *
     * output formatted string
     *
     * @param string $string
     * @param bool   $parseCode
     *
     * @return string
     */
    public function renderHtml($string, $parseCode = false)
    {
        $output = str_ireplace(
            array('&amp;', '&#039;', '&quot;', '&lt;', '&gt;', '&nbsp;'), array('&', "'", '"', '<', '>', ' '), $string);

        if ($parseCode) {
            if (preg_match_all('#<%=action:(.+)%>#', $output, $m)) {
                $params = array();
                foreach ($m[1] as $key => $matches) {
                    $array = explode('.', $matches);

                    $action = (isset($array[0])) ? $array[0] : null;
                    $controller = (isset($array[1])) ? $array[1] : null;
                    $module = (isset($array[2])) ? $array[2] : null;
                    $vars = (isset($array[3])) ? explode(';', $array[3]) : null;

                    foreach ((array)$vars as $var) {
                        list ($k, $v) = explode(',', $var);
                        if (!empty($k)) {
                            $params[$k] = $v;
                        }
                    }

                    $replace = $this->getView()->action($action, $controller, $module, $params);

                    $output = str_replace($m[0][$key], $replace, $output);
                }
            }

            // url helper
            if (preg_match_all('#<%=url:(.+)%>#', $output, $m)) {
                foreach ($m[1] as $key => $matches) {
                    $array = explode(';', $matches);

                    $params = array();
                    foreach ($array as $row) {
                        list($k, $v) = explode(',', $row);
                        if (!empty($v)) {
                            $params[$k] = $v;
                        }
                    }

                    $replace = $this->getView()->url($params);

                    $output = str_replace($m[0][$key], $replace, $output);
                }
            }

            // url helper with path string
            if (preg_match_all('#<%=href:([a-zA-Z0-9\/\-\_]+)%>#', $output, $m)) {
                foreach ($m[1] as $key => $href) {
                    $replace = $this->getView()->url($href);

                    $output = str_replace($m[0][$key], $replace, $output);
                }
            }
        }

        return $output;
    }

}

