<?php
/**
 * Plugin Name: بروزرسانی دسته‌جمعی متن جایگزین رسانه‌ها
 * Description: افزونه‌ای برای بروزرسانی دسته‌جمعی متن جایگزین تمامی رسانه‌ها (تصاویر، ویدیوها، اسناد) در کتابخانه رسانه وردپرس. این افزونه به شما امکان می‌دهد متن جایگزین را به صورت خودکار تنظیم کنید، متن جایگزین قبلی را بازنشانی کنید یا از نام فایل به عنوان متن جایگزین استفاده کنید.
 * Version: 0.1
 * Author: Pr-Mir
 * Author URI: https://github.com/Scary-technologies
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: bulk-alt-text-updater
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// افزودن آیتم منو در مدیریت وردپرس
add_action('admin_menu', 'bulk_alt_text_updater_menu');
function bulk_alt_text_updater_menu() {
    add_menu_page('بروزرسانی دسته‌جمعی متن جایگزین', 'بروزرسانی متن جایگزین', 'manage_options', 'bulk-alt-text-updater', 'bulk_alt_text_updater_page', 'dashicons-edit', 20);
}

add_action('admin_enqueue_scripts', 'bulk_alt_text_updater_styles');
function bulk_alt_text_updater_styles() {
    wp_enqueue_style('bulk-alt-text-updater-styles', plugin_dir_url(__FILE__) . 'styles.css');
}

function bulk_alt_text_updater_page() {
    ?>
    <div class="wrap">
        <h1>بروزرسانی دسته‌جمعی متن جایگزین</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">متن جایگزین جدید:</th>
                    <td><input type="text" name="new_alt_text" value="" class="regular-text" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">انتخاب نوع رسانه:</th>
                    <td>
                        <label><input type="checkbox" name="media_types[]" value="image" checked> تصاویر</label>
                        <label><input type="checkbox" name="media_types[]" value="video"> ویدیوها</label>
                        <label><input type="checkbox" name="media_types[]" value="application"> اسناد</label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">بازنشانی متن جایگزین:</th>
                    <td><input type="checkbox" name="reset_alt_text" value="1"> بله</td>
                </tr>
                <tr valign="top">
                    <th scope="row">استفاده از نام فایل به عنوان متن جایگزین:</th>
                    <td><input type="checkbox" name="use_filename_as_alt" value="1"> بله</td>
                </tr>
            </table>
            <div class="submit-button">
                <?php submit_button('بروزرسانی متن جایگزین'); ?>
            </div>
        </form>
    </div>
    <?php

    if ((isset($_POST['new_alt_text']) || isset($_POST['reset_alt_text']) || isset($_POST['use_filename_as_alt'])) && isset($_POST['media_types'])) {
        $new_alt_text = sanitize_text_field($_POST['new_alt_text']);
        $media_types = array_map('sanitize_text_field', $_POST['media_types']);
        $reset_alt_text = isset($_POST['reset_alt_text']) ? true : false;
        $use_filename_as_alt = isset($_POST['use_filename_as_alt']) ? true : false;
        bulk_update_alt_text($new_alt_text, $media_types, $reset_alt_text, $use_filename_as_alt);
    }
}

function bulk_update_alt_text($new_alt_text, $media_types, $reset_alt_text, $use_filename_as_alt) {
    global $wpdb;

    $media_types_pattern = implode('|', $media_types);
    $attachments = $wpdb->get_results("SELECT ID, post_title FROM $wpdb->posts WHERE post_type = 'attachment' AND post_mime_type REGEXP '(" . $media_types_pattern . ")'");

    foreach ($attachments as $attachment) {
        if ($reset_alt_text) {
            delete_post_meta($attachment->ID, '_wp_attachment_image_alt');
        } elseif ($use_filename_as_alt) {
            $filename = pathinfo(get_attached_file($attachment->ID), PATHINFO_FILENAME);
            update_post_meta($attachment->ID, '_wp_attachment_image_alt', $filename);
        } else {
            update_post_meta($attachment->ID, '_wp_attachment_image_alt', $new_alt_text);
        }
    }

    echo '<div class="updated"><p>متن جایگزین تمامی رسانه‌ها با موفقیت بروزرسانی شد.</p></div>';
}
?>
