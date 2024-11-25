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
        
        // public $numberOfProjects;
        
        public function __construct(array|object|bool $attributes = false) {
            
            if ($attributes !== false && is_array($attributes)) {
                $this->id = $attributes['id'] ?? '';
                $this->name = $attributes['name'] ?? '';
                $this->street = $attributes['street'] ?? '';
                $this->zip = $attributes['zip'] ?? '';
                $this->city = $attributes['city'] ?? '';
                $this->state = $attributes['state'] ?? '';
                $this->country = $attributes['country'] ?? '';
                $this->phone = $attributes['phone'] ?? '';
                $this->internet = $attributes['internet'] ?? '';
                $this->email = $attributes['email'] ?? '';
            } 
            else if ($attributes !== false && is_object($attributes)) {
                $this->id = $attributes->id ?? '';
                $this->name = $attributes->name ?? '';
                $this->street = $attributes->street ?? '';
                $this->zip = $attributes->zip ?? '';
                $this->city = $attributes->city ?? '';
                $this->state = $attributes->state ?? '';
                $this->country = $attributes->country ?? '';
                $this->phone = $attributes->phone ?? '';
                $this->internet = $attributes->internet ?? '';
                $this->email = $attributes->email ?? '';
            }
        }
    }

}