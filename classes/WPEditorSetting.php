<?php
class WPEditorSetting {

  /**
   * Get Settings
   *
   * Retrieves all plugin settings
   *
   * @since 1.0
   * @return array EDD settings
   */
  public static function get_settings() {

    $settings = get_option( 'edd_settings' );

    if( empty( $settings ) ) {

      // Update old settings with new single option

      $general_settings = is_array( get_option( 'edd_settings_general' ) )    ? get_option( 'edd_settings_general' )    : array();
      $gateway_settings = is_array( get_option( 'edd_settings_gateways' ) )   ? get_option( 'edd_settings_gateways' )   : array();
      $email_settings   = is_array( get_option( 'edd_settings_emails' ) )     ? get_option( 'edd_settings_emails' )     : array();
      $style_settings   = is_array( get_option( 'edd_settings_styles' ) )     ? get_option( 'edd_settings_styles' )     : array();
      $tax_settings     = is_array( get_option( 'edd_settings_taxes' ) )      ? get_option( 'edd_settings_taxes' )      : array();
      $ext_settings     = is_array( get_option( 'edd_settings_extensions' ) ) ? get_option( 'edd_settings_extensions' ) : array();
      $license_settings = is_array( get_option( 'edd_settings_licenses' ) )   ? get_option( 'edd_settings_licenses' )   : array();
      $misc_settings    = is_array( get_option( 'edd_settings_misc' ) )       ? get_option( 'edd_settings_misc' )       : array();

      $settings = array_merge( $general_settings, $gateway_settings, $email_settings, $style_settings, $tax_settings, $ext_settings, $license_settings, $misc_settings );

      update_option( 'edd_settings', $settings );

    }
    return apply_filters( 'edd_get_settings', $settings );
  }

  /**
   * Add all settings sections and fields
   *
   * @since 1.0
   * @return void
  */
  public static function register_settings() {

    if ( false == get_option( 'edd_settings' ) ) {
      add_option( 'edd_settings' );
    }

    foreach ( edd_get_registered_settings() as $tab => $sections ) {
      foreach ( $sections as $section => $settings) {

        // Check for backwards compatibility
        $section_tabs = edd_get_settings_tab_sections( $tab );
        if ( ! is_array( $section_tabs ) || ! array_key_exists( $section, $section_tabs ) ) {
          $section = 'main';
          $settings = $sections;
        }

        add_settings_section(
          'edd_settings_' . $tab . '_' . $section,
          __return_null(),
          '__return_false',
          'edd_settings_' . $tab . '_' . $section
        );

        foreach ( $settings as $option ) {
          // For backwards compatibility
          if ( empty( $option['id'] ) ) {
            continue;
          }

          $name = isset( $option['name'] ) ? $option['name'] : '';

          add_settings_field(
            'edd_settings[' . $option['id'] . ']',
            $name,
            function_exists( 'edd_' . $option['type'] . '_callback' ) ? 'edd_' . $option['type'] . '_callback' : 'edd_missing_callback',
            'edd_settings_' . $tab . '_' . $section,
            'edd_settings_' . $tab . '_' . $section,
            array(
              'section'     => $section,
              'id'          => isset( $option['id'] )          ? $option['id']          : null,
              'desc'        => ! empty( $option['desc'] )      ? $option['desc']        : '',
              'name'        => isset( $option['name'] )        ? $option['name']        : null,
              'size'        => isset( $option['size'] )        ? $option['size']        : null,
              'options'     => isset( $option['options'] )     ? $option['options']     : '',
              'std'         => isset( $option['std'] )         ? $option['std']         : '',
              'min'         => isset( $option['min'] )         ? $option['min']         : null,
              'max'         => isset( $option['max'] )         ? $option['max']         : null,
              'step'        => isset( $option['step'] )        ? $option['step']        : null,
              'chosen'      => isset( $option['chosen'] )      ? $option['chosen']      : null,
              'placeholder' => isset( $option['placeholder'] ) ? $option['placeholder'] : null,
              'allow_blank' => isset( $option['allow_blank'] ) ? $option['allow_blank'] : true,
              'readonly'    => isset( $option['readonly'] )    ? $option['readonly']    : false,
              'faux'        => isset( $option['faux'] )        ? $option['faux']        : false,
            )
          );
        }
      }

    }

    // Creates our settings in the options table
    register_setting( 'edd_settings', 'edd_settings', 'edd_settings_sanitize' );

  }

