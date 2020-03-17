<?php

/**
 * Client class - All data access for clients
 *
 */
namespace leantime\domain\repositories {

    use leantime\core;
    use pdo;

    class clients
    {

        /**
         * @access public
         * @var    string
         */
        public $name;

        /**
         * @access public
         * @var    integer
         */
        public $id;

        /**
         * @access public
         * @var    object
         */
        private $db='';

        /**
         * __construct - get database connection
         *
         * @access public
         */
        public function __construct()
        {

            $this->db = core\db::getInstance();

        }

        /**
         * getClient - get one client from db
         *
         * @access public
         * @param  $id
         * @return array
         */
        public function getClient($id)
        {

            $query = "SELECT 
                 zp_clients.id,
                 zp_clients.name,
                 zp_clients.street,
                 zp_clients.zip,
                 zp_clients.city,
                 zp_clients.state,
                 zp_clients.country,
                 zp_clients.phone,
                 zp_clients.internet,
                 zp_clients.email,
              COUNT(zp_projects.clientId) AS numberOfProjects
					FROM zp_clients 
					LEFT JOIN zp_projects ON zp_clients.id = zp_projects.clientId
				WHERE  zp_clients.id = :id
				GROUP BY 
						zp_clients.id,
						zp_clients.name,
						zp_clients.internet
				ORDER BY zp_clients.name
				LIMIT 1
				";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            $stmn->execute();
            $row = $stmn->fetch();
            $stmn->closeCursor();

            if(count($row) > 0) {
                $this->name = $row['name'];

                $this->id = $row['id'];

                return $row;
            }else{
                return false;
            }

        }

        /**
         * getAll - get all clients
         *
         * @access public
         * @return array
         */
        public function getAll()
        {

            $query = "SELECT
						zp_clients.id,
						zp_clients.name,
						zp_clients.internet,
						COUNT(zp_projects.clientId) AS numberOfProjects
					FROM zp_clients 
					LEFT JOIN zp_projects ON zp_clients.id = zp_projects.clientId
					
					GROUP BY 
						zp_clients.id,
						zp_clients.name,
						zp_clients.internet
				ORDER BY zp_clients.name";

            $stmn = $this->db->database->prepare($query);


            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;

        }

        public function isClient($values)
        {

            $sql = "SELECT name, street FROM zp_clients WHERE
			name = :name AND street = :street LIMIT 1";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':name', $values['name'], PDO::PARAM_STR);
            $stmn->bindValue(':street', $values['street'], PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            $flag = false;
            if (count($values)) {
                $flag = true;
            }

            return $flag;
        }

        public function getClientsUsers($clientId)
        {

            $sql = "SELECT * FROM zp_user WHERE clientId = :clientId";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':clientId', $clientId, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        /**
         * addClient - add a client and postback test
         *
         * @access public
         * @param  array $values
         */
        public function addClient(array $values)
        {

            $sql = "INSERT INTO zp_clients (
					name, street, zip, city, state, country, phone, internet, email
				) VALUES (
					:name, :street, :zip, :city, :state, :country, :phone, :internet, :email
				)";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':name', $values['name'], PDO::PARAM_STR);
            $stmn->bindValue(':street', $values['street'], PDO::PARAM_STR);
            $stmn->bindValue(':zip', $values['zip'], PDO::PARAM_STR);
            $stmn->bindValue(':city', $values['city'], PDO::PARAM_STR);
            $stmn->bindValue(':state', $values['state'], PDO::PARAM_STR);
            $stmn->bindValue(':country', $values['country'], PDO::PARAM_STR);
            $stmn->bindValue(':phone', $values['phone'], PDO::PARAM_STR);
            $stmn->bindValue(':internet', $values['internet'], PDO::PARAM_STR);
            $stmn->bindValue(':email', $values['email'], PDO::PARAM_STR);

            $stmn->execute();

            $id = $this->db->database->lastInsertId();
            $stmn->closeCursor();

            return $id;
        }

        /**
         * editClient - edit a client
         *
         * @access public
         * @param  array $values
         * @param  $id
         */
        public function editClient(array $values, $id)
        {

            $query = "UPDATE zp_clients SET
			 	name = :name, 
			 	street = :street, 
			 	zip = :zip, 
			 	city = :city, 
			 	state = :state, 
			 	country = :country, 
			 	phone = :phone, 
			 	internet = :internet,
			 	email = :email
			 WHERE id = :id LIMIT 1";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':name', $values['name'], PDO::PARAM_STR);
            $stmn->bindValue(':street', $values['street'], PDO::PARAM_STR);
            $stmn->bindValue(':zip', $values['zip'], PDO::PARAM_STR);
            $stmn->bindValue(':city', $values['city'], PDO::PARAM_STR);
            $stmn->bindValue(':state', $values['state'], PDO::PARAM_STR);
            $stmn->bindValue(':country', $values['country'], PDO::PARAM_STR);
            $stmn->bindValue(':phone', $values['phone'], PDO::PARAM_STR);
            $stmn->bindValue(':internet', $values['internet'], PDO::PARAM_STR);
            $stmn->bindValue(':email', $values['email'], PDO::PARAM_STR);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $stmn->execute();
            $stmn->closeCursor();
        }

        /**
         * deleteClient - delete a client
         *
         * @access public
         * @param  $id
         */
        public function deleteClient($id)
        {

            $query = "DELETE zp_clients, zp_projects FROM zp_clients LEFT JOIN zp_projects ON zp_clients.id = zp_projects.clientId WHERE zp_clients.id = :id";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);
            $stmn->execute();
            $stmn->closeCursor();
        }

        /**
         * hasTickets - check if a project has Tickets
         *
         * @access public
         * @param  $id
         * @return boolean
         */
        public function hasTickets($id)
        {

            $query = "SELECT zp_projects.id FROM zp_projects JOIN zp_tickets ON zp_projects.id = zp_tickets.projectId WHERE zp_projects.clientId = :id";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);
            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            if (count($values) > 0) {

                return true;
            } else {

                return false;
            }

        }

    }

}
