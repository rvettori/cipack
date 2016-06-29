<?php

namespace Helpers;

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 *  retorna atributo do array
 *
 */
if (!function_exists("get_attr")) {

    /**
     * Retorna o bloco de mensagem padrão
     * @param type $p_message - variavel que contem a mensage
     * @return type html
     */
    function get_attr($p_array, $p_key, $p_default = null)
    {
        if (!is_array($p_array)) {
            return $p_default;
        }
        if (!isset($p_array["$p_key"])) {
            return $p_default;
        }

        if (is_bool($p_array["$p_key"])) {
            //empty nao trata boolean
            return $p_array["$p_key"];
        }

        if (empty($p_array["$p_key"]) && $p_default) {
            return $p_default;
        }

        return $p_array["$p_key"];

    }

}

if (!function_exists("date_br")) {
    function date_br($date_us = null)
    {
        if (empty($date_us)) {
            $date_us = date('Y-m-d');
        }

        $date = date_create_from_format('Y-m-d', $date_us);

        return date_format($date, 'd/m/Y');
    }

}

if (!function_exists("date_us")) {
    function date_us($date_br = null)
    {
        if (empty($date_br)) {
            $date_br = date('d/m/Y');
        }

        $date = date_create_from_format('d/m/Y', $date_br);

        return date_format($date, 'Y-m-d');
    }

}

if (!function_exists("datetime_us")) {
    function datetime_us($date_br = null)
    {
        if (empty($date_br)) {
            $date_br = date('d/m/Y H:i:s');
        }
        $date = date_create_from_format('d/m/Y H:i:s', $date_br);

        return date_format($date, 'Y-m-d H:i:s');
    }

}

if (!function_exists("datetime_br")) {
    function datetime_br($date_us = null)
    {
        if (empty($date_us)) {
            $date_us = date('d/m/Y H:i:s');
        }
        $date = date_create_from_format('d/m/Y H:i:s', $date_us);

        return date_format($date, 'Y-m-d H:i:s');
    }

}

if (!function_exists("date_us_validate")) {
    function date_us_validate($p_date)
    {
        $data = explode("-", "$p_date");
        log_message('debug', 'date_us_validate: ' . implode(', ', $data));
        $d = ($data[2]);
        $m = ($data[1]);
        $y = ($data[0]);

        // verifica se a data é válida!
        // 1 = true (válida)
        // 0 = false (inválida)
        $res = checkdate($m, $d, $y);
        if ($res == 1) {
            return true;
        } else {
            $CI = &get_instance();
            @$CI->form_validation->set_message('date_us_validate', 'O campo [%s] contém uma data inválida');
            return false;
        }
    }
}

if (!function_exists("date_br_validate")) {
    function date_br_validate($p_date)
    {
        $data = explode("/", "$p_date");
        $d    = $data[0];
        $m    = $data[1];
        $y    = $data[2];

        // verifica se a data é válida!
        // 1 = true (válida)
        // 0 = false (inválida)
        $res = checkdate($m, $d, $y);
        if ($res == 1) {
            return true;
        } else {
            $CI = &get_instance();
            @$CI->form_validation->set_message('date_br_validate', 'O campo [%s] contém uma data inválida.');
            return false;
        }
    }
}

if (!function_exists("date_only")) {

    /**
     * Retorna a Hora de um DateTime
     * @param type $p_date_time
     * @param type $p_precision_HMS. H=00, M= 00:00, S= 00:00:00
     * @return type string
     */
    function date_only($p_date_time)
    {
        $return = $p_date_time;
        if (strlen($p_date_time) > 10) {
            $return = substr($p_date_time, 0, 10);
        }
        return $return;
    }

}

