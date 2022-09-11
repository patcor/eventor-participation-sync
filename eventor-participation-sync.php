<?php
/*
 * Plugin Name: Eventor Participation Sync
 * Version: 1.0
 * Plugin URI: http://www.okloftan.se
 * Description: Fetches the clubs participations in events
 * Author: OK Löftan
 * Author URI: http://www.okloftan.se
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: okloftan
 *
 * @package WordPress
 * @author Patrik Corneliusson
 * @since 1.0.0
*/
if (!defined('ABSPATH')) exit;

class EventorParticipationSyncPlugin
{
    public function sync()
    {
        global $wpdb;
        $query = "DELETE FROM wp_posts WHERE post_type = 'eventor-posts'";
        $wpdb->query($query);

        $baseUrl = "https://eventor.orientering.se/api/";
        $ch = curl_init();
        $fromdate = date('Y-m-d', strtotime("-5 days", strtotime(date("Y-m-d"))));
        $todate = date('Y-m-d', strtotime("+3 months", strtotime(date("Y-m-d"))));

        curl_setopt($ch, CURLOPT_URL, $baseUrl . "entries?organisationIds=" . get_option('eventor_organization_id') . "&fromEventDate=" . $fromdate . "&toEventDate=" . $todate . "&includePersonElement=true&includeEventElement=true");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "ApiKey: " . get_option('eventor_api_key')
        ));
        $responseString = curl_exec($ch);
        curl_close($ch);
        $data = simplexml_load_string($responseString);

        $eventCounts = [];
        foreach ($data->Entry as $entry)
        {
            if (array_key_exists("{$entry->Event->EventId}", $eventCounts))
            {
                $eventCounts["{$entry->Event->EventId}"]["count"] = $eventCounts["{$entry->Event->EventId}"]["count"] + 1;
            }
            else
            {
                $info = ["count" => 1, "name" => $entry->Event->Name, "eventdate" => (string)$entry->Event->StartDate->Date];
                $eventCounts["{$entry->Event->EventId}"] = $info;
            }

        }

        foreach ($eventCounts as $key => $value)
        {

            $my_post = array(
                'post_title' => wp_strip_all_tags($value["name"]) ,
                'post_content' => '',
                'post_type' => 'eventor-posts',
                'post_status' => 'publish'
            );

            // Insert the post into the database
            $post_id = wp_insert_post($my_post);

            update_field(get_option('acf_url_field_key') , 'https://eventor.orientering.se/Events/Show/' . $key, $post_id); // Url
            update_field(get_option('acf_count_field_key') , $value["count"], $post_id); // Count
            update_field(get_option('acf_eventdate_field_key') , $value["eventdate"], $post_id); // EventDate
            
        }
    }
}

// Daily Cron activated att
register_activation_hook(__FILE__, 'eps_activation');

function eps_activation()
{

    $rp = new EventorParticipationSyncPlugin();
    $rp->sync();

    if (!wp_next_scheduled('eps_daily_sync'))
    {
        wp_schedule_event(strtotime("tomorrow", time()) , 'daily', 'eps_daily_sync');
    }
}

add_action('eps_daily_sync', 'eps_daily_sync');

function eps_daily_sync()
{
    $rp = new EventorParticipationSyncPlugin();
    $rp->sync();
}

// Daily Cron is Deactivated on Plugin inactivation
register_deactivation_hook(__FILE__, 'eps_deactivation');
function eps_deactivation()
{
    wp_clear_scheduled_hook('eps_daily_sync');
}

add_filter('plugin_action_links_eventor-participation-sync/eventor-participation-sync.php', 'eps_settings_link');
function eps_settings_link($links)
{
    $url = esc_url(add_query_arg('page', 'eps-settings', get_admin_url() . 'options-general.php'));
    $settings_link = "<a href='$url'>" . __('Settings') . '</a>';
    array_push($links, $settings_link);
    return $links;
}

function eps_register_settings()
{
    register_setting('eventor_participation_settings', 'eventor_api_key');
    register_setting('eventor_participation_settings', 'eventor_organization_id');
    register_setting('eventor_participation_settings', 'acf_url_field_key');
    register_setting('eventor_participation_settings', 'acf_count_field_key');
    register_setting('eventor_participation_settings', 'acf_eventdate_field_key');
}
add_action('admin_init', 'eps_register_settings');

function eps_plugin_setting_page()
{
    add_options_page('Eventor participation sync settings', 'Eventor participation sync', 'manage_options', 'eps-settings', 'eps_html_form');
}
add_action('admin_menu', 'eps_plugin_setting_page');

function eps_html_form()
{ ?>
    <div class="wrap">
        <h2>Eventor participation settings</h2>
        <form method="post" action="options.php">
            <?php settings_fields('eventor_participation_settings'); ?>
 
        <table class="form-table">
 
            <tr>
                <th><label for="eventor_api_key">Eventor API Key:</label></th>
                <td>
                    <input type = 'text' class="regular-text" id="eventor_api_key" name="eventor_api_key" value="<?php echo get_option('eventor_api_key'); ?>">
                </td>
            </tr>
            
            <tr>
                <th><label for="eventor_organization_id">Eventor Organization Id:</label></th>
                <td>
                    <input type = 'text' class="regular-text" id="eventor_organization_id" name="eventor_organization_id" value="<?php echo get_option('eventor_organization_id'); ?>">
                </td>
            </tr>
 
            <tr>
                <th><label for="second_field_id">ACF Url field key:</label></th>
                <td>
                    <input type = 'text' class="regular-text" id="acf_url_field_key" name="acf_url_field_key" value="<?php echo get_option('acf_url_field_key'); ?>">
                </td>
            </tr>
 
            <tr>
                <th><label for="third_field_id">ACF Count field key:</label></th>
                <td>
                    <input type = 'text' class="regular-text" id="acf_count_field_key" name="acf_count_field_key" value="<?php echo get_option('acf_count_field_key'); ?>">
                </td>
            </tr>
            
            <tr>
                <th><label for="third_field_id">ACF Event date field key:</label></th>
                <td>
                    <input type = 'text' class="regular-text" id="acf_eventdate_field_key" name="acf_eventdate_field_key" value="<?php echo get_option('acf_eventdate_field_key'); ?>">
                </td>
            </tr>
        </table>
 
        <?php submit_button(); ?>
 
    </div>
<?php } ?>
