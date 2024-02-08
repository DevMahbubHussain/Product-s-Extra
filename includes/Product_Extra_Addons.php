<?php

class Product_Extra_Addons
{

    /**
     * Class constructor. Initializes the actions and hooks.
     */
    public function __construct()
    {
        add_action('init', array($this, 'init'));
    }

    /**
     * Initialize hooks and actions for the custom WooCommerce product options.
     *
     * @return void
     */
    public function init()
    {
        add_action('woocommerce_product_options_general_product_data', array($this, 'add_custom_product_fields'));
        add_action('woocommerce_process_product_meta', array($this, 'save_custom_product_fields'));
        add_action('woocommerce_before_add_to_cart_button', array($this, 'display_extra_product_options'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_custom_scripts'));
        add_filter('woocommerce_cart_item_price', array($this, 'modify_displayed_price'), 10, 3);
        add_action('woocommerce_before_calculate_totals', array($this, 'update_cart_item_prices'));
        add_filter('woocommerce_add_cart_item_data', array($this, 'add_custom_extra_product_field'), 10, 2);
    }

    /**
     * Enqueue custom styles and scripts for WooCommerce product pages.
     *
     * @return void
     */
    public function enqueue_custom_scripts()
    {
        if (is_product()) {
            wp_enqueue_style('woo-custom-addons-style', plugin_dir_url(__FILE__) . 'assets/css/woo-custom-addons-style.css');
            // Get custom product options
            $custom_product_options = $this->get_custom_product_options();
            wp_enqueue_script('woo-custom-addons-script', plugin_dir_url(__FILE__) . 'assets/js/woo-custom-addons-script.js', array('jquery'), '', true);
            // Localize script with parameters
            wp_localize_script('woo-custom-addons-script', 'custom_addons_params', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'customProductOptions' => $custom_product_options,
            ));
        }
    }

    /**
     * Get custom options for the current WooCommerce product.
     * @return array Associative array.
     */
    public function get_custom_product_options()
    {
        global $product;

        if (!$product instanceof WC_Product) {
            $product = wc_get_product(get_the_ID());
        }

        if (!$product instanceof WC_Product) {
            return array();
        }

        $custom_product_title = get_post_meta($product->get_id(), '_custom_extra_product_title', true);
        $custom_product_price = get_post_meta($product->get_id(), '_custom_extra_product_price', true);

        return array(
            'customProductTitle' => $custom_product_title,
            'customProductPrice' => floatval($custom_product_price),
        );
    }

    /**
     * Add custom fields to the WooCommerce product general options.
     *
     * @return void
     */
    public function add_custom_product_fields()
    {
        global $woocommerce, $post;
        echo '<div class="options_group">';

        woocommerce_wp_text_input(
            array(
                'id'          => '_custom_extra_product_title',
                'label'       => __('Extra Product Tile', 'woo-extra'),
                'placeholder' => __('Product Title', 'woo-extra'),
                'desc_tip'    => 'true',
                'description' => __('Enter the title for the extra product.', 'woo-extra'),
            )
        );
        woocommerce_wp_text_input(
            array(
                'id'          => '_custom_extra_product_price',
                'label'       => __('Addon Price ($)', 'woo-extra'),
                'placeholder' => __('Enter the price', 'woo-extra'),
                'type'        => 'text',
                'desc_tip'    => 'true',
                'description' => __('Enter the price for the Extra Product.', 'woo-extra'),
            )
        );
        echo '</div>';
    }

    /**
     * Save custom fields for the WooCommerce product.
     *
     * @return void
     */
    public function save_custom_product_fields($post_id)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $extra_product_title = isset($_POST['_custom_extra_product_title']) ? sanitize_text_field($_POST['_custom_extra_product_title']) : '';
        update_post_meta($post_id, '_custom_extra_product_title', $extra_product_title);

        $custom_product_price = isset($_POST['_custom_extra_product_price']) ? floatval($_POST['_custom_extra_product_price']) : '';
        update_post_meta($post_id, '_custom_extra_product_price', $custom_product_price);
    }

    /**
     * Display extra product options on the WooCommerce product page.
     *
     * @return void
     */
    public function display_extra_product_options()
    {
        global $product;

        $custom_product_title = get_post_meta($product->get_id(), '_custom_extra_product_title', true);
        $custom_product_price = get_post_meta($product->get_id(), '_custom_extra_product_price', true);

        echo '<div class="custom-addons">';
        echo '<label><input type="radio" name="wooaddon" value="' . esc_attr($custom_product_title) . '">' . esc_html($custom_product_title) . ' (+' . wc_price($custom_product_price) . ')</label><br>';
        echo '</div>';
    }

    /**
     * Modify the displayed price based on selected extra options in the cart.
     *
     * @return string Modified displayed price.
     */
    public function modify_displayed_price($price, $cart_item, $cart_item_key)
    {
        // Only on frontend and if price is not null
        if (is_admin() || '' === $price) {
            return $price;
        }
        // If extra option is selected, modify the displayed price
        $selected_option = isset($_POST['wooaddon']) ? sanitize_text_field($_POST['wooaddon']) : '';
        // var_dump($selected_option);

        if (!empty($selected_option)) {
            $custom_product_price_text = $cart_item['data']->get_meta('_custom_extra_product_price');
            // Convert the text value to a float
            $custom_product_price = floatval($custom_product_price_text);
            // Modify the displayed price
            $price = wc_price(wc_get_price_to_display($cart_item['data']) + $custom_product_price) . $cart_item['data']->get_price_suffix();
        }

        return $price;
    }

    /**
     * Update cart item prices based on selected custom options.
     *
     * @return void
     */
    public function update_cart_item_prices($cart)
    {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            // Check if the custom extra product price is selected
            if (isset($cart_item['custom_extra_product_selected']) && $cart_item['custom_extra_product_selected']) {
                $custom_product_price_text = $cart_item['data']->get_meta('_custom_extra_product_price');
                // Convert the text value to a float
                $custom_product_price = floatval($custom_product_price_text);
                // Set the new price for the cart item
                $new_price = $cart_item['data']->get_price() + $custom_product_price;
                // Update the cart item price
                $cart_item['data']->set_price($new_price);
            }
        }
    }

    /**
     * Add custom extra product field to cart item data based on user selection.
     *
     * @return array Modified cart item data.
     */
    public function add_custom_extra_product_field($cart_item_data, $product_id)
    {
        if (isset($_POST['wooaddon']) && !empty($_POST['wooaddon'])) {
            $cart_item_data['custom_extra_product_selected'] = true;
        }
        return $cart_item_data;
    }
}
