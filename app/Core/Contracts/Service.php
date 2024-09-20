<?php

namespace Leantime\Core\Contracts;

/**
 * Service Interface - Base interface for all services.
 */
interface Service
{
    /**
     * patches the object by key.
     *
     * @param int   $id     Id of the object to be patched
     * @param array $params Key=>value array where key represents the object field name and value the value.
     *
     * @return bool returns true on success, false on failure
     */
    public function patch(int $id, array $params): bool;

    /**
     * updates the object by key.
     *
     * @param object|array $object expects the entire object to be updated as object or array
     *
     * @return array|bool Returns true on success, false on failure
     */
    public function update(object|array $object): array|bool;

    /**
     * Creates a new object.
     *
     * @param object|array $object Object or array to be created
     *
     * @return int|false Returns id of new element or false
     */
    public function create(object|array $object): int|false;

    /**
     * Deletes object.
     *
     * @param int $id Id of the object to be deleted
     *
     * @return bool Returns id of new element or false
     */
    public function delete(int $id);

    /**
     * Gets 1 specific item.
     *
     * @param int $id Id of the object to be retrieved
     *
     * @return object|array|false Returns object or array. False on failure or if item cannot be found
     */
    public function get(int $id);

    /**
     * Get all items.
     *
     * @param array|null $searchparams Search parameters
     *
     * @return array|false Returns array on success, false on failure. No results should return empty array
     */
    public function query(array $searchparams = null);
}
