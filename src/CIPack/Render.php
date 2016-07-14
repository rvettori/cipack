<?php

namespace CIPack;

defined('BASEPATH') or exit('No direct script access allowed');

class Render
{

    protected $CI;
    protected $params;
    protected $layout = 'layouts/application';

    public function __construct($params = null)
    {
        $this->CI     = &get_instance();
        $this->params = $params;
    }

    /**
     * Base Layout to render views.
     * Only for view and html
     */
    public function set_layout($layout)
    {
        $this->layout = $layout;
    }

    /**
     * Render view file
     */
    public function view($view, $data = array(), $return = false)
    {
        if ($return === true) {
            return $this->CI->load->view($view, $data, true);
        }
        $this->html($this->CI->load->view($view, $data, true));
    }

    /**
     * Render html
     */
    public function html($html = '')
    {
        // without layout
        if (empty($this->layout)) {
            return $this->CI->output->set_output($html);
        }

        // with layout
        $template_data = array(
            'content' => $html,
        );

        $template = $this->CI->parser->parse($this->layout, $template_data);
        $this->CI->output->set_output($template);
    }

    /**
     * Render JSON
     */
    public function json($array)
    {
        $this->CI->output
            ->set_content_type('application/json')
            ->set_output(json_encode($array));
    }

    public function format($view, $data)
    {
        if ($this->CI->input->get('format') == 'json') {
            $this->json($data);
        } else {
            $this->view($view, $data);
        }

    }

}
