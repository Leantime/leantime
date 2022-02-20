<?php

namespace leantime\domain\services;

use leantime\core\config;
use leantime\domain\repositories;

class ldap
{

    private $ldapConnection;
    private $host;
    private $port;
    private $baseDn; //Base DN for domain
    private $ldapDn; //DN where users are located (including baseDn)
    public $userDomain;
    private $ldapKeys = array (
        "username" => "uid",
        "groups" => "memberof",
        "email" => "mail",
        "firstname" => "displayname",
        "lastname" => '',
        );
    private $ldapLtGroupAssignments = array();
    private $settingsRepo;
    private $defaultRoleKey;

    /**
     * @var config
     */
    private $config;
    /**
     * @var array|bool|int|mixed|string
     */
    public $useLdap;
    /**
     * @var array|bool|int|mixed|string
     */
    public $autoCreateUser;

    public function __construct($differentConfig = false)
    {

        $this->settingsRepo = new repositories\setting();

        if($differentConfig == false) {

            $this->config = new config();
            //Map config vars
            $this->useLdap = $this->settingsRepo->getSetting('companysettings.ldap.useLdap') ? $this->settingsRepo->getSetting('companysettings.ldap.useLdap') : $this->config->useLdap;

            //Don't do anything else if ldap is turned off
            if ($this->useLdap === false) {
                return false;
            }

            $this->host = $this->settingsRepo->getSetting('companysettings.ldap.ldapHost') ? $this->settingsRepo->getSetting('companysettings.ldap.ldapHost') : $this->config->ldapHost;
            $this->autoCreateUser = $this->settingsRepo->getSetting('companysettings.ldap.autoCreateUser') ? $this->settingsRepo->getSetting('companysettings.ldap.autoCreateUser') : $this->config->autoCreateUser;
            $this->baseDn = $this->settingsRepo->getSetting('companysettings.ldap.baseDn') ? $this->settingsRepo->getSetting('companysettings.ldap.baseDn') : $this->config->baseDn;
            $this->ldapDn = $this->settingsRepo->getSetting('companysettings.ldap.ldapDn') ? $this->settingsRepo->getSetting('companysettings.ldap.ldapDn') : $this->config->ldapDn;
            $this->defaultRoleKey = $this->settingsRepo->getSetting('companysettings.ldap.ldapDefaultRoleKey') ? $this->settingsRepo->getSetting('companysettings.ldap.ldapDefaultRoleKey') : $this->config->ldapDefaultRoleKey;
            $this->port = $this->settingsRepo->getSetting('companysettings.ldap.ldapPort') ? $this->settingsRepo->getSetting('companysettings.ldap.port') : $this->config->ldapPort;
            $this->userDomain = $this->settingsRepo->getSetting('companysettings.ldap.ldapUserDomain') ? $this->settingsRepo->getSetting('companysettings.ldap.ldapUserDomain') : $this->config->ldapUserDomain;

            $this->ldapLtGroupAssignments = $this->settingsRepo->getSetting('companysettings.ldap.ltGroupAssignments') ? json_decode($this->settingsRepo->getSetting('companysettings.ldap.ltGroupAssignments')) : json_decode(trim(preg_replace('/\s+/', '', $this->config->ldapLtGroupAssignments)));

            //var_dump(   (trim(preg_replace('/\s+/', '', $this->config->ldapLtGroupAssignments))) );
            //echo json_last_error();

            $this->ldapKeys = $this->settingsRepo->getSetting('companysettings.ldap.ldapKeys') ? json_decode($this->settingsRepo->getSetting('companysettings.ldap.ldapKeys')) : json_decode(trim(preg_replace('/\s+/', '', $this->config->ldapKeys)));


        }else{
            //TODO
        }

        //$this->getRoleAssignments();


    }

