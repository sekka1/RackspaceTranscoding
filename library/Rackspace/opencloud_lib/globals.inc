<?php
/**
 * Defines global constants and functions
 *
 * @copyright 2012 Rackspace Hosting, Inc.
 * See COPYING for licensing information
 *
 * @package phpOpenCloud
 * @version 1.0
 * @author Glen Campbell <glen.campbell@rackspace.com>
 */

/**
 * This file contains only configuration data such as constants.
 * You can override these constants by defining them BEFORE you including
 * any of the top-level files from the SDK.
 *
 * Definitions:
 * * RAXSDK_TIMEZONE - the default timezone for interpreting date/time requests
 * * RAXSDK_STRICT_PROPERTY_CHECKS - if TRUE, the library will strictly enforce
 *      property names on objects; only properties that are pre-defined or
 *      appear in the extensions aliases for the service will be permitted.
 *      When FALSE (the default), then any property can be set on an object.
 * * RAXSDK_COMPUTE_NAME - the default name for the compute service
 * * RAXSDK_COMPUTE_REGION - the default region for the compute service
 * * RAXSDK_COMPUTE_URLTYPE - the default URL type for the compute service
 * * RAXSDK_OBJSTORE_NAME - the default name for the object storage service
 * * RAXSDK_OBJSTORE_REGION - the default region for the object storage service
 * * RAXSDK_OBJSTORE_URLTYPE - the default URL type for the object storage
 *      service
 * * RAXSDK_DATABASE_NAME - the default name for the DbService service
 * * RAXSDK_DATABASE_REGION - the default region for the DbService service
 * * RAXSDK_DATABASE_URLTYPE - the default URL type for the DbService service
 * * RAXSDK_CONNECTTIMEOUT - the time (in seconds) to wait for a connection
 *      to a service
 * * RAXSDK_TIMEOUT - the max time (in seconds) to wait for an HTTP request
 *      to complete
 * * RAXSDK_SERVER_MAXTIMEOUT - the max time (in seconds) that a server
 *      will wait for a change in status (Server::WaitFor() method)
 * * RAXSDK_POLL_INTERVAL - how often (in seconds) the Server::WaitFor() method
 *      will poll for a status change
 * * RAXSDK_DEFAULT_IP_VERSION - the default IP version (4 or 6) to return for
 *      the server's primary IP address
 * * RAXSDK_OVERLIMIT_TIMEOUT - the max time (in seconds) to wait before
 *      retrying a request that has failed because of rate limits. If the
 *      next available time for the request is more than (X) seconds away,
 *      then the request will fail; otherwise, the request will sleep until
 *      available.
 */

if (!defined('RAXSDK_TIMEZONE'))
    define('RAXSDK_TIMEZONE', 'America/Chicago');
if (!defined('RAXSDK_STRICT_PROPERTY_CHECKS'))
    define('RAXSDK_STRICT_PROPERTY_CHECKS', FALSE);
if (!defined('RAXSDK_COMPUTE_NAME'))
    define('RAXSDK_COMPUTE_NAME', 'cloudServersOpenStack');
if (!defined('RAXSDK_COMPUTE_REGION'))
    define('RAXSDK_COMPUTE_REGION', NULL);
if (!defined('RAXSDK_COMPUTE_URLTYPE'))
    define('RAXSDK_COMPUTE_URLTYPE', 'publicURL');
if (!defined('RAXSDK_OBJSTORE_NAME'))
    define('RAXSDK_OBJSTORE_NAME', 'cloudFiles');
if (!defined('RAXSDK_OBJSTORE_REGION'))
    define('RAXSDK_OBJSTORE_REGION', NULL);
if (!defined('RAXSDK_OBJSTORE_URLTYPE'))
    define('RAXSDK_OBJSTORE_URLTYPE', 'publicURL');
if (!defined('RAXSDK_DATABASE_NAME'))
    define('RAXSDK_DATABASE_NAME', 'cloudDatabases');
if (!defined('RAXSDK_DATABASE_REGION'))
    define('RAXSDK_DATABASE_REGION', NULL);
if (!defined('RAXSDK_DATABASE_URLTYPE'))
    define('RAXSDK_DATABASE_URLTYPE', 'publicURL');
if (!defined('RAXSDK_VOLUME_NAME'))
    define('RAXSDK_VOLUME_NAME', 'cloudBlockStorage');
if (!defined('RAXSDK_VOLUME_REGION'))
    define('RAXSDK_VOLUME_REGION', NULL);