  /**
   * Retrieve the array of plugin settings
   *
   * @since 1.8
   * @return array
  */
  public static function get_registered_settings() {

    /**
     * 'Whitelisted' EDD settings, filters are provided for each settings
     * section to allow extensions and other plugins to add their own settings
     */
    $wpeditor_settings = array(
      /** General Settings */
      'general' => apply_filters( 'edd_settings_general',
        array(
          'main' => array(
            'page_settings' => array(
              'id'   => 'page_settings',
              'name' => '<h3>' . __( 'Page Settings', 'easy-digital-downloads' ) . '</h3>',
              'desc' => '',
              'type' => 'header',
            ),
            'locale_settings' => array(
              'id'   => 'locale_settings',
              'name' => '<h3>' . __( 'Store Location', 'easy-digital-downloads' ) . '</h3>',
              'desc' => '',
              'type' => 'header',
            ),
            'base_state' => array(
              'id'          => 'base_state',
              'name'        => __( 'Base State / Province', 'easy-digital-downloads' ),
              'desc'        => __( 'What state / province does your store operate from?', 'easy-digital-downloads' ),
              'type'        => 'shop_states',
              'chosen'      => true,
              'placeholder' => __( 'Select a state', 'easy-digital-downloads' ),
            ),
            'tracking_settings' => array(
              'id'   => 'tracking_settings',
              'name' => '<h3>' . __( 'Tracking Settings', 'easy-digital-downloads' ) . '</h3>',
              'desc' => '',
              'type' => 'header',
            ),
            'allow_tracking' => array(
              'id'   => 'allow_tracking',
              'name' => __( 'Allow Usage Tracking?', 'easy-digital-downloads' ),
              'desc' => sprintf(
                __( 'Allow Easy Digital Downloads to anonymously track how this plugin is used and help us make the plugin better. Opt-in to tracking and our newsletter and immediately be emailed a 20&#37; discount to the EDD shop, valid towards the <a href="%s" target="_blank">purchase of extensions</a>. No sensitive data is tracked.', 'easy-digital-downloads' ),
                'https://easydigitaldownloads.com/extensions?utm_source=' . substr( md5( get_bloginfo( 'name' ) ), 0, 10 ) . '&utm_medium=admin&utm_term=settings&utm_campaign=EDDUsageTracking'
              ),
              'type' => 'checkbox',
            ),
          ),
          'currency' => array(
            'currency_settings' => array(
              'id'   => 'currency_settings',
              'name' => '<h3>' . __( 'Currency Settings', 'easy-digital-downloads' ) . '</h3>',
              'desc' => '',
              'type' => 'header',
            ),
            'currency_position' => array(
              'id'      => 'currency_position',
              'name'    => __( 'Currency Position', 'easy-digital-downloads' ),
              'desc'    => __( 'Choose the location of the currency sign.', 'easy-digital-downloads' ),
              'type'    => 'select',
              'options' => array(
                'before' => __( 'Before - $10', 'easy-digital-downloads' ),
                'after'  => __( 'After - 10$', 'easy-digital-downloads' ),
              ),
            ),
            'thousands_separator' => array(
              'id'   => 'thousands_separator',
              'name' => __( 'Thousands Separator', 'easy-digital-downloads' ),
              'desc' => __( 'The symbol (usually , or .) to separate thousands', 'easy-digital-downloads' ),
              'type' => 'text',
              'size' => 'small',
              'std'  => ',',
            ),
            'decimal_separator' => array(
              'id'   => 'decimal_separator',
              'name' => __( 'Decimal Separator', 'easy-digital-downloads' ),
              'desc' => __( 'The symbol (usually , or .) to separate decimal points', 'easy-digital-downloads' ),
              'type' => 'text',
              'size' => 'small',
              'std'  => '.',
            ),
          ),
          'api' => array(
            'api_settings' => array(
              'id'   => 'api_settings',
              'name' => '<h3>' . __( 'API Settings', 'easy-digital-downloads' ) . '</h3>',
              'desc' => '',
              'type' => 'header',
            ),
            'api_allow_user_keys' => array(
              'id'   => 'api_allow_user_keys',
              'name' => __( 'Allow User Keys', 'easy-digital-downloads' ),
              'desc' => __( 'Check this box to allow all users to generate API keys. Users with the \'manage_shop_settings\' capability are always allowed to generate keys.', 'easy-digital-downloads' ),
              'type' => 'checkbox',
            ),
          ),
        )
      ),
      /** Payment Gateways Settings */
      'gateways' => apply_filters('edd_settings_gateways',
        array(
          'main' => array(
            'gateway_settings' => array(
              'id'   => 'api_header',
              'name' => '<h3>' . __( 'Gateway Settings', 'easy-digital-downloads' ) . '</h3>',
              'desc' => '',
              'type' => 'header',
            ),
            'test_mode' => array(
              'id'   => 'test_mode',
              'name' => __( 'Test Mode', 'easy-digital-downloads' ),
              'desc' => __( 'While in test mode no live transactions are processed. To fully use test mode, you must have a sandbox (test) account for the payment gateway you are testing.', 'easy-digital-downloads' ),
              'type' => 'checkbox',
            ),
            'accepted_cards' => array(
              'id'      => 'accepted_cards',
              'name'    => __( 'Accepted Payment Method Icons', 'easy-digital-downloads' ),
              'desc'    => __( 'Display icons for the selected payment methods', 'easy-digital-downloads' ) . '<br/>' . __( 'You will also need to configure your gateway settings if you are accepting credit cards', 'easy-digital-downloads' ),
              'type'    => 'payment_icons',
              'options' => apply_filters('edd_accepted_payment_icons', array(
                  'mastercard'      => 'Mastercard',
                  'visa'            => 'Visa',
                  'americanexpress' => 'American Express',
                  'discover'        => 'Discover',
                  'paypal'          => 'PayPal',
                )
              ),
            ),
          ),
          'paypal' => array(
            'paypal_settings' => array(
              'id'   => 'paypal_settings',
              'name' => '<h3>' . __( 'PayPal Standard Settings', 'easy-digital-downloads' ) . '</h3>',
              'type' => 'header',
            ),
            'paypal_email' => array(
              'id'   => 'paypal_email',
              'name' => __( 'PayPal Email', 'easy-digital-downloads' ),
              'desc' => __( 'Enter your PayPal account\'s email', 'easy-digital-downloads' ),
              'type' => 'text',
              'size' => 'regular',
            ),
            'paypal_page_style' => array(
              'id'   => 'paypal_page_style',
              'name' => __( 'PayPal Page Style', 'easy-digital-downloads' ),
              'desc' => __( 'Enter the name of the page style to use, or leave blank for default', 'easy-digital-downloads' ),
              'type' => 'text',
              'size' => 'regular',
            ),
            'disable_paypal_verification' => array(
              'id'   => 'disable_paypal_verification',
              'name' => __( 'Disable PayPal IPN Verification', 'easy-digital-downloads' ),
              'desc' => __( 'If payments are not getting marked as complete, then check this box. This forces the site to use a slightly less secure method of verifying purchases.', 'easy-digital-downloads' ),
              'type' => 'checkbox',
            ),
          ),
        )
      ),
      /** Emails Settings */
      'emails' => apply_filters('edd_settings_emails',
        array(
          'main' => array(
            'email_settings_header' => array(
              'id'   => 'email_settings_header',
              'name' => '<h3>' . __( 'Email Settings', 'easy-digital-downloads' ) . '</h3>',
              'type' => 'header',
            ),
            'email_logo' => array(
              'id'   => 'email_logo',
              'name' => __( 'Logo', 'easy-digital-downloads' ),
              'desc' => __( 'Upload or choose a logo to be displayed at the top of the purchase receipt emails. Displayed on HTML emails only.', 'easy-digital-downloads' ),
              'type' => 'upload',
            ),
            'email_settings' => array(
              'id'   => 'email_settings',
              'name' => '',
              'desc' => '',
              'type' => 'hook',
            ),
          ),
          'purchase_receipts' => array(
            'purchase_receipt_settings' => array(
              'id'   => 'purchase_receipt_settings',
              'name' => '<h3>' . __( 'Purchase Receipts', 'easy-digital-downloads' ) . '</h3>',
              'type' => 'header',
            ),
            'from_name' => array(
              'id'   => 'from_name',
              'name' => __( 'From Name', 'easy-digital-downloads' ),
              'desc' => __( 'The name purchase receipts are said to come from. This should probably be your site or shop name.', 'easy-digital-downloads' ),
              'type' => 'text',
              'std'  => get_bloginfo( 'name' ),
            ),
            'from_email' => array(
              'id'   => 'from_email',
              'name' => __( 'From Email', 'easy-digital-downloads' ),
              'desc' => __( 'Email to send purchase receipts from. This will act as the "from" and "reply-to" address.', 'easy-digital-downloads' ),
              'type' => 'text',
              'std'  => get_bloginfo( 'admin_email' ),
            ),
            'purchase_subject' => array(
              'id'   => 'purchase_subject',
              'name' => __( 'Purchase Email Subject', 'easy-digital-downloads' ),
              'desc' => __( 'Enter the subject line for the purchase receipt email', 'easy-digital-downloads' ),
              'type' => 'text',
              'std'  => __( 'Purchase Receipt', 'easy-digital-downloads' ),
            ),
            'purchase_heading' => array(
              'id'   => 'purchase_heading',
              'name' => __( 'Purchase Email Heading', 'easy-digital-downloads' ),
              'desc' => __( 'Enter the heading for the purchase receipt email', 'easy-digital-downloads' ),
              'type' => 'text',
              'std'  => __( 'Purchase Receipt', 'easy-digital-downloads' ),
            ),
          ),
          'sale_notifications' => array(
            'sale_notification_settings' => array(
              'id'   => 'sale_notification_settings',
              'name' => '<h3>' . __( 'Sale Notifications', 'easy-digital-downloads' ) . '</h3>',
              'type' => 'header',
            ),
            'sale_notification_subject' => array(
              'id'   => 'sale_notification_subject',
              'name' => __( 'Sale Notification Subject', 'easy-digital-downloads' ),
              'desc' => __( 'Enter the subject line for the sale notification email', 'easy-digital-downloads' ),
              'type' => 'text',
              'std'  => 'New download purchase - Order #{payment_id}',
            ),
            'admin_notice_emails' => array(
              'id'   => 'admin_notice_emails',
              'name' => __( 'Sale Notification Emails', 'easy-digital-downloads' ),
              'desc' => __( 'Enter the email address(es) that should receive a notification anytime a sale is made, one per line', 'easy-digital-downloads' ),
              'type' => 'textarea',
              'std'  => get_bloginfo( 'admin_email' ),
            ),
            'disable_admin_notices' => array(
              'id'   => 'disable_admin_notices',
              'name' => __( 'Disable Admin Notifications', 'easy-digital-downloads' ),
              'desc' => __( 'Check this box if you do not want to receive sales notification emails.', 'easy-digital-downloads' ),
              'type' => 'checkbox',
            ),
          ),
        )
      ),
      /** Styles Settings */
      'styles' => apply_filters('edd_settings_styles',
        array(
          'main' => array(
            'style_settings' => array(
              'id'   => 'style_settings',
              'name' => '<h3>' . __( 'Style Settings', 'easy-digital-downloads' ) . '</h3>',
              'type' => 'header',
            ),
            'disable_styles' => array(
              'id'   => 'disable_styles',
              'name' => __( 'Disable Styles', 'easy-digital-downloads' ),
              'desc' => __( 'Check this to disable all included styling of buttons, checkout fields, and all other elements.', 'easy-digital-downloads' ),
              'type' => 'checkbox',
            ),
            'button_header' => array(
              'id'   => 'button_header',
              'name' => '<strong>' . __( 'Buttons', 'easy-digital-downloads' ) . '</strong>',
              'desc' => __( 'Options for add to cart and purchase buttons', 'easy-digital-downloads' ),
              'type' => 'header',
            ),
          ),
        )
      ),
      /** Taxes Settings */
      'taxes' => apply_filters('edd_settings_taxes',
        array(
          'main' => array(
            'tax_settings' => array(
              'id'   => 'tax_settings',
              'name' => '<h3>' . __( 'Tax Settings', 'easy-digital-downloads' ) . '</h3>',
              'type' => 'header',
            ),
            'enable_taxes' => array(
              'id'   => 'enable_taxes',
              'name' => __( 'Enable Taxes', 'easy-digital-downloads' ),
              'desc' => __( 'Check this to enable taxes on purchases.', 'easy-digital-downloads' ),
              'type' => 'checkbox',
            ),
            'tax_rates' => array(
              'id'   => 'tax_rates',
              'name' => '<strong>' . __( 'Tax Rates', 'easy-digital-downloads' ) . '</strong>',
              'desc' => __( 'Enter tax rates for specific regions.', 'easy-digital-downloads' ),
              'type' => 'tax_rates',
            ),
            'tax_rate' => array(
              'id'   => 'tax_rate',
              'name' => __( 'Fallback Tax Rate', 'easy-digital-downloads' ),
              'desc' => __( 'Enter a percentage, such as 6.5. Customers not in a specific rate will be charged this rate.', 'easy-digital-downloads' ),
              'type' => 'text',
              'size' => 'small',
            ),
            'prices_include_tax' => array(
              'id'   => 'prices_include_tax',
              'name' => __( 'Prices entered with tax', 'easy-digital-downloads' ),
              'desc' => __( 'This option affects how you enter prices.', 'easy-digital-downloads' ),
              'type' => 'radio',
              'std'  => 'no',
              'options' => array(
                'yes' => __( 'Yes, I will enter prices inclusive of tax', 'easy-digital-downloads' ),
                'no'  => __( 'No, I will enter prices exclusive of tax', 'easy-digital-downloads' ),
              ),
            ),
            'display_tax_rate' => array(
              'id'   => 'display_tax_rate',
              'name' => __( 'Display Tax Rate on Prices', 'easy-digital-downloads' ),
              'desc' => __( 'Some countries require a notice when product prices include tax.', 'easy-digital-downloads' ),
              'type' => 'checkbox',
            ),
            'checkout_include_tax' => array(
              'id'   => 'checkout_include_tax',
              'name' => __( 'Display during checkout', 'easy-digital-downloads' ),
              'desc' => __( 'Should prices on the checkout page be shown with or without tax?', 'easy-digital-downloads' ),
              'type' => 'select',
              'std'  => 'no',
              'options' => array(
                'yes' => __( 'Including tax', 'easy-digital-downloads' ),
                'no'  => __( 'Excluding tax', 'easy-digital-downloads' ),
              ),
            ),
          ),
        )
      ),
      /** Extension Settings */
      'extensions' => apply_filters('edd_settings_extensions',
        array()
      ),
      'licenses' => apply_filters('edd_settings_licenses',
        array()
      ),
      /** Misc Settings */
      'misc' => apply_filters('edd_settings_misc',
        array(
          'main' => array(
            'misc_settings' => array(
              'id'   => 'misc_settings',
              'name' => '<h3>' . __( 'Misc Settings', 'easy-digital-downloads' ) . '</h3>',
              'type' => 'header',
            ),
            'enable_ajax_cart' => array(
              'id'   => 'enable_ajax_cart',
              'name' => __( 'Enable Ajax', 'easy-digital-downloads' ),
              'desc' => __( 'Check this to enable AJAX for the shopping cart.', 'easy-digital-downloads' ),
              'type' => 'checkbox',
              'std'  => '1',
            ),
            'redirect_on_add' => array(
              'id'   => 'redirect_on_add',
              'name' => __( 'Redirect to Checkout', 'easy-digital-downloads' ),
              'desc' => __( 'Immediately redirect to checkout after adding an item to the cart?', 'easy-digital-downloads' ),
              'type' => 'checkbox',
            ),
            'item_quantities' => array(
              'id'   => 'item_quantities',
              'name' => __('Item Quantities','easy-digital-downloads' ),
              'desc' => __('Allow item quantities to be changed.','easy-digital-downloads' ),
              'type' => 'checkbox',
            ),
            'uninstall_on_delete' => array(
              'id'   => 'uninstall_on_delete',
              'name' => __( 'Remove Data on Uninstall?', 'easy-digital-downloads' ),
              'desc' => __( 'Check this box if you would like EDD to completely remove all of its data when the plugin is deleted.', 'easy-digital-downloads' ),
              'type' => 'checkbox',
            ),
          ),
          'checkout' => array(
            'checkout_settings' => array(
              'id'   => 'checkout_settings',
              'name' => '<h3>' . __( 'Checkout Settings', 'easy-digital-downloads' ) . '</h3>',
              'type' => 'header',
            ),
            'enforce_ssl' => array(
              'id'   => 'enforce_ssl',
              'name' => __( 'Enforce SSL on Checkout', 'easy-digital-downloads' ),
              'desc' => __( 'Check this to force users to be redirected to the secure checkout page. You must have an SSL certificate installed to use this option.', 'easy-digital-downloads' ),
              'type' => 'checkbox',
            ),
            'logged_in_only' => array(
              'id'   => 'logged_in_only',
              'name' => __( 'Disable Guest Checkout', 'easy-digital-downloads' ),
              'desc' => __( 'Require that users be logged-in to purchase files.', 'easy-digital-downloads' ),
              'type' => 'checkbox',
            ),
            'show_register_form' => array(
              'id'      => 'show_register_form',
              'name'    => __( 'Show Register / Login Form?', 'easy-digital-downloads' ),
              'desc'    => __( 'Display the registration and login forms on the checkout page for non-logged-in users.', 'easy-digital-downloads' ),
              'type'    => 'select',
              'std'     => 'none',
              'options' => array(
                'both'         => __( 'Registration and Login Forms', 'easy-digital-downloads' ),
                'registration' => __( 'Registration Form Only', 'easy-digital-downloads' ),
                'login'        => __( 'Login Form Only', 'easy-digital-downloads' ),
                'none'         => __( 'None', 'easy-digital-downloads' ),
              ),
            ),
            'allow_multiple_discounts' => array(
              'id'   => 'allow_multiple_discounts',
              'name' => __('Multiple Discounts','easy-digital-downloads' ),
              'desc' => __('Allow customers to use multiple discounts on the same purchase?','easy-digital-downloads' ),
              'type' => 'checkbox',
            ),
            'enable_cart_saving' => array(
              'id'   => 'enable_cart_saving',
              'name' => __( 'Enable Cart Saving', 'easy-digital-downloads' ),
              'desc' => __( 'Check this to enable cart saving on the checkout.', 'easy-digital-downloads' ),
              'type' => 'checkbox',
            ),
          ),
          'button_text' => array(
            'button_settings' => array(
              'id'   => 'button_settings',
              'name' => '<h3>' . __( 'Button Text', 'easy-digital-downloads' ) . '</h3>',
              'type' => 'header',
            ),
            'checkout_label' => array(
              'id'   => 'checkout_label',
              'name' => __( 'Complete Purchase Text', 'easy-digital-downloads' ),
              'desc' => __( 'The button label for completing a purchase.', 'easy-digital-downloads' ),
              'type' => 'text',
              'std'  => __( 'Purchase', 'easy-digital-downloads' ),
            ),
            'add_to_cart_text' => array(
              'id'   => 'add_to_cart_text',
              'name' => __( 'Add to Cart Text', 'easy-digital-downloads' ),
              'desc' => __( 'Text shown on the Add to Cart Buttons.', 'easy-digital-downloads' ),
              'type' => 'text',
              'std'  => __( 'Add to Cart', 'easy-digital-downloads' ),
            ),
            'buy_now_text' => array(
              'id'   => 'buy_now_text',
              'name' => __( 'Buy Now Text', 'easy-digital-downloads' ),
              'desc' => __( 'Text shown on the Buy Now Buttons.', 'easy-digital-downloads' ),
              'type' => 'text',
              'std'  => __( 'Buy Now', 'easy-digital-downloads' ),
            ),
          ),
          'file_downloads' => array(
            'file_settings' => array(
              'id'   => 'file_settings',
              'name' => '<h3>' . __( 'File Download Settings', 'easy-digital-downloads' ) . '</h3>',
              'type' => 'header',
            ),
            'symlink_file_downloads' => array(
              'id'   => 'symlink_file_downloads',
              'name' => __( 'Symlink File Downloads?', 'easy-digital-downloads' ),
              'desc' => __( 'Check this if you are delivering really large files or having problems with file downloads completing.', 'easy-digital-downloads' ),
              'type' => 'checkbox',
            ),
            'download_link_expiration' => array(
              'id'   => 'download_link_expiration',
              'name' => __( 'Download Link Expiration', 'easy-digital-downloads' ),
              'desc' => __( 'How long should download links be valid for? Default is 24 hours from the time they are generated. Enter a time in hours.', 'easy-digital-downloads' ),
              'type' => 'number',
              'size' => 'small',
              'std'  => '24',
              'min'  => '0',
            ),
            'disable_redownload' => array(
              'id'   => 'disable_redownload',
              'name' => __( 'Disable Redownload?', 'easy-digital-downloads' ),
              'desc' => __( 'Check this if you do not want to allow users to redownload items from their purchase history.', 'easy-digital-downloads' ),
              'type' => 'checkbox',
            ),
          ),
          'accounting'     => array(
            'accounting_settings' => array(
              'id'   => 'accounting_settings',
              'name' => '<h3>' . __( 'Accounting Settings', 'easy-digital-downloads' ) . '</h3>',
              'type' => 'header',
            ),
            'enable_skus' => array(
              'id'   => 'enable_skus',
              'name' => __( 'Enable SKU Entry', 'easy-digital-downloads' ),
              'desc' => __( 'Check this box to allow entry of product SKUs. SKUs will be shown on purchase receipt and exported purchase histories.', 'easy-digital-downloads' ),
              'type' => 'checkbox',
            ),
            'enable_sequential' => array(
              'id'   => 'enable_sequential',
              'name' => __( 'Sequential Order Numbers', 'easy-digital-downloads' ),
              'desc' => __( 'Check this box to enable sequential order numbers.', 'easy-digital-downloads' ),
              'type' => 'checkbox',
            ),
            'sequential_start' => array(
              'id'   => 'sequential_start',
              'name' => __( 'Sequential Starting Number', 'easy-digital-downloads' ),
              'desc' => __( 'The number at which the sequence should begin.', 'easy-digital-downloads' ),
              'type' => 'number',
              'size' => 'small',
              'std'  => '1',
            ),
            'sequential_prefix' => array(
              'id'   => 'sequential_prefix',
              'name' => __( 'Sequential Number Prefix', 'easy-digital-downloads' ),
              'desc' => __( 'A prefix to prepend to all sequential order numbers.', 'easy-digital-downloads' ),
              'type' => 'text',
            ),
            'sequential_postfix' => array(
              'id'   => 'sequential_postfix',
              'name' => __( 'Sequential Number Postfix', 'easy-digital-downloads' ),
              'desc' => __( 'A postfix to append to all sequential order numbers.', 'easy-digital-downloads' ),
              'type' => 'text',
            ),
          ),
          'site_terms'     => array(
            'terms_settings' => array(
              'id'   => 'terms_settings',
              'name' => '<h3>' . __( 'Agreement Settings', 'easy-digital-downloads' ) . '</h3>',
              'type' => 'header',
            ),
            'show_agree_to_terms' => array(
              'id'   => 'show_agree_to_terms',
              'name' => __( 'Agree to Terms', 'easy-digital-downloads' ),
              'desc' => __( 'Check this to show an agree to terms on the checkout that users must agree to before purchasing.', 'easy-digital-downloads' ),
              'type' => 'checkbox',
            ),
            'agree_label' => array(
              'id'   => 'agree_label',
              'name' => __( 'Agree to Terms Label', 'easy-digital-downloads' ),
              'desc' => __( 'Label shown next to the agree to terms check box.', 'easy-digital-downloads' ),
              'type' => 'text',
              'size' => 'regular',
            ),
            'agree_text' => array(
              'id'   => 'agree_text',
              'name' => __( 'Agreement Text', 'easy-digital-downloads' ),
              'desc' => __( 'If Agree to Terms is checked, enter the agreement terms here.', 'easy-digital-downloads' ),
              'type' => 'rich_editor',
            ),
          ),
        )
      )
    );

    return apply_filters( 'edd_registered_settings', $wpeditor_settings );
  }