    private function getRoleAssignments (){

        //$assignedRoles = $this->settingsRepo->getSetting('companysettings.ldap.roleAssignments') ? unserialize($this->settingsRepo->getSetting('companysettings.ldap.roleAssignments')) : $this->config->assignedRoles;

        //TODO: Should come from db roles table eventually
        $availableRoles = core\login::$userRoles;
        foreach($availableRoles as $key => $row) {
            if(isset($this->ldapLtGroupAssignments[$row])) {
                $this->ldapLtGroupAssignments[$key] = array("ltRole" => $row, "ldapRole" => $assignedRoles[$row]);
            }
        }

    }

    public function connect() {

        $this->ldapConnection = ldap_connect($this->host, $this->port);

        ldap_set_option($this->ldapConnection, LDAP_OPT_PROTOCOL_VERSION, 3) or die('Unable to set LDAP protocol version');
        ldap_set_option($this->ldapConnection, LDAP_OPT_REFERRALS, 0); // We need this for doing an LDAP search.

        return true;

    }

    public function bind($username, $password){

        if($username == '') {
            return false;
        }

        $usernameDN = $this->ldapKeys->username."=".$this->extractLdapFromUsername($username).",".$this->ldapDn;

        return ldap_bind($this->ldapConnection, $usernameDN, $password);

    }

    public function getSingleUser($username) {

        if(!is_resource($this->ldapConnection)){
            throw Exception("No connection");
        }

        $filter = "(".$this->ldapKeys->username."=" . $this->extractLdapFromUsername($username) . ")";

        $attr = array($this->ldapKeys->groups, $this->ldapKeys->firstname, $this->ldapKeys->lastname);

        $result = ldap_search($this->ldapConnection, $this->ldapDn, $filter, $attr) or exit("Unable to search LDAP server");
        $entries = ldap_get_entries($this->ldapConnection, $result);

        //Find Role
        $role = $this->defaultRoleKey;

        foreach($entries[0][$this->ldapKeys->groups] as $grps) {

            foreach($this->ldapLtGroupAssignments as $key=>$row) {

                if( $row->ldapRole != "") {
                    if (strpos($grps, $row->ldapRole)) {
                        if ($key > $role) {
                            $role = $key;
                        }
                    }
                }
            }
        }

        //Find Firstname & Lastname
        $firstname = isset($entries[0][$this->ldapKeys->firstname]) ? $entries[0][$this->ldapKeys->firstname][0] : '';
        $lastname = isset($entries[0][$this->ldapKeys->lastname]) ? $entries[0][$this->ldapKeys->lastname][0] : '';

        return array(
            "user" => $this->extractLdapFromUsername($username)."".$this->userDomain,
            "firstname" => $firstname,
            "lastname" => $lastname,
            "role" => $role,
            );
    }

    public function extractLdapFromUsername($username){

        $getLdap = explode("@", $username);

        if($getLdap && is_array($getLdap)) {
            return $getLdap[0];
        }else {
            return "";
        }

    }

    public function getAllMembers($group,$user,$password) {

            $ldap_host = "LDAPSERVER";
            $ldap_dn = "OU=some_group,OU=some_group,DC=company,DC=com";
            $base_dn = "DC=company,DC=com";
            $ldap_usr_dom = "@company.com";
            $ldap = ldap_connect($ldap_host);

            $results = ldap_search($ldap,$ldap_dn, "cn=" . $group);
            $member_list = ldap_get_entries($ldap, $results);

            $dirty = 0;
            $group_member_details = array();

            foreach($member_list[0]['member'] as $member) {
                if($dirty == 0) {
                    $dirty = 1;
                } else {
                    $member_dn = explode_dn($member);
                    $member_cn = str_replace("CN=","",$member_dn[0]);
                    $member_search = ldap_search($ldap, $base_dn, "(CN=" . $member_cn . ")");
                    $member_details = ldap_get_entries($ldap, $member_search);
                    $group_member_details[] = array($member_details[0]['givenname'][0],$member_details[0]['sn'][0],$member_details[0]['telephonenumber'][0],$member_details[0]['othertelephone'][0]);
                }
            }
            ldap_close($ldap);
            return $group_member_details;
        }


}