<?php

namespace leantime\domain\repositories {

    use leantime\core;
    use pdo;

    class setting
    {

        private $db;

        public $applications = array(
            'general' => 'General'
        );

        /**
         * __construct - neu db connection
         *
         * @access public
         * @return
         */
        public function __construct()
        {

            $this->db = core\db::getInstance();

        }

        public function getSetting($type)
        {

                $sql = "SELECT
						value
				FROM zp_settings WHERE `key` = :key
				LIMIT 1";

                $stmn = $this->db->database->prepare($sql);
                $stmn->bindvalue(':key', $type, PDO::PARAM_STR);

                try {
                    $stmn->execute();
                    $values = $stmn->fetch();
                    $stmn->closeCursor();

                }catch(\PDOException $e){
                    return false;
                }

                if($values !== false && isset($values['value'])) {
                    return $values['value'];
                }

                //TODO: This needs to return null or throw an exception if the setting doesn't exist.
                return false;

        }

        public function saveSetting($type, $value)
        {

            $sql = "INSERT INTO zp_settings (`key`, `value`)
				VALUES (:key, :value) ON DUPLICATE KEY UPDATE
				  `value` = :value";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindvalue(':key', $type, PDO::PARAM_STR);
            $stmn->bindvalue(':value', $value, PDO::PARAM_STR);

            $stmn->execute();
            $stmn->closeCursor();


        }

        public function deleteSetting($type)
        {

            $sql = "DELETE FROM zp_settings WHERE `key` = :key LIMIT 1";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindvalue(':key', $type, PDO::PARAM_STR);

            $stmn->execute();
            $stmn->closeCursor();


        }

        public function getUsers()
        {
        }

        public function getMenu($user)
        {

            if ($user != '') {
                //Fetch Parents
                $query = "SELECT 
						t1.id AS id, 
						t1.name AS name, 
						t1.link AS link, 
						t1.parent AS parent, 
						t1.inTopNav AS inTopNav,
						t2.name AS parentName 			
				FROM zp_menu AS t1 
				
				
				LEFT JOIN zp_menu AS t2 ON t1.parent = t2.id
				ORDER BY t2.name, t1.name";

                $stmn = $this->db->database->prepare($query);

                $stmn->execute();

                $array = $stmn->fetchAll();


                $stmn->closeCursor();


                for ($i = 0; $i < count($array); $i++) {


                    $query2 = "SELECT * 
							FROM 
								zp_usermenu
							WHERE 
								(menuId = '" . $array[$i]['id'] . "') 
								AND
								(username = '" . $user . "')";

                    $row2 = $this->db->dbQuery($query2)->hasResults();


                    if ($row2 === true) {

                        $array[$i]['isRelated'] = '1';

                    } else {

                        $array[$i]['isRelated'] = '0';

                    }


                }


                return $array;

            } else {


                return array();

            }

        }

        public function deleteAllRelations($user)
        {

            $query = "DELETE FROM zp_usermenu WHERE username = :user";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':user', $user, PDO::PARAM_STR);

            $stmn->execute();

            $stmn->closeCursor();

        }

        public function insertRelations($values, $user)
        {

            $num = count($values);

            $this->db->database->beginTransaction();

            for ($i = 0; $i < $num; $i++) {

                $query = "INSERT INTO zp_usermenu
				(username, menuId) VALUES
				(:user, :value)";

                $stmn = $this->db->database->prepare($query);

                $stmn->bindValue(':user', $user, PDO::PARAM_STR);
                $stmn->bindValue(':value', $values[$i], PDO::PARAM_STR);

                $stmn->execute();


            }
            $this->db->database->commit();

            $stmn->closeCursor();

        }


        public function getWholeMenu()
        {

            //Fetch Parents
            $query = "SELECT * FROM zp_menu 
				ORDER BY name";

            $stmn = $this->db->database->prepare($query);

            $stmn->execute();

            $values = $stmn->fetchAll();


            $stmn->closeCursor();

            return $values;

        }

        public function getMenuById($id)
        {

            $query = "SELECT * FROM zp_menu AS t1 
				WHERE id = :id
				LIMIT 1";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            return $values;
        }

