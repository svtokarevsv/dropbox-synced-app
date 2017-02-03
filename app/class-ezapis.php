<?php
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\Dropbox;

require_once 'class-medprovider.php';

class EZapis
{
    private $dropbox;
    private $db;

    public function __construct($key, $secret, $token)
    {
        $app = new DropboxApp($key, $secret, $token);
        $this->dropbox = new Dropbox($app);
        $this->db = DB::getInstance();
    }


    private function list_medical_provider()
    {
        return $this->db->query('CALL list_medical_provider(0,NULL,NULL,NULL)');
    }

    private function checkSchedule()
    {
        $now=time();
        $last_sync=(int)array_shift($this->db->query("SELECT get_custom_variable_value('last_sync')")[0]);
        $minutes_diff=($now-$last_sync)/60;
        $interval=array_shift($this->db->query("SELECT get_custom_variable_value('sync_dropbox_interval')")[0]);
        var_dump($now,$last_sync,$interval,$minutes_diff);

        return $interval>=$minutes_diff;
    }

    public function run()
    {
//        $v=$this->checkSchedule();
//        file_put_contents(__DIR__.'/temp/cron.sql',1);echo 'done';die;

//        die;
        foreach ($this->list_medical_provider() as $index => $provider_data) {
            $med_provider = new MedProvider($provider_data, $this->dropbox);
            if (!$med_provider->contents) {
                continue;
            }
            $med_provider->process_sql_to_db();
            $med_provider->get_script_procedure();
        }
        echo 'done';
    }

}
