<?php

/**
 * Plugin Name: Image Alt Checker and Fixer
 * Plugin URI:  https://test.com/
 * Description: A plugin to find and fix missing or empty alt attributes on images using page titles, or fallback.
 * Version:     1.0.0
 * Author:      Maxim
 * Author URI:  https://test.com
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: image-alt-checker
 */

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

if (!class_exists('ImageAltChecker')) {
    class ImageAltChecker
    {
        public function __construct()
        {
            add_action('admin_menu', [$this, 'add_admin_menu']);
            add_action('admin_init', [$this, 'check_alt_images_action']);  // Проверка при инициализации
        }

        /**
         * Adds the admin menu page
         */
        public function add_admin_menu()
        {
            add_menu_page(
                __('Image Alt Checker', 'image-alt-checker'),
                __('Image Alt Checker', 'image-alt-checker'),
                'manage_options',
                'check_alt_images_page',
                [$this, 'render_admin_page'],
                'dashicons-format-image',
                99
            );
        }

        /**
         * Renders plugin admin page
         */
        public function render_admin_page()
        {
?>
            <div class="wrap">
                <h1><?php _e('Image Alt Checker', 'image-alt-checker'); ?></h1>
                <p><?php _e('To start the check, click on the button below.', 'image-alt-checker'); ?></p>
                <a href="<?php echo add_query_arg('check_alt_images', 'true'); ?>" class="button button-primary"><?php _e('Start Check', 'image-alt-checker'); ?></a>
            </div>
<?php
        }

        /**
         * Handles the check and update of image alt attributes
         */
        public function check_alt_images_action()
        {
            if (isset($_GET['check_alt_images']) && $_GET['check_alt_images'] === 'true') {
                $this->update_image_alts();
            }
        }

        /**
         * Updates alt attributes for all images
         */
        private function update_image_alts()
        {
            global $wpdb;

            // Получаем все изображения на сайте
            $images = $wpdb->get_results("
                SELECT ID, post_title
                FROM {$wpdb->posts}
                WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%'
            ");

            if (empty($images)) {
                echo '<p>' . __('No images to process.', 'image-alt-checker') . '</p>';
                return;
            }

            $total_images = count($images);
            $images_updated = 0;

            echo '<h2>' . __('Image Alt Attributes Analysis:', 'image-alt-checker') . '</h2>';
            echo '<p>' . __('Total images:', 'image-alt-checker') . ' ' . $total_images . '</p>';

            // Проходим по всем изображениям и обновляем alt
            foreach ($images as $image) {
                $alt_text = get_post_meta($image->ID, '_wp_attachment_image_alt', true);

                // Если alt пустой или отсутствует, обновляем его
                if (empty($alt_text)) {
                    $new_alt = $image->post_title; // Используем заголовок записи как alt
                    update_post_meta($image->ID, '_wp_attachment_image_alt', $new_alt);
                    $images_updated++;
                    if ($images_updated < 10) {
                        echo '<p>' . __('Updated Image ID:', 'image-alt-checker') . ' ' . $image->ID . ' - ' . __('Set alt to page title.', 'image-alt-checker') . '</p>';
                    }
                }
            }

            echo '<p>' . __('Total updated images:', 'image-alt-checker') . ' ' . $images_updated . '</p>';
        }
    }
}

if (! function_exists('image_alt_checker_plugin_init')) {
    function image_alt_checker_plugin_init()
    {
        // Инициализация плагина
        $image_alt_checker = new ImageAltChecker();
    }
    image_alt_checker_plugin_init();
}
?>