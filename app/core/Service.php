<?php

namespace Leantime\Core;

/**
 * Service Interface - Base interface for all services
 *
 * @package    leantime
 * @subpackage core
 */
interface Service
{
    /**
     * patches the object by key.
     *
     * @param  int   $id     id of the object to be patched
     * @param  array $params key=>value array where key represents the object field name and value the value.
     * @access public
     *
     * @return boolean returns true on success, false on failure
     */
    public function patch(int $id, array $params): bool;

    /**
     * updates the object by key.
     *
     * @param  object|array $object expects the entire object to be updated as object or array
     * @access public
     *
     * @return boolean returns true on success, false on failure
     */
    public function update(object|array $object): bool;

    /**
     * Creates a new object
     *
     * @access public
     * @param  object|array  $object object or array to be created
     * @return integer|false returns id of new element or false
     */
    public function create(object|array $object): int|false;

    /**
     * Deletes object
     *
     * @access public
     * @param  int     $id id of the object to be deleted
     * @return boolean returns id of new element or false
     */
    public function delete(int $id): bool;

    /**
     * Gets 1 specific item
     *
     * @access public
     * @param  int                $id id of the object to be retrieved
     * @return object|array|false returns object or array. False on failure or if item cannot be found
     */
    public function get(int $id): object|array|false;

    /**
     * Get all items
     *
     * @access public
     * @param  ?array      $searchparams search parameters
     * @return array|false returns array on success, false on failure. No results should return empty array
     */
    public function getAll(array $searchparams = null): array|false;
}
