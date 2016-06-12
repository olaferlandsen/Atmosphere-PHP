<?php
namespace Atmosphere\Database
{
    class Results
    {
        private $boolean = false;
        private $results;
        public function __construct($data)
        {
            $this->results = $data;
        }
        
        public function __toString()
        {
            if ($this->boolean === true) {
                if ($this->results == "1" OR $this->results == 1 OR $this->results == true) {
                    return true;
                }
                return false;
            }
            return $this->results;
        }
        
        public function get()
        {
            return $this->results;
        }
        
        public function boolean()
        {
            $this->boolean = true;
        }
    }
}