<?php

/**
 * Client class - All data access for clients
 */

namespace Leantime\Domain\Clients\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Core\Db\Repository;

class Clients extends Repository
{
    public string $name;

    protected string $entity = 'clients';

    public int $id;

    private ConnectionInterface $db;

    /**
     * __construct - get database connection
     */
    public function __construct(DbCore $db)
    {
        $this->db = $db->getConnection();
    }

    /**
     * getClient - get one client from db
     */
    public function getClient(int|string $id): array|false
    {
        $result = $this->db->table('zp_clients')
            ->select(
                'zp_clients.id',
                'zp_clients.name',
                'zp_clients.street',
                'zp_clients.zip',
                'zp_clients.city',
                'zp_clients.state',
                'zp_clients.country',
                'zp_clients.phone',
                'zp_clients.internet',
                'zp_clients.email'
            )
            ->selectRaw('COUNT(zp_projects.clientId) AS numberOfProjects')
            ->leftJoin('zp_projects', 'zp_clients.id', '=', 'zp_projects.clientId')
            ->where('zp_clients.id', $id)
            ->groupBy(
                'zp_clients.id',
                'zp_clients.name',
                'zp_clients.street',
                'zp_clients.zip',
                'zp_clients.city',
                'zp_clients.state',
                'zp_clients.country',
                'zp_clients.phone',
                'zp_clients.internet',
                'zp_clients.email'
            )
            ->orderBy('zp_clients.name')
            ->limit(1)
            ->first();

        if ($result !== null) {
            $row = (array) $result;
            $this->name = $row['name'];
            $this->id = $row['id'];

            return $row;
        }

        return false;
    }

    /**
     * getAll - get all clients
     */
    public function getAll(): array
    {
        $results = $this->db->table('zp_clients')
            ->select(
                'zp_clients.id',
                'zp_clients.name',
                'zp_clients.internet'
            )
            ->selectRaw('COUNT(zp_projects.clientId) AS numberOfProjects')
            ->leftJoin('zp_projects', 'zp_clients.id', '=', 'zp_projects.clientId')
            ->groupBy(
                'zp_clients.id',
                'zp_clients.name',
                'zp_clients.internet'
            )
            ->orderBy('zp_clients.name')
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * @return int|mixed
     */
    public function getNumberOfClients(): mixed
    {
        return $this->db->table('zp_clients')->count();
    }

    public function isClient(array $values): bool
    {
        return $this->db->table('zp_clients')
            ->where('name', $values['name'])
            ->where('street', $values['street'])
            ->exists();
    }

    public function getClientsUsers(int|string $clientId): false|array
    {
        $results = $this->db->table('zp_user')
            ->select(
                'id',
                'firstname',
                'lastname',
                'username',
                'notifications',
                'profileId',
                'phone',
                'status'
            )
            ->where('clientId', $clientId)
            ->where(function ($query) {
                $query->whereNull('source')
                    ->orWhere('source', '!=', 'api');
            })
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * addClient - add a client and postback test
     */
    public function addClient(array $values): false|string
    {
        $id = $this->db->table('zp_clients')->insertGetId([
            'name' => $values['name'],
            'street' => $values['street'] ?? '',
            'zip' => $values['zip'] ?? '',
            'city' => $values['city'] ?? '',
            'state' => $values['state'] ?? '',
            'country' => $values['country'] ?? '',
            'phone' => $values['phone'] ?? '',
            'internet' => $values['internet'] ?? '',
            'email' => $values['email'] ?? '',
        ]);

        return (string) $id;
    }

    /**
     * editClient - edit a client
     */
    public function editClient(array $values, int|string $id): bool
    {
        return $this->db->table('zp_clients')
            ->where('id', $id)
            ->limit(1)
            ->update([
                'name' => $values['name'],
                'street' => $values['street'],
                'zip' => $values['zip'],
                'city' => $values['city'],
                'state' => $values['state'],
                'country' => $values['country'],
                'phone' => $values['phone'],
                'internet' => $values['internet'],
                'email' => $values['email'],
            ]) >= 0;
    }

    /**
     * deleteClient - delete a client and associated projects
     */
    public function deleteClient(int|string $id): bool
    {
        // Delete projects associated with the client first
        $this->db->table('zp_projects')
            ->where('clientId', $id)
            ->delete();

        // Then delete the client
        return $this->db->table('zp_clients')
            ->where('id', $id)
            ->delete() > 0;
    }

    /**
     * hasTickets - check if a project has Tickets
     */
    public function hasTickets(int|string $id): bool
    {
        return $this->db->table('zp_projects')
            ->join('zp_tickets', 'zp_projects.id', '=', 'zp_tickets.projectId')
            ->where('zp_projects.clientId', $id)
            ->exists();
    }
}