if (!function_exists("time_only")) {

    /**
     * Retorna a Hora de um DateTime
     * @param type $p_date_time
     * @param type $p_precision_HMS. H=00, M= 00:00, S= 00:00:00
     * @return type string
     */
    function time_only($p_date_time, $p_precision_HMS = 'S')
    {
        if (strlen($p_date_time) < 19) {
            $p_date_time = dateNowUs() . ' 00:00:00';
        }
        $hor = str_pad(substr($p_date_time, 11, 2), 2, '0', STR_PAD_LEFT);
        $min = str_pad(substr($p_date_time, 14, 2), 2, '0', STR_PAD_LEFT);
        $seg = str_pad(substr($p_date_time, 17, 2), 2, '0', STR_PAD_LEFT);
        if (!is_int((int) $hor) || !is_int((int) $min) || !is_int((int) $seg)) {
            return null;
        }

        $return = '';
        if (strtoupper($p_precision_HMS) == 'H') {
            $return = $hor;
        } else if (strtoupper($p_precision_HMS) == 'M') {
            $return = $hor . ':' . $min;
        } else if (strtoupper($p_precision_HMS) == 'S') {
            $return = $hor . ':' . $min . ':' . $seg;
        } else {
            $return = '00:00:00';
        }

        return $return;
    }

}

if (!function_exists("decimal_us")) {
    /**
     *   Converte string decimal BR para US
     */
    function decimal_us($p_decimalBR)
    {
        if ($p_decimalBR) {
            $vlr = str_replace('.', '', $p_decimalBR);
            $vlr = str_replace(',', '.', $vlr);
            return $vlr;
        } else {
            return null;
        }
    }

}

if (!function_exists("decimal_br")) {

    /**
     * @param type $p_decimalUS NUMERO DO NORMATO US
     * @param type $p_precision NUMERO DE CASA DECIMAIS. SE FOR ZERO, NÃO SE ALTERA
     * @return type
     */
    function decimal_br($p_decimalUS, $p_precision = 0)
    {
        /**
         *   Converte String decimal US para BR
         */
        $p_decimalUS = floatval($p_decimalUS);
        if ($p_precision > 0) {
            return number_format($p_decimalUS, $p_precision, ',', '.');
        } else {
            $vlr = str_replace(',', '', $p_decimalUS);
            $vlr = str_replace('.', ',', $vlr);
            return $vlr;
        }
        return null;
    }

}

if (!function_exists("xml_encode")) {

    /**
     *  CONVERTE UM ARRAY EM XML
     * @param type $mixed
     * @param type $domElement
     * @param DOMDocument $DOMDocument
     * @return type
     */
    function xml_encode($mixed, $domElement = null, $DOMDocument = null)
    {
        if (is_null($DOMDocument)) {
            $DOMDocument               = new DOMDocument('1.0', 'UTF-8');
            $DOMDocument->formatOutput = true;
            xml_encode($mixed, $DOMDocument, $DOMDocument);
            return $DOMDocument->saveXML();
        } else {
            if (is_array($mixed)) {
                foreach ($mixed as $index => $mixedElement) {
                    if (is_int($index)) {
                        if ($index == 0) {
                            $node = $domElement;
                        } else {
                            $node = $DOMDocument->createElement($domElement->tagName);
                            $domElement->parentNode->appendChild($node);
                        }
                    } else {
                        $plural = $DOMDocument->createElement($index);
                        $domElement->appendChild($plural);
                        $node = $plural;
                        if (rtrim($index, 's') !== $index && count($mixedElement) > 1) {

                            $singular = $DOMDocument->createElement(rtrim($index, 's'));
                            $plural->appendChild($singular);
                            $node = $singular;
                        }
                    }
                    xml_encode($mixedElement, $node, $DOMDocument);
                }
            } else {
                $domElement->appendChild($DOMDocument->createTextNode($mixed));
            }
        }
    }

}

if (!function_exists("curl_post_async_config")) {

    /**
     * Função utilizada para configurar o script quando executado de
     * forma assincrona pelo curl_post_async
     * @param type $time_limit
     */
    function curl_post_async_config($time_limit = 0)
    {
        ignore_user_abort(true);
        set_time_limit($time_limit);
    }

}

