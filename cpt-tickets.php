<?php

// Register custom post type 'ticket'
//currently added to advanced scrips as php code in development 


function create_ticket_post_type() {
    $labels = array(
        'name'               => 'Tickets',
        'singular_name'      => 'Ticket',
        'add_new'            => 'Add New Ticket',
        'add_new_item'       => 'Add New Ticket',
        'edit_item'          => 'Edit Ticket',
        'new_item'           => 'New Ticket',
        'all_items'          => 'All Tickets',
        'view_item'          => 'View Ticket',
        'search_items'       => 'Search Tickets',
        'not_found'          => 'No tickets found',
        'not_found_in_trash' => 'No tickets found in Trash',
        'parent_item_colon'  => '',
        'menu_name'          => 'Tickets'
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'ticket' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
    );

    register_post_type( 'ticket', $args );
}
add_action( 'init', 'create_ticket_post_type' );

// Disable comments and custom fields for 'ticket' post type
function disable_ticket_comments() {
    remove_post_type_support('ticket', 'comments');
    remove_post_type_support('ticket', 'trackbacks');
    remove_post_type_support('ticket', 'custom-fields');
}
add_action('admin_init', 'disable_ticket_comments');

// Register meta boxes for 'ticket' post type
function register_ticket_meta_boxes() {
    add_meta_box('ticket_details_meta_box', 'Ticket Details', 'display_ticket_meta_box', 'ticket', 'normal', 'high');
    add_meta_box('ticket_file_upload_meta_box', 'File Upload', 'display_ticket_file_upload_meta_box', 'ticket', 'normal', 'high');
    add_meta_box('ticket_custom_fields', 'Ticket Custom Fields', 'display_ticket_custom_fields', 'ticket', 'normal', 'high');

}
add_action('add_meta_boxes', 'register_ticket_meta_boxes');

// Display meta box for ticket details
function display_ticket_meta_box($post) {
    // Use nonce for verification
    wp_nonce_field(basename(__FILE__), 'ticket_meta_box_nonce');

    // Retrieve existing values
    $resolution = get_post_meta($post->ID, '_resolution', true);
    $invoice_number = get_post_meta($post->ID, '_invoice_number', true);
    $time = get_post_meta($post->ID, '_time', true);
    $status = get_post_meta($post->ID, '_status', true);
    $resolution_date = get_post_meta($post->ID, '_resolution_date', true);
    $assigned = get_post_meta($post->ID, '_assigned', true);
    $current_user = wp_get_current_user();
    $sent_for_review = get_post_meta($post->ID, '_sent_for_review', true);
    $is_billable = get_post_meta($post->ID, '_is_billable', true);
    $updates = get_post_meta($post->ID, '_updates', true);
    $billable_amount = get_post_meta($post->ID, '_billable_amount', true);


    ?>

    <p>
        <label for="resolution">Resolution:</label>
        <input type="text" id="resolution" name="resolution" value="<?php echo esc_attr($resolution); ?>" />
    </p>
    <p>
        <label for="invoice_number">Invoice Number:</label>
        <input type="text" id="invoice_number" name="invoice_number" value="<?php echo esc_attr($invoice_number); ?>" />
    </p>
    <p>
        <label for="time">Time (HH:MM):</label>
        <input type="text" id="time" name="time" value="<?php echo esc_attr($time); ?>" />
    </p>
    <p>
        <label for="status">Status:</label>
        <select id="status" name="status">
            <option value="new" <?php selected($status, 'new'); ?>>New</option>
            <option value="in_progress" <?php selected($status, 'in_progress'); ?>>In Progress</option>
            <option value="on_hold" <?php selected($status, 'on_hold'); ?>>On Hold</option>
            <option value="resolved" <?php selected($status, 'resolved'); ?>>Resolved</option>
        </select>
    </p>
    <p>
        <label for="resolution_date">Resolution Date:</label>
        <input type="date" id="resolution_date" name="resolution_date" value="<?php echo esc_attr($resolution_date); ?>" />
    </p>
    <p>
        <label for="assigned">Assigned To:</label>
        <input type="hidden" id="assigned" name="assigned" value="<?php echo esc_attr($assigned); ?>" />
        <input type="text" disabled value="<?php echo esc_attr($current_user->display_name); ?>" />
    </p>
    
    <p>
        <label for="sent_for_review">Sent for Review:</label>
        <select id="sent_for_review" name="sent_for_review">
            <option value="yes" <?php selected($sent_for_review, 'yes'); ?>>Yes</option>
            <option value="no" <?php selected($sent_for_review, 'no'); ?>>No</option>
        </select>
    </p>

    <p>
        <label for="is_billable">Is Billable:</label>
        <input type="checkbox" id="is_billable" name="is_billable" value="1" <?php checked($is_billable, '1'); ?> />
    </p>
    
    <div class="updates-wrapper">
        <label for="updates">Updates:</label>
        <ul id="updates-list">
            <?php if (!empty($updates) && is_array($updates)) : ?>
                <?php foreach ($updates as $update) : ?>
                    <li>
                        <input type="date" class="update-date" name="update_date[]" value="<?php echo esc_attr($update['date']); ?>" />
                        <input type="text" class="update-description" name="update_description[]" value="<?php echo esc_attr($update['description']); ?>" />
                        <input type="time" class="update-time" step="1800" name="update_time[]" value="<?php echo esc_attr($update['time']); ?>" />
                    </li>
                <?php endforeach; ?>
            <?php else : ?>
                <li>
                    <input type="date" class="update-date" name="update_date[]" value="" />
                    <input type="text" class="update-description" name="update_description[]" value="" />
                    <input type="time" class="update-time" step="1800" name="update_time[]" value="" />
                </li>
            <?php endif; ?>
        </ul>
        <button type="button" id="add-update">Add Update</button>
    </div>

    <p>
        <label for="billable_amount">Billable Amount:</label>
        <input type="text" id="billable_amount" name="billable_amount" value="<?php echo esc_attr($billable_amount); ?>" />
        <input type="checkbox" id="allow_billable_edit" name="allow_billable_edit" value="1" />
        <label for="allow_billable_edit">Allow editing</label>
    </p>

    <script>
        jQuery(document).ready(function($) {
            $('#add-update').click(function() {
                $('#updates-list').append('<li><input type="date" class="update-date" name="update_date[]" value="" /><input type="text" class="update-description" name="update_description[]" value="" /><input type="time" class="update-time" step="1800" name="update_time[]" value="" /></li>');
            });
        });
    </script>



    <?php
}

