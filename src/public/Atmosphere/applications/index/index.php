<?php
/**
*    @file    \applications\index\index.php
*    @class   index
*/
namespace Atmosphere\Controller
{
    class index extends Controller
    {
        public function _config()
        {
            $this->example = true;
        }
        public function index ()
        {
            echo 'Hello World!';
        }
    }
}