if (!function_exists("curl_post_async")) {

    /**
     * Chama script assicrono. Utilizar função [ignore_user_abort(TRUE); set_time_limit(0);] no script ou metodo chamado.
     * @param type $url
     * @param type $params
     */
    function curl_post_async($url, $params = array())
    {

        $post_params = array();
        foreach ($params as $key => &$val) {
            if (is_array($val)) {
                $val = implode(',', $val);
            }

            $post_params[] = $key . '=' . urlencode($val);
        }
        $post_string = implode('&', $post_params);
        $parts       = parse_url($url);
        $fp          = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 120);
        $out         = "POST " . $parts['path'] . " HTTP/1.1\r\n";
        $out .= "Host: " . $parts['host'] . "\r\n";
        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out .= "Content-Length: " . strlen($post_string) . "\r\n";
        $out .= "Connection: Close\r\n\r\n";
        if (isset($post_string)) {
            $out .= $post_string;
        }

        fwrite($fp, $out);
        fclose($fp);
    }

}

if (!function_exists('link_popup')) {

    function link_popup($p_name, $p_uri, $p_params = false, $p_paramsget = false, $p_atts = array())
    {
        $atts = array(
            'width'      => '800',
            'height'     => '600',
            'scrollbars' => 'yes',
            'status'     => 'no',
            'resizable'  => 'yes',
            'screenx'    => '0',
            'screeny'    => '0',
        );
        # ATRIBUTOS DO PUPUP
        $atts = (empty($p_atts)) ? $atts : $p_atts;
        # PARAMETROS PARA TEMPLATE POPUP
        if (is_array($p_params)) {
            $p_params['tpl'] = 'popup';
        } elseif (is_string($p_params)) {
            $p_paramsget['tpl'] = 'popup';
        } elseif (!$p_params) {
            $p_params['tpl'] = 'popup';
        } else {
            $p_paramsget['tpl'] = 'popup';
        }

        return anchor_popup(link_url($p_uri, $p_params, $p_paramsget), $p_name, $atts);
    }
}

if (!function_exists('number_only')) {

    function number_only($p_numero_str)
    {
        return preg_replace("/[^0-9]/", "", $p_numero_str);
    }
}

