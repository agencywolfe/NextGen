<?php
/**
 * User enumeration
 *
 * @package wp-fail2ban
 * @since   4.0.0
 */
namespace org\lecklider\charles\wordpress\wp_fail2ban\feature;

use function org\lecklider\charles\wordpress\wp_fail2ban\array_value;
use function org\lecklider\charles\wordpress\wp_fail2ban\bail;
use function org\lecklider\charles\wordpress\wp_fail2ban\openlog;
use function org\lecklider\charles\wordpress\wp_fail2ban\syslog;
use function org\lecklider\charles\wordpress\wp_fail2ban\closelog;

defined('ABSPATH') or exit;

/**
 * Common enumeration handling
 *
 * @since 4.3.0 Remove JSON support
 * @since 4.1.0 Add JSON support
 * @since 4.0.0
 *
 * @param bool  $is_json
 *
 * @return \WP_Error
 *
 * @wp-f2b-hard Blocked user enumeration attempt
 */
function _log_bail_user_enum()
{
    if (openlog()) {
        syslog(LOG_NOTICE, 'Blocked user enumeration attempt');
        closelog();
    }

    do_action(__FUNCTION__);

    return bail();
}

/**
 * Catch traditional user enum
 *
 * @see \WP::parse_request()
 *
 * @since 4.3.0 Refactored to make XDebug happy; h/t @dinghy
 *              Changed cap to 'edit_others_posts'
 * @since 3.5.0 Refactored for unit testing
 * @since 2.1.0
 *
 * @param \WP   $query
 *
 * @return \WP
 */
function parse_request($query)
{
    if (!current_user_can('edit_others_posts') && intval(array_value('author', $query->query_vars))) {
        _log_bail_user_enum();
    }

    return $query;
}

/**
 * Catch RESTful user list
 *
 * @see \WP_REST_Users_Controller::get_items()
 *
 * @since 4.3.0 Change to 'edit_others_posts'
 * @since 4.0.0
 *
 * @param array             $prepared_args
 * @param \WP_REST_Request  $request
 *
 * @return array|\WP_Error
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function rest_user_query($prepared_args, $request)
{
    if (!current_user_can('edit_others_posts')) {
        return _log_bail_user_enum();
    }

    return $prepared_args;
}

/**
 * Catch oembed user info
 *
 * @see \get_oembed_response_data()
 *
 * @since 4.2.7
 *
 * @param array   $data   The response data.
 * @param WP_Post $post   The post object.
 * @param int     $width  The requested width.
 * @param int     $height The calculated height.
 *
 * @return array
 *
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function oembed_response_data($data, $post, $width, $height)
{
    unset($data['author_name']);
    unset($data['author_url']);

    return $data;
}

