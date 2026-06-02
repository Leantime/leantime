<?php

namespace Leantime\Core\Domains;

/**
 * Marker interface for all domain (and plugin) service classes.
 *
 * It carries no required methods on purpose. Real services have wildly different shapes
 * (the Tickets service alone has ~75 heterogeneous methods), so a fixed CRUD contract
 * never fit — which is exactly why the previous patch/update/create/delete/get/query
 * interface had zero implementers. This marker instead gives the service layer a single
 * type to scan for and a shared home (via {@see BaseService}) for the cross-cutting
 * authorize()/validate() helpers, without forcing a fictional method surface.
 *
 * Granular capability interfaces (e.g. a real Crudable) may be introduced alongside this
 * marker where they genuinely apply, opt-in per service.
 */
interface DomainService {}