if (!function_exists('base64_url_encode')) {

    function base64_url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

if (!function_exists('base64_url_decode')) {

    function base64_url_decode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}

if (!function_exists('array_find')) {
    //mudar para array_find
    /**
     * PESQUISA UMA COLUNA EM UMA LISTA DE ARRAYS(OBJETOS)
     * EX: array_find(array('rowid' => $rowid_cart), $cart_contents)
     * @param type $needle
     * @param type $haystack
     * @return type key index da coluna
     */
    function array_find($needle, $haystack)
    {
        if (empty($needle) || empty($haystack)) {
            return false;
        }

        foreach ($haystack as $key => $value) {
            $exists = 0;
            foreach ($needle as $nkey => $nvalue) {
                if (!empty($value[$nkey]) && $value[$nkey] == $nvalue) {
                    $exists = 1;
                } else {
                    $exists = 0;
                }
            }
            if ($exists) {
                return $key;
            }

        }

        return false;
    }
}

if (!function_exists('is_get')) {
    function is_get()
    {
        return $_SERVER['REQUEST_METHOD'] == 'GET';
    }
}

// ------------------------------------------------------------------------

if (!function_exists('is_post')) {
    function is_post()
    {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }
}

// ------------------------------------------------------------------------

if (!function_exists('is_ajax')) {
    function is_ajax()
    {
        $CI = &get_instance();
        return $CI->input->is_ajax_request();
        //return (boolean) (isset($_SERVER['HTTP_X_REQUESTED_WITH']))&& ( $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
    }
}

/*
modules
 *
if ( ! function_exists('list_modules_array') ) {

function list_modules_array($excludes=array())
{
$CI =& get_instance();
$locations = $CI->config->item('modules_locations');
$CI->load->helper('directory');
# flip para inverter key=>value por causa do exclude
$modules = array_flip(directory_map($locations[0],1));
foreach ($excludes as $value) {
unset($modules["$value"]);
}
unset($modules["admin_scaffold"]);
return  array_flip($modules);
}
}

if ( ! function_exists('list_modules_class_array') ) {

function list_modules_class_array($p_module='')
{
$CI =& get_instance();
$locations = $CI->config->item('modules_locations');
$location = $locations[0];
//$location = (empty($p_module)) ? $location : $location.$p_module."/";
log_message('debug',"LOCATION: $locations");
$CI->load->helper('directory');
$modules = array_flip(list_modules_array()); // array_flip(directory_map($location,1));
foreach ($modules as $key => $value) {
$path_controller =  $location . $key . '/controllers/';
$modules["$key"] = directory_map($path_controller,1);
#RETIRA O .PHP
foreach ($modules["$key"] as $idx=>$cls) {
$modules["$key"][$idx] = str_replace(".php", "", $cls);
}
}
if ((!empty($p_module)) && (isset($modules["$p_module"]))) {
return $modules["$p_module"];
}
return $modules;
}
}
 */

/**
 *   Converte um array em um http query string, ex: a=1&b=2&c=3
 *
 **/
if (!function_exists('urlparam_encode')) {
    function urlparam_encode($p_array)
    {
        $params = "";
        if (!is_array($p_array)) {
            die('PARAMS IS NOT ARRAY');
        }
        $paramsJoined = array();
        foreach ($p_array as $param => $value) {
            $paramsJoined[] = "$param=$value";
        }
        $params = implode('&', $paramsJoined);
        return $params;
    }
}

if (!function_exists('urlparam_decode')) {
    function urlparam_decode($p_http_query_string)
    {
        $params = explode("&", $p_http_query_string);
        $ret    = array();
        foreach ($params as $value) {
            $it            = explode("=", $value);
            $ret["$it[0]"] = $it[1];
        }
        return $ret;
    }
}

if (!function_exists("remove_acents")) {
    function remove_acents($texto)
    {

        $array1 = array("á", "à", "â", "ã", "ä", "é", "è", "ê", "ë", "í", "ì", "î", "ï", "ó", "ò", "ô", "õ", "ö", "ú", "ù", "û", "ü", "ç"
            , "Á", "À", "Â", "Ã", "Ä", "É", "È", "Ê", "Ë", "Í", "Ì", "Î", "Ï", "Ó", "Ò", "Ô", "Õ", "Ö", "Ú", "Ù", "Û", "Ü", "Ç");
        $array2 = array("a", "a", "a", "a", "a", "e", "e", "e", "e", "i", "i", "i", "i", "o", "o", "o", "o", "o", "u", "u", "u", "u", "c"
            , "A", "A", "A", "A", "A", "E", "E", "E", "E", "I", "I", "I", "I", "O", "O", "O", "O", "O", "U", "U", "U", "U", "C");
        return str_replace($array1, $array2, $texto);
        return $var;
    }
}

if (!function_exists("validation_errors_to_array")) {
    /**
     *  Must $this->form_validation->run() == FALSE
     */
    function validation_errors_to_array($validation_rules)
    {
        $CI = &get_instance();
        $CI->form_validation->set_rules($validation_rules);
        $errors_array = array();
        foreach ($validation_rules as $row) {
            $field = $row['field'];
            $error = form_error($field);
            if ($error) {
                $errors_array[$field] = $error;
            }
        }
        return $errors_array;
    }
}

if (!function_exists("form_has_error")) {
    /**
     *  Must $this->form_validation->run() == FALSE
     */
    function form_has_error($field, $return = true)
    {
        $has = form_error($field);
        if (empty($has) === false) {
            return $return;
        }
        return false;
    }
}

if (!function_exists("segments_until")) {
  function segments_until($segment_number) {
    $CI= &get_instance();
    $CI->uri->segment_array();
    return implode(array_slice($CI->uri->segment_array(),0,$segment_number),'/');
  }
}