// Save meta box data for ticket details
function save_ticket_meta_box_data($post_id) {
    // Check nonce for security
    if (!isset($_POST['ticket_meta_box_nonce']) || !wp_verify_nonce($_POST['ticket_meta_box_nonce'], basename(__FILE__))) {
        return;
    }

    // Check if auto-saving
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save each field
    $fields = array('resolution', 'invoice_number', 'time', 'status', 'resolution_date', 'assigned');
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
        }
    }

    // Automatically set assigned field to current user if status is "in_progress"
    if (isset($_POST['status']) && $_POST['status'] == 'in_progress') {
        $current_user = wp_get_current_user();
        update_post_meta($post_id, '_assigned', $current_user->ID);
    }

    // Automatically set resolution date if status is "resolved"
    if (isset($_POST['status']) && $_POST['status'] == 'resolved') {
        update_post_meta($post_id, '_resolution_date', current_time('Y-m-d'));
    }
    // Save sent for review
    if (isset($_POST['sent_for_review'])) {
        update_post_meta($post_id, '_sent_for_review', sanitize_text_field($_POST['sent_for_review']));
    } else {
        update_post_meta($post_id, '_sent_for_review', 'no');
    }

    // Save is billable
    $is_billable = isset($_POST['is_billable']) ? '1' : '0';
    update_post_meta($post_id, '_is_billable', $is_billable);

    // Save updates as repeater field
    $updates = array();
    if (isset($_POST['update_date'])) {
        $update_count = count($_POST['update_date']);
        for ($i = 0; $i < $update_count; $i++) {
            if (!empty($_POST['update_date'][$i])) {
                $update = array(
                    'date' => sanitize_text_field($_POST['update_date'][$i]),
                    'description' => sanitize_text_field($_POST['update_description'][$i]),
                    'time' => sanitize_text_field($_POST['update_time'][$i]),
                );
                $updates[] = $update;
            }
        }
    }
    update_post_meta($post_id, '_updates', $updates);

    // Save billable amount
    $billable_amount = isset($_POST['billable_amount']) ? sanitize_text_field($_POST['billable_amount']) : '0';
    update_post_meta($post_id, '_billable_amount', $billable_amount);
}
add_action('save_post', 'save_ticket_meta_box_data');

// Display meta box for file upload
function display_ticket_file_upload_meta_box($post) {
    wp_nonce_field(basename(__FILE__), 'ticket_file_upload_nonce');

    $uploaded_file = get_post_meta($post->ID, '_uploaded_file', true);
    ?>

    <p>
        <label for="uploaded_file">Upload File:</label>
        <input type="file" id="uploaded_file" name="uploaded_file" />
        <?php if ($uploaded_file) : ?>
            <br/>
            <a href="<?php echo wp_get_attachment_url($uploaded_file); ?>" target="_blank">View File</a>
        <?php endif; ?>
    </p>

    <?php
}

