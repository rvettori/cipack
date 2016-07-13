<?php defined('BASEPATH') or exit('No direct script access allowed');

class MY_Model extends CI_Model
{

    public $table       = null; # Table name
    public $primary_key = 'id'; # Primary key name
    public $sequence    = ''; # Sequence Name
    public $fillable    = array(); # Fields that can be used
    public $protected   = array(); # Fields that not be used

    private $table_fields = array();
    private $data         = array();

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('inflector');
        $this->load->database();
        $this->get_table();
    }

    public function insert($object)
    {
        $object = $this->prepare_data($object);

        unset($object[$this->primary_key]);
        $this->db->insert($this->get_table(), $object);
        return $this->find(@$this->db->insert_id($this->get_sequence()));
    }

    public function update($object)
    {
        $object = $this->prepare_data($object);
        $this->db->where($this->primary_key, $object[$this->primary_key]);
        $this->db->update($this->get_table(), $object);
        return $this->find($object[$this->primary_key]);
    }

    public function delete($id)
    {
        if (is_array($id)) {
            $this->db->where($id);
        } else {
            $this->db->where($this->primary_key, $id);
        }

        return @$this->db->delete($this->table) !== false;
    }

    public function save($object)
    {
        $record = $this->find($object[$this->primary_key]);

        if (empty($record)) {
            $result = $this->insert($object);
        } else {
            $object = array_merge($record, $object);
            $result = $this->update($object);
        }
        return $result;
    }

    public function find($id)
    {
        if (empty($id)) {
            return array();
        }

        if (is_array($id)) {
            $result = $this->db->where($id)->get($this->get_table())->row_array();
        } else {
            $result = $this->db->where($this->primary_key, $id)->get($this->get_table())->row_array();
        }

        return empty($result) ? array() : $result;
    }

    public function search($params, $search_columns = array())
    {

        $this->db->start_cache();
        // search in all columns by OR
        if (isset($params['search']) and !empty($params['search'])) {
            $words = explode(' ', $params['search']);

            $this->db->group_start();
            $search_columns = array_merge($this->get_table_fields(), $search_columns);
            foreach ($words as $word) {

                $word = $this->get_db_format_for($word);
                foreach ($search_columns as $col) {
                    $col = strpos($col, '.') > 0 ? $col : "{$this->table}.{$col}";
                    $this->db->or_like("upper({$col}::text)", strtoupper($word));
                }
            }
            $this->db->group_end();
        }

        // Filter especific column by END
        $predicates = ['gt' => '>', 'lt' => '<', 'eq' => '=', 'cont' => 'like', 'gte' => '>=', 'lte' => '<=', 'null' => 'IS NULL', 'present' => 'IS NOT NULL'];
        if (isset($params['q']) and !empty($params['q'])) {
            $query_columns = $params['q'];
            $this->db->group_start();
            $this->db->where(1, 1); # para não gerar bloco end vaziu
            foreach ($query_columns as $key_predicate => $value) {
                if (empty($value)) {
                    continue;
                }

                $value = $this->get_db_format_for($value);

                $parts  = explode('_', $key_predicate);
                $pred   = array_pop($parts);
                $column = implode('_', $parts);
                $column = strpos($column, '.') > 0 ? $column : "{$this->table}.{$column}";

                if (strpos($pred, 'cont') !== false) {
                    $this->db->like("upper({$column})", strtoupper($value));

                } elseif (strpos($pred, 'present') !== false) {
                    $this->db->where("{$column} IS NOT NULL");

                } elseif (strpos($pred, 'null') !== false) {
                    $this->db->where("{$column} IS NULL");

                } else {
                    $this->db->where("{$column} {$predicates[$pred]}", $value);
                }
            }
            $this->db->group_end();
        }

        $this->db->stop_cache();

        if (!empty($params['sort']) and !empty($params['order'])) {
            $this->db->order_by($params['sort'], $params['order']);
        }

        $query = $this->db->limit(get_attr($params, 'limit'), get_attr($params, 'offset'))->get($this->get_table());
        log_message('debug', $this->db->last_query());
        $data['rows'] = $query->result_array();

        $data['total'] = $this->db->count_all_results($this->get_table());
        $this->db->flush_cache();
        return $data;
    }

    public function dropdown($field, $include_blank = false)
    {
        $this->db->select("$this->primary_key, $field");
        $query  = $this->db->get($this->table)->result_array();
        $return = [];
        if ($include_blank === true) {
            $return[''] = '';
        }
        $return += array_column($query, $field, $this->primary_key);
        return $return;
    }

    private function fillable_fields()
    {
        $result        = array();
        $no_protection = array();
        foreach ($this->get_table_fields() as $field) {
            if (!in_array($field, $this->protected)) {
                $no_protection[] = $field;
            }
        }

        if (!empty($this->fillable)) {
            $can_fill = array();
            foreach ($this->fillable as $field) {
                if (in_array($field, $no_protection)) {
                    $can_fill[] = $field;
                }
            }
            $result = $can_fill;
        } else {
            $result = $no_protection;
        }
        return $result;
    }

    protected function get_db_format_for($value)
    {
        # BR date to US date if date
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value, $matches, PREG_OFFSET_CAPTURE) > 0) {
            $value = date_us($value);
        }

        # BR number if number
        if (is_numeric(decimal_us($value))) {
            $value = decimal_us($value);
        }

        return $value;
    }

    private function prepare_data($set)
    {
        if (!isset($set)) {
            return false;
        }
        log_message('debug', 'variable $set' . json_encode($set));

        $fillable = array();
        foreach ($this->fillable_fields() as $key) {
            $fillable[$key] = empty($set[$key]) ? null : $set[$key];
        }

        return $fillable;
    }

    private function get_table_fields()
    {
        if (empty($this->table_fields)) {
            $this->table_fields = $this->db->list_fields($this->get_table());
        }
        return $this->table_fields;
    }

    private function get_table()
    {
        if (empty($this->table)) {
            $model_name  = get_class($this);
            $this->table = plural(preg_replace('/(_m|_model)?$/', '', strtolower($model_name)));
        }

        return $this->table;
    }

    public function get_sequence()
    {
        if (empty($this->sequence)) {
            $this->sequence = $this->get_table() . '_' . $this->primary_key . '_seq';
        }
        return $this->sequence;
    }

}
