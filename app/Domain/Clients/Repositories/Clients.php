<?php

/**
 * Client class - All data access for clients
 *
 */

namespace Leantime\Domain\Clients\Repositories {

    use Leantime\Core\Db\Db as DbCore;
    use Leantime\Core\Db\Repository;
    use PDO;

    /**
     *
     */
    class Clients extends Repository
    {
        /**
         * @access public
         * @var    string
         */
        public string $name;

        /**
         * @access protected
         * @var    string
         */
        protected string $entity = "clients";

        /**
         * @access public
         * @var    int
         */
        public int $id;

        /**
         * @access public
         * @var    object
         */
        private $db = '';

        /**
         * __construct - get database connection
         *
         * @access public
         */
        public function __construct(
            DbCore $db
        ) {
            $this->db = $db;
        }

        /**
         * getClient - get one client from db
         *
         * @access public
         * @param  $id
         * @return array|false
         */
        public function getClient($id): array|false
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

            if ($row !== false && count($row) > 0) {
                $this->name = $row['name'];

                $this->id = $row['id'];

                return $row;
            } else {
                return false;
            }
        }

        /**
         * getAll - get all clients
         *
         * @access public
         * @return array
         */
        public function getAll(): array
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

        /**
         * @return int|mixed
         */
        public function getNumberOfClients(): mixed
        {

            $sql = "SELECT COUNT(id) AS clientCount FROM `zp_clients`";

            $stmn = $this->db->database->prepare($sql);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            if (isset($values['clientCount']) === true) {
                return $values['clientCount'];
            } else {
                return 0;
            }
        }

        /**
         * @param $values
         * @return bool
         */
        public function isClient($values): bool
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

        /**
         * @param $clientId
         * @return array|false
         */
        public function getClientsUsers($clientId): false|array
        {

            $sql = "SELECT
                    zp_user.id,
					zp_user.firstname,
					zp_user.lastname,
					zp_user.username,
					zp_user.notifications,
					zp_user.profileId,
					zp_user.phone,
                    zp_user.status
                    FROM zp_user WHERE clientId = :clientId
                    AND !(source <=> 'api') ";

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
         * @param array $values
         * @return false|string
         */
        public function addClient(array $values): false|string
        {

            $sql = "INSERT INTO zp_clients (
					name, street, zip, city, state, country, phone, internet, email
				) VALUES (
					:name, :street, :zip, :city, :state, :country, :phone, :internet, :email
				)";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':name', $values['name'], PDO::PARAM_STR);
            $stmn->bindValue(':street', $values['street'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':zip', $values['zip'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':city', $values['city'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':state', $values['state'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':country', $values['country'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':phone', $values['phone'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':internet', $values['internet'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':email', $values['email'] ?? '', PDO::PARAM_STR);

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
        public function editClient(array $values, $id): bool
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

            $result = $stmn->execute();
            $stmn->closeCursor();

            return $result;
        }

        /**
         * deleteClient - delete a client
         *
         * @access public
         * @param  $id
         * @return bool
         */
        public function deleteClient($id): bool
        {

            $query = "DELETE zp_clients, zp_projects FROM zp_clients LEFT JOIN zp_projects ON zp_clients.id = zp_projects.clientId WHERE zp_clients.id = :id";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);
            $result = $stmn->execute();
            $stmn->closeCursor();

            return $result;
        }

        /**
         * hasTickets - check if a project has Tickets
         *
         * @access public
         * @param  $id
         * @return bool
         */
        public function hasTickets($id): bool
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
