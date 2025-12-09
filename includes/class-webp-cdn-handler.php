<?php

class Webp_CDN_Handler {

    public function init() {
        add_filter('wp_get_attachment_url', [$this, 'replace_with_cdn_url'], 10, 2);
        add_filter('wp_calculate_image_srcset', [$this, 'replace_srcset_with_cdn'], 10, 5);
    }

    public function replace_with_cdn_url($url, $attachment_id) {
        $settings = get_option('webp_optimizer_settings', []);
        
        if (empty($settings['cdn_enabled']) || empty($settings['cdn_url'])) {
            return $url;
        }

        $cdn_url = rtrim($settings['cdn_url'], '/');
        $upload_dir = wp_upload_dir();
        $base_url = $upload_dir['baseurl'];

        return str_replace($base_url, $cdn_url, $url);
    }

    public function replace_srcset_with_cdn($sources, $size_array, $image_src, $image_meta, $attachment_id) {
        $settings = get_option('webp_optimizer_settings', []);
        
        if (empty($settings['cdn_enabled']) || empty($settings['cdn_url'])) {
            return $sources;
        }

        $cdn_url = rtrim($settings['cdn_url'], '/');
        $upload_dir = wp_upload_dir();
        $base_url = $upload_dir['baseurl'];

        foreach ($sources as &$source) {
            $source['url'] = str_replace($base_url, $cdn_url, $source['url']);
        }

        return $sources;
    }
}
