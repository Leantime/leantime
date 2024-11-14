<?php

namespace Leantime\Domain\Clients\Models {

    class Clients
    {
        public $id;
        
        public $name;
        
        public $street;
        
        public $zip;
        
        public $city;
        
        public $state;
        
        public $country;
        
        public $phone;
        
        public $internet;
        
        public $email;
        
        public $numberOfProjects;
        
        public function __construct() {}
    }

}