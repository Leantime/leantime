<?php

/**
 * Detect browser client
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @license	GNU/GPL, see license.txt
 *
 */
 
class client  
 {  
     /** 
      * Available Mobile Clients 
      * 
      * @var array 
      */  
     private $_mobileClients = array(  
         "midp",  
         "240x320",  
         "blackberry",  
         "netfront",  
         "nokia",  
         "panasonic",  
         "portalmmm",  
         "sharp",  
         "sie-",  
         "sonyericsson",  
         "symbian",  
         "windows ce",  
         "benq",  
         "mda",  
         "mot-",  
         "opera mini",
     	"opera mobi",  
        "philips",  
         "pocket pc",  
         "sagem",  
         "samsung",  
         "sda",  
         "sgh-",  
         "vodafone",  
         "xda",  
         "iphone",  
         "android"  
     );  
   
     /** 
     * Check if client is a mobile client 
      * 
      * @param string $userAgent 
      * @return boolean 
      */  
     public function isMobileClient($userAgent)  
     {  
         $userAgent = strtolower($userAgent);  
         foreach($this->_mobileClients as $mobileClient) {  
             if (strstr($userAgent, $mobileClient)) {  
                 return true;  
             }  
         }  
        return false;  
     }  
   
 }  
   
