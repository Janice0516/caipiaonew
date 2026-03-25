<?php
defined('IN_MYWEB') or exit('No permission resources.');
base::load_sys_class('model', '', 0);
class bc_risk_profiles_model extends model {
    public function __construct() {
        $this->db_config = base::load_config('database');
        $this->db_setting = 'default';
        $this->table_name = 'risk_profiles';
        parent::__construct();
    }
}
?>
