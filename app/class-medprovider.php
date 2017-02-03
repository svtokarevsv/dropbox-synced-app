<?php

class MedProvider
{
    private $id;
    private $path;
    public $contents;
    private $dropbox;

    public function __construct($provider_data, $dropbox)
    {
        $this->dropbox = $dropbox;
        $this->db = DB::getInstance();
        $this->id = $provider_data['medical_provider_id'];
        $this->path = end(explode('\\', $provider_data['sync_path']));
        $this->process_contents();
    }

    private function get_folder_contents($path)
    {
        $listFolderContents = $this->dropbox->listFolder("/" . $path, ["include_deleted" => false, "recursive" => false]);

        $items = $listFolderContents->getItems();
        return $items->all();
    }


    private function filter_contents()
    {
        foreach ($this->contents as $key => $item) {
            /*check if is sql file and starts with From_ */
            if (!preg_match('/^from_(.)*\.sql$/i', $item->name)) {
                unset($this->contents[$key]);
            }
            /*check if not a folder*/
            if ($item->getDataProperty('.tag') !== 'file') {
                unset($this->contents[$key]);
            }
        }

    }

    public function process_sql_to_db()
    {
        $i = 0;
        foreach ($this->contents as $index => $item) {
            $path_to_sql = $item->getDataProperty('path_display');

            $file = $this->dropbox->download($path_to_sql);
            //File Contents
            $contents = $file->getContents();
            $sql_array = explode(';', $contents);
            $result = null;
            foreach ($sql_array as $sql) {

                if (!empty(trim($sql))) {
                    $result = null;
                    try {
                        $result = $this->db->exec($sql);

                    } catch (Exception $e) {
                        break;
                    }
                }
            }

            if($result!==null){
                $this->move_sql_to_dir('done',$item);
            }else{
                $this->move_sql_to_dir('error',$item);
            }

        }
    }

    private function move_sql_to_dir($destination, $item)
    {
        $destination_dir = '/' . $this->path . '/' . $destination . '/';
        $file = $this->dropbox->move($item->getDataProperty('path_display'), $destination_dir . $item->getName());
    }

    public function get_script_procedure()
    {
        $result=$this->db->query("CALL get_script({$this->id})");
        $content=null;
        if(count($result)){
            foreach ($result as $item) {
                $content.=array_shift($item)."\r";
            }
            $this->upload_report($content);
        }

    }

    private function upload_report($content)
    {
        $date=date("Y_m_d_H_i_s");
        $filename='TO_MEDUCHET_'.$date.'.sql';
        $temp_file=APP_DIR.'temp'.DS.$filename;
        file_put_contents($temp_file,$content);
        $file = $this->dropbox->simpleUpload($temp_file, "/".$this->path.'/'.$filename, ['autorename' => true]);
        unlink($temp_file);
    }

    private function process_contents()
    {
        if (!empty($this->path)) {
            try {
                $this->contents = $this->get_folder_contents($this->path);
            } catch (Exception $e) {
                return false;
            }

            $this->filter_contents();
        }
    }

}