if (!defined('RAXSDK_VOLUME_URLTYPE'))
    define('RAXSDK_VOLUME_URLTYPE', 'publicURL');
if (!defined('RAXSDK_LBSERVICE_NAME'))
    define('RAXSDK_LBSERVICE_NAME', 'cloudLoadBalancers');
if (!defined('RAXSDK_LBSERVICE_REGION'))
    define('RAXSDK_LBSERVICE_REGION', NULL);
if (!defined('RAXSDK_LBSERVICE_URLTYPE'))
    define('RAXSDK_LBSERVICE_URLTYPE', 'publicURL');
if (!defined('RAXSDK_CONNECTTIMEOUT'))
    define('RAXSDK_CONNECTTIMEOUT', 5);
if (!defined('RAXSDK_TIMEOUT'))
    define('RAXSDK_TIMEOUT', 30);
if (!defined('RAXSDK_SERVER_MAXTIMEOUT'))
    define('RAXSDK_SERVER_MAXTIMEOUT', 3600);
if (!defined('RAXSDK_POLL_INTERVAL'))
    define('RAXSDK_POLL_INTERVAL', 10);
if (!defined('RAXSDK_DEFAULT_IP_VERSION'))
    define('RAXSDK_DEFAULT_IP_VERSION', 4);
if (!defined('RAXSDK_OVERLIMIT_TIMEOUT'))
    define('RAXSDK_OVERLIMIT_TIMEOUT', 300);
/**
 * sets default (highly secure) value for CURLOPT_SSL_VERIFYHOST. If you are using
 * a self-signed SSL certificate, you can reduce this setting, but you do so at your
 * own risk. 
 */
if (!defined('RAXSDK_SSL_VERIFYHOST'))
    define('RAXSDK_SSL_VERIFYHOST', 2);
/**
 * sets default (highly secure) value for CURLOPT_SSL_VERIFYPEER. If you are using
 * a self-signed SSL certificate, you can reduce this setting, but you do so at your
 * own risk.
 */
if (!defined('RAXSDK_SSL_VERIFYPEER'))
    define('RAXSDK_SSL_VERIFYPEER', TRUE);

// set the default timezone
date_default_timezone_set(RAXSDK_TIMEZONE);

/* these should not be overridden */
define('RAXSDK_VERSION', '1.2');
define('RAXSDK_USER_AGENT', 'php-opencloud/'.RAXSDK_VERSION.' (Rackspace)');
define('RAXSDK_ERROR', 'Error:');
define('RAXSDK_FATAL', 'FATAL ERROR:');
define('RAXSDK_TERMINATED', '*** PROCESSING HALTED ***');
define('RAXSDK_CONTENT_TYPE', 'Content-Type: application/json');
define('RAXSDK_URL_PUBLIC', 'publicURL');
define('RAXSDK_URL_INTERNAL', 'internalURL');
define('RAXSDK_URL_VERSION_INFO', 'versionInfo');
define('RAXSDK_URL_VERSION_LIST', 'versionList');
/**
 * We can re-authenticate this many seconds before the token expires
 *
 * Set this to a higher value if your service does not cache tokens; if it *does* cache
 * them, then this value is not required.
 */
define('RAXSDK_FUDGE', 0);
/**
 * Readable constants
 */
define('RAXSDK_SOFT_REBOOT', 'soft');
define('RAXSDK_HARD_REBOOT', 'hard');
define('RAXSDK_DETAILS', TRUE);
define('RAXSDK_MAX_CONTAINER_NAME_LEN', 256);
/**
 * UUID of the Rackspace 'public' network
 */
define('RAX_PUBLIC','00000000-0000-0000-0000-000000000000');
/**
 * UUID of the Rackspace 'private' network
 */
define('RAX_PRIVATE','11111111-1111-1111-1111-111111111111');


/********** SOME GLOBAL FUNCTIONS **********/

/**
 * _() - this function should be used to wrap all static strings. In the future,
 * this may provide us with a hook for providing different language
 * translations.
 */
function define_gettext() {
	function _($str) {
		return $str;
	}
}
if (!function_exists('_'))
	define_gettext();

/**
 * removes trailing slash(es) from a URL string
 *
 * Mainly, this is just for appearance's sake. I really hate to see
 * URLs like .../servers//address, for some reason.
 */
function noslash($str) {
	while ($str && (substr($str, -1) == '/'))
		$str = substr($str, 0, strlen($str)-1);
	return $str;
}

/**
 * Turns debugging on or off
 */
function setDebug($state=TRUE) {
    global $RAXSDK_DEBUG;
    $RAXSDK_DEBUG=$state;
}
