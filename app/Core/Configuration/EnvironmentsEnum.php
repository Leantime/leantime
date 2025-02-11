<?php

namespace Leantime\Core\Configuration;

/**
 * Enum class Environment options
 *
 * Enum representing the available environment configs
 */
enum EnvironmentsEnum: string
{
    /**
     * Set to dev environment
     */
    case Dev = 'dev';

    /**
     * Set to staging environment
     */
    case Staging = 'staging';

    /**
     * Set to oss release environment
     */
    case Oss = 'oss';

    /**
     * Set to prod environment
     */
    case Production = 'production';

}
