<?php

namespace CIPack;

defined('BASEPATH') or exit('No direct script access allowed');

class Signer
{
  protected $ci;

  private $settings;

  public function __construct()
  {
    $this->ci = &get_instance();
    $this->ci->load->database();
    $this->ci->config->load('signer', true, true);

    // Table Definitions
    $config['table']                  = 'users';
    $config['user_id']                = 'id';
    $config['login']                  = 'email';
    $config['password']               = 'password';
    $config['reset_password_token']   = 'reset_password_token';
    $config['reset_password_sent_at'] = 'reset_password_sent_at';
    $config['login_count']            = 'login_count';
    $config['current_login_at']       = 'current_login_at';
    $config['last_login_at']          = 'last_login_at';
    $config['last_ip']                = 'last_ip';
    $config['created_at']             = 'created_at';
    $config['updated_at']             = 'updated_at';
    $config['active']                 = 'active';

    log_message('debug', json_encode($this->ci->config->item('signer')));

    $this->settings = array_merge($config, ($cfg = $this->ci->config->item('signer') ?: []));
  }

  public function login($login, $password)
  {
    $where                           = array();
    $where[$this->settings['login']] = $login;
    $where[$this->settings['password']] = md5($password);

    $record = $this->ci->db->get_where($this->settings['table'], $where, 1)->row_array();
    if (empty($record)) {
      return false;
    } else {
      $update[$this->settings['login_count']]   = $record[$this->settings['login_count']] + 1;
      $update[$this->settings['last_login_at']] = date('Y/m/d H:i:s');
      $update[$this->settings['updated_at']]    = date('Y/m/d H:i:s');
      $update[$this->settings['last_ip']]       = $this->ci->input->ip_address();

      $this->ci->db->where($this->settings['user_id'], $record[$this->settings['user_id']]);
      $this->ci->db->update($this->settings['table'], $update);
      $this->ci->session->set_userdata(array('user_id' => $record[$this->settings['user_id']]));
      return true;
    }
  }

  public function logout()
  {
    $this->ci->session->set_userdata(array('user_id' => ''));
    $this->ci->session->sess_destroy();
  }

  public function register($login = '', $password = '', $password_confirmation = '')
  {
    $this->ci->db->where($this->settings['login'], $login);
    $count = $this->ci->db->count_all_results($this->settings['table']);

    if ((strlen($login) > 0) and ($count == 0) and (strlen($password) > 0) and ($password == $password_confirmation)) {
      $insert                                = array();
      $insert[$this->settings['login']]      = $login;
      $insert[$this->settings['password']]   = $password;
      $insert[$this->settings['created_at']] = date('d/m/Y H:i:s');
      $insert[$this->settings['last_ip']]    = $this->ci->input->ip_address();
      return $this->ci->db->insert($this->settings['table'], $insert);
    }
    return false;
  }

  public function get_user_id()
  {
    return $this->ci->session->userdata('user_id');
  }

  public function get_user()
  {
    return $this->ci->db->get_where($this->settings['table'],
      ["{$this->settings['user_id']}" => $this->ci->session->userdata('user_id')],
      1)->row_array();
  }

  public function is_logged_in()
  {
        # verificar session;
        # verificar tabela current_sign se corresponde com o cookie
    return $this->ci->session->userdata('user_id') > 0;
  }

    /**
     * Send reset to email
     */
    public function reset_password($login)
    {
      # Get user by login
      log_message('debug','reset_password: ' . $login );
      $user = $this->ci->db->get_where($this->settings['table'],
              ["{$this->settings['login']}" => $login], 1)->row_array();

      log_message('debug', json_encode($user));
      if (empty($user)) {
        return false;
      }
      
      # Generate reset_password_token
      $reset_password_sent_at = date('Y-m-d H:i:s', time());
      $reset_password_token   = md5($reset_password_sent_at);

      $this->ci->db->where($this->settings['user_id'], $user["{$this->settings['user_id']}"]);
      $sql = $this->ci->db->update($this->settings['table'], [
        $this->settings['reset_password_token']   =>md5($reset_password_sent_at), 
        $this->settings['reset_password_sent_at'] => $reset_password_sent_at
      ]);

      log_message('debug','sql reset_password: ' . $sql );
      # Retrun reset to Send mail to user
      return $reset_password_token;
    }

    public function valid_reset($token)
    {
      $this->ci->db->where($this->settings['reset_password_token'], $token);
      $this->ci->db->where("{$this->settings['reset_password_token']} is not null");
      $user = $this->ci->db->get($this->settings['table'])->row_array();
      log_message('debug', 'valid_reset: '. json_encode($user));
      
      if ($user) {
        return $user;
      }

      return '';
      
    }

    /**
     * Validate and apply changes
     */
    public function change_password($login, $old_password, $password, $password_confirmation)
    {
        # Validate Login
      $where                              = array();
      $where[$this->settings['login']]    = $login;
      $where[$this->settings['password']] = md5($old_password);
      $record                             = $this->ci->db->get_where($this->settings['table'], $where, 1)->row_array();

      if (empty($record)) {
        return false;
      }

        # Validate new passwrod
      if ($password == $password_confirmation and strlen($passwrod) > 0) {
            # update in db
        $update[$this->settings['updated_at']] = date('d/m/Y H:i:s');
        $update[$this->settings['last_ip']]    = $this->ci->input->ip_address();

        $this->ci->db->where($this->settings['user_id'], $record[$this->settings['user_id']]);
        $this->ci->db->update($this->settings['table'], $update);
        return true;
      } else {
        return false;
      }
    }

  }

  /* End of file Auth.php */
  /* Location: ./application/libraries/Auth.php */