// Save file upload meta box data
function save_ticket_file_upload_meta_box($post_id) {
    if (!isset($_POST['ticket_file_upload_nonce']) || !wp_verify_nonce($_POST['ticket_file_upload_nonce'], basename(__FILE__))) {
        return;
    }

    if (isset($_FILES['uploaded_file']) && !empty($_FILES['uploaded_file']['name'])) {
        $file = $_FILES['uploaded_file'];
        $upload_overrides = array('test_form' => false);
        $uploaded_file = media_handle_upload('uploaded_file', $post_id, $upload_overrides);

        if (!is_wp_error($uploaded_file)) {
            update_post_meta($post_id, '_uploaded_file', $uploaded_file);
        }
    }
}

add_action('save_post', 'save_ticket_file_upload_meta_box');

// Generate autonumber for ticket post type
// Generate autonumber for ticket post type
function generate_ticket_autonumber($post_ID, $post) {
    if ($post->post_type == 'ticket' && empty($post->post_name)) {
        $latest_ticket = get_posts(array(
            'post_type' => 'ticket',
            'posts_per_page' => 1,
            'orderby' => 'ID',
            'order' => 'DESC',
        ));

        $latest_autonumber = '001876'; // Default starting autonumber
        if (!empty($latest_ticket)) {
            $latest_post = reset($latest_ticket);
            $latest_post_slug = $latest_post->post_name;
            $latest_autonumber = intval(substr($latest_post_slug, strpos($latest_post_slug, '-') + 1));
            $latest_autonumber++; // Increment autonumber
        }

        $autonumber_str = sprintf('%06d', $latest_autonumber); // Format autonumber to 6 digits
        $post_slug = 'ticket-' . $autonumber_str;
        
        // Update post slug and title
        wp_update_post(array(
            'ID' => $post_ID,
            'post_name' => $post_slug,
            'post_title' => $post->post_title,
        ));
    }
}
add_action('wp_insert_post', 'generate_ticket_autonumber', 10, 2);


// Modify upload directory for ticket post type
function custom_ticket_upload_dir($upload) {
    global $post;

    if ($post && $post->post_type == 'ticket') {
        $upload['subdir'] = '/tickets/' . $post->post_name;
        $upload['path'] = $upload['basedir'] . $upload['subdir'];
        $upload['url'] = $upload['baseurl'] . $upload['subdir'];
    }

    return $upload;
}
add_filter('upload_dir', 'custom_ticket_upload_dir');

// Add custom columns to ticket post type admin screen
function add_ticket_columns($columns) {
    $columns['resolution'] = __('Resolution');
    $columns['invoice_number'] = __('Invoice Number');
    $columns['time'] = __('Time');
    $columns['status'] = __('Status');
    $columns['resolution_date'] = __('Resolution Date');
    $columns['assigned'] = __('Assigned To');
    return $columns;
}
add_filter('manage_ticket_posts_columns', 'add_ticket_columns');

// Populate custom columns with data for ticket post type admin screen
function custom_ticket_column_content($column, $post_id) {
    switch ($column) {
        case 'resolution':
            echo get_post_meta($post_id, '_resolution', true);
            break;
        case 'invoice_number':
            echo get_post_meta($post_id, '_invoice_number', true);
            break;
        case 'time':
            echo get_post_meta($post_id, '_time', true);
            break;
        case 'status':
            echo get_post_meta($post_id, '_status', true);
            break;
        case 'resolution_date':
            echo get_post_meta($post_id, '_resolution_date', true);
            break;
        case 'assigned':
            $user_id = get_post_meta($post_id, '_assigned', true);
            $user_info = get_userdata($user_id);
            if ($user_info) {
                echo $user_info->display_name;
            } else {
                echo '-';
            }
            break;
    }
}
add_action('manage_ticket_posts_custom_column', 'custom_ticket_column_content', 10, 2);

function save_ticket_custom_fields($post_id) {
    // Previous code...

    // Calculate billable amount based on total time in updates
    $billable_amount = 0;
    $total_minutes = 0;
    foreach ($updates as $update) {
        // Convert time (HH:MM) to total minutes
        $time_parts = explode(':', $update['time']);
        $hours = intval($time_parts[0]);
        $minutes = intval($time_parts[1]);
        $total_minutes += $hours * 60 + $minutes;
    }

    // Calculate billable amount based on total minutes
    if ($is_billable == '1') {
        if ($total_minutes <= 30) {
            $billable_amount = 60.00;
        } else {
            $additional_minutes = $total_minutes - 30;
            $additional_quarters = ceil($additional_minutes / 15);
            $billable_amount = 60.00 + ($additional_quarters * 30.00);
        }
    }

    // Allow editing of billable amount if checkbox is checked
    if (isset($_POST['allow_billable_edit'])) {
        update_post_meta($post_id, '_billable_edit_allowed', '1');
    } else {
        update_post_meta($post_id, '_billable_edit_allowed', '0');
    }

    // Update billable amount based on calculation
    update_post_meta($post_id, '_billable_amount', $billable_amount);
}