  /**
   * Retrieve settings tabs
   *
   * @since 1.8
   * @return array $tabs
   */
  public static function get_settings_tabs() {

    $settings = self::get_registered_settings();

    $tabs             = array();
    $tabs['general']  = __( 'General', 'easy-digital-downloads' );
    $tabs['gateways'] = __( 'Payment Gateways', 'easy-digital-downloads' );
    $tabs['emails']   = __( 'Emails', 'easy-digital-downloads' );
    $tabs['styles']   = __( 'Styles', 'easy-digital-downloads' );
    $tabs['taxes']    = __( 'Taxes', 'easy-digital-downloads' );

    if( ! empty( $settings['extensions'] ) ) {
      $tabs['extensions'] = __( 'Extensions', 'easy-digital-downloads' );
    }
    if( ! empty( $settings['licenses'] ) ) {
      $tabs['licenses'] = __( 'Licenses', 'easy-digital-downloads' );
    }

    $tabs['misc']      = __( 'Misc', 'easy-digital-downloads' );

    return apply_filters( 'edd_settings_tabs', $tabs );
  }

  /**
   * Retrieve settings tabs
   *
   * @since 2.5
   * @return array $section
   */
  public static function get_settings_tab_sections( $tab = false ) {

    $tabs     = false;
    $sections = WPEditorSetting::get_registered_settings_sections();

    if( $tab && ! empty( $sections[ $tab ] ) ) {
      $tabs = $sections[ $tab ];
    } else if ( $tab ) {
      $tabs = false;
    }

    return $tabs;
  }

