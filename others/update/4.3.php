<?php

require_once __DIR__."/update.php";

class Update_43 extends Update
{
    public function update()
    {
        $this->_config->set("lbc", "api_key", "ba0c2dad52b3ec");
    }
}
