<?php

function decimal_br($p_decimalUS, $p_precision = 0)
{

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


    function datetime_br($date_us = null)
    {
      if (empty($date_us)) {
        $date_us = date('d/m/Y H:i:s');
      }
      $date = date_create_from_format('d/m/Y H:i:s', $date_us);

      return date_format($date, 'Y-m-d H:i:s');
    }

    function datetime_us($date_br = null)
    {
      if (empty($date_br)) {
        $date_br = date('d/m/Y H:i:s');
      }
      $date = date_create_from_format('d/m/Y H:i:s', $date_br);

      return date_format($date, 'Y-m-d H:i:s');
    }

    function date_br($date_us = null)
    {
      if (empty($date_us)) {
        $date_us = date('Y-m-d');
      }

      $date = date_create_from_format('Y-m-d', $date_us);

      return date_format($date, 'd/m/Y');
    }


    function date_us($date_br = null)
    {
      if (empty($date_br)) {
        $date_br = date('d/m/Y');
      }

      $date = date_create_from_format('d/m/Y', $date_br);

      return date_format($date, 'Y-m-d');
    }


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


  function base64_url_encode($data)
  {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
  }


  function base64_url_decode($data)
  {
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
  }


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

  function remove_acents($texto)
  {

    $array1 = array("á", "à", "â", "ã", "ä", "é", "è", "ê", "ë", "í", "ì", "î", "ï", "ó", "ò", "ô", "õ", "ö", "ú", "ù", "û", "ü", "ç"
      , "Á", "À", "Â", "Ã", "Ä", "É", "È", "Ê", "Ë", "Í", "Ì", "Î", "Ï", "Ó", "Ò", "Ô", "Õ", "Ö", "Ú", "Ù", "Û", "Ü", "Ç");
    $array2 = array("a", "a", "a", "a", "a", "e", "e", "e", "e", "i", "i", "i", "i", "o", "o", "o", "o", "o", "u", "u", "u", "u", "c"
      , "A", "A", "A", "A", "A", "E", "E", "E", "E", "I", "I", "I", "I", "O", "O", "O", "O", "O", "U", "U", "U", "U", "C");
    return str_replace($array1, $array2, $texto);
    return $var;
  }

  function is_get()
  {
    return $_SERVER['REQUEST_METHOD'] == 'GET';
  }

  function is_post()
  {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
  }

  function is_ajax()
  {
    return (boolean) (isset($_SERVER['HTTP_X_REQUESTED_WITH']))&& ( $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
  }


  function number_only($p_numero_str)
  {
    return preg_replace("/[^0-9]/", "", $p_numero_str);
  }

  function segments_until($segment_number) 
  {
    $CI= &get_instance();
    $CI->uri->segment_array();
    return implode(array_slice($CI->uri->segment_array(),0,$segment_number),'/');
  }


  function form_has_error($field, $return = true)
  {
      $has = form_error($field);
      if (empty($has) === false) {
          return $return;
      }
      return false;
  }