  /**
   * Get the settings sections for each tab
   * Uses a static to avoid running the filters on every request to this function
   *
   * @since  2.5
   * @return array Array of tabs and sections
   */
  public static function get_registered_settings_sections() {

    static $sections = false;

    if ( false !== $sections ) {
      return $sections;
    }

    $sections = array(
      'general'    => apply_filters( 'edd_settings_sections_general', array(
        'main'               => __( 'General Settings', 'easy-digital-downloads' ),
        'currency'           => __( 'Currency Settings', 'easy-digital-downloads' ),
        'api'                => __( 'API Settings', 'easy-digital-downloads' ),
      ) ),
      'gateways'   => apply_filters( 'edd_settings_sections_gateways', array(
        'main'               => __( 'Gateway Settings', 'easy-digital-downloads' ),
        'paypal'             => __( 'PayPal Standard', 'easy-digital-downloads' ),
      ) ),
      'emails'     => apply_filters( 'edd_settings_sections_emails', array(
        'main'               => __( 'Email Settings', 'easy-digital-downloads' ),
        'purchase_receipts'  => __( 'Purchase Receipts', 'easy-digital-downloads' ),
        'sale_notifications' => __( 'New Sale Notifications', 'easy-digital-downloads' ),
      ) ),
      'styles'     => apply_filters( 'edd_settings_sections_styles', array(
        'main'               => __( 'Style Settings', 'easy-digital-downloads' ),
      ) ),
      'taxes'      => apply_filters( 'edd_settings_sections_taxes', array(
        'main'               => __( 'Tax Settings', 'easy-digital-downloads' ),
      ) ),
      'extensions' => apply_filters( 'edd_settings_sections_extensions', array(
        'main'               => __( 'Main', 'easy-digital-downloads' )
      ) ),
      'licenses'   => apply_filters( 'edd_settings_sections_licenses', array() ),
      'misc'       => apply_filters( 'edd_settings_sections_misc', array(
        'main'               => __( 'Misc Settings', 'easy-digital-downloads' ),
        'checkout'           => __( 'Checkout Settings', 'easy-digital-downloads' ),
        'button_text'        => __( 'Button Text', 'easy-digital-downloads' ),
        'file_downloads'     => __( 'File Downloads', 'easy-digital-downloads' ),
        'accounting'         => __( 'Accounting Settings', 'easy-digital-downloads' ),
        'site_terms'         => __( 'Terms of Agreement', 'easy-digital-downloads' ),
      ) ),
    );

    $sections = apply_filters( 'edd_settings_sections', $sections );

    return $sections;
  }


  
  public static function set_value( $key, $value ) {
    global $wpdb;
    $settings_table = WPEditor::get_table_name( 'settings' );
    
    if ( ! empty( $key ) ) {
      $db_key = $wpdb->get_var( "SELECT `key` from $settings_table where `key`='$key'" );
      if ( $db_key ) {
        if ( ! empty( $value ) || $value !== 0 ) {
          $wpdb->update( $settings_table, 
            array( 'key'=>$key, 'value'=>$value ),
            array( 'key'=>$key ),
            array( '%s', '%s' ),
            array( '%s' )
          );
        }
        else {
          $wpdb->query( "DELETE from $settings_table where `key`='$key'" );
        }
      }
      else {
        if ( !empty( $value ) || $value !== 0 ) {
          $wpdb->insert( $settings_table, 
            array( 'key'=>$key, 'value'=>$value ),
            array( '%s', '%s' )
          );
        }
      }
    }
    
  }
  
  public static function get_value( $key, $entities=false ) {
    $value = false;
    global $wpdb;
    $settings_table = WPEditor::get_table_name( 'settings' );
    $value = $wpdb->get_var( "SELECT `value` from $settings_table where `key`='$key'" );
    
    if(!empty( $value ) && $entities ) {
      $value = htmlentities( $value );
    }
    
    return $value;
  }
  
}