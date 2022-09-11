<?php
function eventor_init()
{
    $args = array(
        'label' => 'Eventor competitions',
        'public' => false,
        'show_ui' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
        'rewrite' => array(
            'slug' => 'eventor'
        ) ,
        'query_var' => false,
        'menu_icon' => 'dashicons-location-alt',
        'supports' => array(
            'title'
        )
    );
    register_post_type('eventor-posts', $args);
}
add_action('init', 'eventor_init');

//
// // Creating the widget
class eventor_widget extends WP_Widget
{

    function __construct()
    {
        parent::__construct('eventor_widget', __('Eventor competitions', 'eventor_widget_domain') ,

        array(
            'description' => __('Eventor competition widget', 'eventor_widget_domain') ,
        ));
    }

    // Creating widget front-end
    // This is where the action happens
    public function widget($args, $instance)
    {
        $title = apply_filters('widget_title', $instance['title']);
        echo $args['before_widget'];
        if (!empty($title))

        global $wpdb;
        $rows = $wpdb->get_results("SELECT $wpdb->posts.* FROM $wpdb->posts, $wpdb->postmeta WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id AND $wpdb->postmeta.meta_key = 'eventdate' AND $wpdb->posts.post_type = 'eventor-posts' ORDER BY $wpdb->postmeta.meta_value");

        if (count($rows) > 0)
        {
            setlocale(LC_TIME, "sv_SE");

            $currentMonth = "";
            foreach ($rows as $row)
            {

                if (strftime("%B", strtotime(get_field('eventdate', $row->ID))) != $currentMonth)
                {
                    $currentMonth = strftime("%B", strtotime(get_field('eventdate', $row->ID)));
                    echo __('<h4>' . ucfirst($currentMonth) . '</h4>', 'eventor_widget_domain');
                }

                echo __('<a href="' . get_field('url', $row->ID) . '">', 'eventor_widget_domain');
                echo __(substr($row->post_title, 0, 25) , 'eventor_widget_domain');
                echo __('</a> (' . get_field('count', $row->ID) . ')<br/>', 'eventor_widget_domain');

            }
        }

        echo $args['after_widget'];
    }

    public function form($instance)
    {
        if (isset($instance['title']))
        {
            $title = $instance['title'];
        }
        else
        {
            $title = __('New title', 'eventor_widget_domain');
        }
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        return $instance;
    }
} 

function eventor_load_widget()
{
    register_widget('eventor_widget');
}
add_action('widgets_init', 'eventor_load_widget');

?>