        public function deleteAllRelationsMenuUser($menu)
        {

            $query = "DELETE FROM zp_usermenu WHERE menuId = :menu";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':menu', $menu, PDO::PARAM_STR);

            $stmn->execute();

            $stmn->closeCursor();

        }


        public function insertRelationsMenuUser($menu, $user)
        {

            $num = count($user);
            $stmn = '';
            $this->db->database->beginTransaction();

            for ($i = 0; $i < $num; $i++) {

                $query = "INSERT INTO zp_usermenu
				(username, menuId) VALUES
				(:user, :menu)";

                $stmn = $this->db->database->prepare($query);

                $stmn->bindValue(':menu', $menu, PDO::PARAM_STR);
                $stmn->bindValue(':user', $user[$i], PDO::PARAM_STR);

                $stmn->execute();


            }

            $this->db->database->commit();

            if ($num > 0) {
                $stmn->closeCursor();
            }

        }




        public function getRoles()
        {

            $query = "SELECT 
			zp_roles.id,
			zp_roles.roleName,
			zp_roles.roleDescription,
			zp_roles.sysOrg,
			zp_system_organisations.name AS sysOrgName,
			zp_system_organisations.modules
			FROM zp_roles
			LEFT JOIN zp_system_organisations ON zp_roles.sysOrg = zp_system_organisations.id
			
			ORDER BY zp_roles.sysOrg, zp_roles.roleName";

            $stmn = $this->db->database->prepare($query);

            $stmn->execute();

            $values = $stmn->fetchAll();


            $stmn->closeCursor();

            return $values;


        }

        public function getRole($id)
        {

            $query = "SELECT id, roleName, roleDescription, sysOrg, template FROM zp_roles WHERE id = :id";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            $stmn->execute();

            $values = $stmn->fetch();

            $stmn->closeCursor();

            return $values;
        }

        public function getRoleByName($name)
        {

            $sql = "SELECT * FROM zp_roles WHERE roleName = :name";

            $stmn = $this->db->database->prepare($sql);

            $stmn->bindValue(':name', $name, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            return $values;
        }

        public function roleAliasExist($alias, $id = '')
        {

            $query = "SELECT id FROM zp_roles 
		WHERE roleName = :alias ";

            if ($id != '') {

                $query .= "AND id <> :id";

            }

            $query .= " LIMIT 1";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':alias', $alias, PDO::PARAM_STR);

            if ($id != '') {

                $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            }

            $stmn->execute();

            $values = $stmn->fetch();

            $stmn->closeCursor();

            if (isset($values) && count($values) >= 1 && $values == true) {

                return true;

            } else {

                return false;

            }


        }

        public function editRole($values, $id)
        {

            $this->db->database->beginTransaction();

            $query = "UPDATE zp_roles SET 
			roleName = :roleName,
			roleDescription = :roleDescription,
			sysOrg = :sysOrg,
			template = :template
			 WHERE id = :id LIMIT 1";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':roleName', $values['roleName'], PDO::PARAM_STR);
            $stmn->bindValue(':roleDescription', $values['roleDescription'], PDO::PARAM_STR);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);
            $stmn->bindValue(':sysOrg', $values['sysOrg'], PDO::PARAM_STR);
            $stmn->bindValue(':template', $values['template'], PDO::PARAM_STR);
            $stmn->execute();

            $menu = $values['menu'];

            //Delete old Relations
            $query = "DELETE FROM zp_rolesdefaultmenu WHERE roleId = :id";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            $stmn->execute();

            if ($menu[0] != '') {

                $query2 = "INSERT INTO zp_rolesdefaultmenu
					(menuId, roleId) 
						VALUES ";

                for ($i = 0; $i < count($menu); $i++) {

                    $query2 .= "(" . $menu[$i] . ", " . $id . ")";

                    if ($i < count($menu) - 1) {

                        $query2 .= ",";

                    }

                }

                $query2 .= " ";

                $stmn = $this->db->database->prepare($query2);
                $stmn->execute();

            }


            $this->db->database->commit();


            $stmn->closeCursor();


        }

        public function deleteRole($id, $roleName, $newRole)
        {

            $query = "DELETE FROM zp_roles WHERE id = :id LIMIT 1";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            $stmn->execute();

            $stmn->closeCursor();

            //Change all Users to new Role

            $query = "UPDATE zp_user SET 
		role = :newRole 
		WHERE role = :oldRole";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':newRole', $newRole, PDO::PARAM_STR);
            $stmn->bindValue(':oldRole', $roleName, PDO::PARAM_STR);

            $stmn->execute();

            $stmn->closeCursor();

            //Delete old Relations
            $query = "DELETE FROM zp_rolesdefaultmenu WHERE roleId = :id";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            $stmn->execute();

            $stmn->closeCursor();


        }

        public function newRole($values)
        {

            $this->db->database->beginTransaction();

            $query = "INSERT INTO zp_roles (roleName, roleDescription, sysOrg, template) VALUES (:roleName, :roleDescription, :sysOrg, :template)";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':roleName', $values['roleName'], PDO::PARAM_STR);
            $stmn->bindValue(':roleDescription', $values['roleDescription'], PDO::PARAM_STR);
            $stmn->bindValue(':sysOrg', $values['sysOrg'], PDO::PARAM_STR);
            $stmn->bindValue(':template', $values['template'], PDO::PARAM_STR);

            $stmn->execute();

            $menu = $values['menu'];

            if ($menu[0] != '') {

                $id = $this->db->database->lastInsertId();

                $query2 = "INSERT INTO zp_rolesdefaultmenu
					(menuId, roleId) 
						VALUES ";

                for ($i = 0; $i < count($menu); $i++) {

                    $query2 .= "(" . $menu[$i] . ", " . $id . ")";

                    if ($i < count($menu) - 1) {

                        $query2 .= ",";

                    }

                }

                $query2 .= " ";

                $stmn = $this->db->database->prepare($query2);
                $stmn->execute();

            }


            $this->db->database->commit();

            $stmn->closeCursor();

        }


        public function editMenu($values, $id)
        {

            $query = "UPDATE zp_menu SET 
					name   = :name,
					parent = :parent,
					module = :module,
					action = :action,
					icon  = :icon
				WHERE id = :id";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':name', $values['name'], PDO::PARAM_STR);
            $stmn->bindValue(':parent', $values['parent'], PDO::PARAM_STR);
            $stmn->bindValue(':module', $values['module'], PDO::PARAM_STR);
            $stmn->bindValue(':action', $values['action'], PDO::PARAM_STR);
            $stmn->bindValue(':icon', $values['icon'], PDO::PARAM_STR);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            $stmn->execute();
            $stmn->closeCursor();
        }

        public function addMenu($values)
        {

            $query = "INSERT INTO zp_menu (name, parent, module, action, icon) VALUES 
			(:name, :parent, :module, :action, :icon)";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':name', $values['name'], PDO::PARAM_STR);
            $stmn->bindValue(':parent', $values['parent'], PDO::PARAM_STR);
            $stmn->bindValue(':module', $values['module'], PDO::PARAM_STR);
            $stmn->bindValue(':action', $values['action'], PDO::PARAM_STR);
            $stmn->bindValue(':icon', $values['icon'], PDO::PARAM_STR);

            $stmn->execute();
            $stmn->closeCursor();
        }

        public function deleteMenu($id)
        {
            $query = "DELETE FROM zp_menu WHERE id = :id LIMIT 1";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':id', $id, PDO::PARAM_STR);


            $stmn->execute();

            $stmn->closeCursor();
        }

        public function getDefaultMenu($id)
        {

            $query = "SELECT id, roleId, menuId FROM zp_rolesdefaultmenu WHERE roleId = :id";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            $stmn->execute();

            $values = $stmn->fetchAll();

            $stmn->closeCursor();

            $menuRelation = array();

            foreach ($values as $menuId) {

                $menuRelation[] = $menuId['menuId'];

            }

            return $menuRelation;

        }


        public function getAllSystemOrganisations()
        {

            $query = "SELECT 
			id,
			alias,
			name,
			modules
			FROM zp_system_organisations ORDER BY name";

            $stmn = $this->db->database->prepare($query);

            $stmn->execute();

            $values = $stmn->fetchAll();


            $stmn->closeCursor();

            return $values;


        }

        public function getSystemOrg($id)
        {

            $query = "SELECT 
			id,
			alias,
			name,
			modules
			FROM zp_system_organisations 
			WHERE id = :id ORDER BY name";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            $stmn->execute();

            $values = $stmn->fetch();


            $stmn->closeCursor();

            return $values;


        }

        public function editSystemOrg($values, $id)
        {


            $query = "UPDATE zp_system_organisations SET 
			name = :name,
			alias = :alias,
			modules = :modules				
			WHERE id = :id LIMIT 1";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':modules', $values['modules'], PDO::PARAM_STR);
            $stmn->bindValue(':name', $values['name'], PDO::PARAM_STR);
            $stmn->bindValue(':alias', $values['alias'], PDO::PARAM_STR);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            $stmn->execute();

            $stmn->closeCursor();

        }

        public function newSystemOrg($values)
        {


            $query = "INSERT INTO zp_system_organisations (name, alias, modules) VALUES (:name,:alias,:modules)";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':name', $values['name'], PDO::PARAM_STR);
            $stmn->bindValue(':alias', $values['alias'], PDO::PARAM_STR);
            $stmn->bindValue(':modules', $values['modules'], PDO::PARAM_STR);
            $stmn->execute();

            $stmn->closeCursor();

        }

        public function deleteSystemOrg($id, $newRole)
        {


            $this->db->database->beginTransaction();

            $query = "DELETE FROM zp_system_organisations WHERE id = :id LIMIT 1";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            $stmn->execute();


            /*SELECT all roles that needs to be deleted*/
            $query = "SELECT roleName FROM zp_roles WHERE sysOrg = :id";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            $stmn->execute();


            $values = $stmn->fetchAll();


            foreach ($values as $row) {

                $query = "UPDATE zp_user SET 
			role = :newRole 
			WHERE role = :oldRole";

                $stmn = $this->db->database->prepare($query);

                $stmn->bindValue(':newRole', $newRole, PDO::PARAM_STR);
                $stmn->bindValue(':oldRole', $values['roleName'], PDO::PARAM_STR);

                $stmn->execute();


            }


            /* DELETE all Roles */
            $query = "DELETE FROM zp_roles WHERE sysOrg = :id";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            $stmn->execute();


            //Change all Users to new Role


            //Delete old Relations
            $query = "DELETE FROM zp_rolesdefaultmenu WHERE roleId = :id";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            $stmn->execute();


            $this->db->database->commit();

            $stmn->closeCursor();

        }

        public function getAllTemplates()
        {

            $path = 'includes/templates/';
            $arrayModules = array();
            if ($handle = opendir($path)) {

                while (false !== ($file = readdir($handle))) {
                    if ($file != '.' && $file != '..') {

                        $arrayModules[] = $file;

                    }

                }


                closedir($handle);
            }


            return $arrayModules;

        }

        public function getTabRights($action)
        {

            $query = "SELECT 
			id,
			action,
			tab,
			tabRights
			FROM zp_action_tabs
			WHERE action = :action";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':action', $action, PDO::PARAM_STR);

            $stmn->execute();

            $values = $stmn->fetchAll();


            $stmn->closeCursor();

            $results = array();

            foreach ($values as $row) {

                if (is_array(explode('|', $row['tabRights'])) === true) {

                    $results[$row['tab']] = explode('|', $row['tabRights']);

                } else {

                    $results[$row['tab']] = $row['tabRights'];

                }

            }

            return $results;

        }

        public function saveTabRights($action, $values)
        {
            $stmn = '';

            $this->db->database->beginTransaction();

            $query1 = "DELETE FROM zp_action_tabs WHERE action = :action";
            $stmn = $this->db->database->prepare($query1);
            $stmn->bindValue(':action', $action, PDO::PARAM_STR);

            $stmn->execute();


            foreach ($values as $row) {

                $query = "INSERT INTO zp_action_tabs
				(action, tab, tabRights) VALUES
				(:action, :tab, :tabRights)";

                $stmn = $this->db->database->prepare($query);

                $stmn->bindValue(':action', $row['action'], PDO::PARAM_STR);
                $stmn->bindValue(':tab', $row['tab'], PDO::PARAM_STR);
                $stmn->bindValue(':tabRights', $row['tabRights'], PDO::PARAM_STR);

                $stmn->execute();

            }

            $this->db->database->commit();
            $stmn->closeCursor();

        }

        public function hasTabRights($action)
        {

            $query = "SELECT 
			id

			FROM zp_action_tabs WHERE action = :action LIMIT 1";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':action', $action, PDO::PARAM_STR);

            $stmn->execute();

            $values = $stmn->fetch();

            if ($values !== false) {

                return true;

            } else {

                return false;

            }

        }



        public function getAllSubmodulesInDB()
        {

            $sql = "SELECT * FROM zp_submodulerights";

            $stmn = $this->db->database->prepare($sql);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        public function getSubmoduleByFile($file)
        {

            $sql = "SELECT * FROM zp_submodulerights WHERE submodule=:file LIMIT 1";

            $stmn = $this->db->database->prepare($sql);

            $stmn->bindValue(':file', $file, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            $return = false;
            if ($values != null) {
                $return = $values;
            }

            return $return;
        }

        public function getSubmodule($alias)
        {

            $sql = "SELECT * FROM zp_submodulerights WHERE alias=:alias LIMIT 1";

            $stmn = $this->db->database->prepare($sql);

            $stmn->bindValue(':alias', $alias, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            $return = false;
            if ($values != null) {
                $return = $values;
            }

            return $return;
        }

        public function getAllSubmodules()
        {

            $path = 'includes/modules/';
            $submodule = '/templates/submodules/';
            $submoduleFiles = array();

            $dirs = glob($path . '*', GLOB_ONLYDIR);

            foreach ($dirs as $dir) {
                if (file_exists($dir . $submodule)) {
                    $module = str_replace($path, '', $dir);
                    $subDirs = glob($dir . $submodule . '*');

                    foreach ($subDirs as $subDir) {

                        $submoduleFile = str_replace($dir, '', $subDir);
                        $submoduleFile = str_replace($submodule, '', $submoduleFile);
                        $alias = $module . '-' . str_replace('.sub.php', '', $submoduleFile);

                        $values = array(
                            'alias' => $alias,
                            'submodule' => $submoduleFile,
                            'module' => $module,
                            'title' => '',
                            'id' => null,
                            'roleIds' => null
                        );

                        $dbSubmodule = $this->getSubmodule($alias);
                        if ($dbSubmodule !== false) {
                            $values['alias'] = $dbSubmodule['alias'];
                            $values['id'] = $dbSubmodule['id'];
                            $values['roleIds'] = $dbSubmodule['roleIds'];
                            $values['title'] = $dbSubmodule['title'];
                        }

                        $submoduleFiles[] = $values;
                    }
                }
            }

            return $submoduleFiles;
        }

        public function saveSubmoduleRights($values)
        {

            $this->db->database->beginTransaction();

            $truncate = "TRUNCATE zp_submodulerights";
            $insert = "INSERT INTO zp_submodulerights (alias, title, submodule, module, roleIds) 
					VALUES (:alias, :title, :submodule, :module, :roleIds)";


            $stmn = $this->db->database->prepare($truncate);
            $stmn->execute();

            foreach ($values as $value) {

                $stmn = $this->db->database->prepare($insert);

                $stmn->bindValue(':alias', $value['alias'], PDO::PARAM_STR);
                $stmn->bindValue(':title', $value['title'], PDO::PARAM_STR);
                $stmn->bindValue(':submodule', $value['submodule'], PDO::PARAM_STR);
                $stmn->bindValue(':module', $value['module'], PDO::PARAM_STR);
                $stmn->bindValue(':roleIds', $value['roleIds'], PDO::PARAM_STR);

                $stmn->execute();
            }


            $this->db->database->commit();

            $stmn->closeCursor();

        }



        /**
         * checkIfInstalled checks if zp user table exists (and assumes that leantime is installed)
         *
         * @access public
         * @return bool
         */
        public function checkIfInstalled()
        {

            try {

                $stmn = $this->db->database->prepare("SELECT COUNT(*) FROM zp_user");

                $stmn->execute();
                $values = $stmn->fetchAll();

                $stmn->closeCursor();

                return true;

            } catch (\PDOException $e) {

                return false;

            }
        }

    }
}
