<?php

/*
 * Plugin Name: OS newsletter sync-mailchimp AND PODS
 * Plugin URI: bibliotecadeterminus.xyz
 * Description: Añade registro de newsletter con sincronizacion mailchimp y pods
 * Version: 1.0.0
 * Author: Oscar Sanchez
 * Author URI: bibliotecadeterminus.xyz
 * Requires at least: 4.0
 * Tested up to: 4.3
 *
 * Text Domain: wpos-additional
 * Domain Path: /languages/
 */

include "Repository/PodsNewsletterRepository.php";
include "Model/MailChimpIntegration.php";

add_action('wp_ajax_nopriv_newsletter__register', 'newsletter__register');
add_action('wp_ajax_newsletter__register', 'newsletter__register');
add_action('admin_menu', 'newsletter_plugin_create_menu');

function newsletter_plugin_create_menu() {

    //create new top-level menu
    add_menu_page('MAILCHIMP API', 'Mailchimp', 'administrator', __FILE__, 'os_newsletter_options' , plugins_url('/images/icon.png', __FILE__) );

    //call register settings function
    add_action( 'admin_init', 'register_options_newsletter' );
}


function register_options_newsletter() {
    //register our settings
    register_setting( 'os-mailchimp_integration_api', 'os_mailchimp_api' );
    register_setting( 'os-mailchimp_integration_api', 'os_mailchimp_list' );
    register_setting( 'os-mailchimp_integration_api' ,'os_mailchimp_dg' );
    register_setting( 'os-mailchimp_integration_api' ,'os_register_pods' );
}

function os_newsletter_options() {
    ?>
    <div class="wrap">
        <h1>Mailchimp Integration</h1>

        <form method="post" action="options.php">
            <?php settings_fields( 'os-mailchimp_integration_api' ); ?>
            <?php do_settings_sections( 'os-mailchimp_integration_api' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Mailchimp API Key</th>
                    <td>
                        <input type="text" name="os_mailchimp_api" value="<?php echo esc_attr( get_option('os_mailchimp_api') ); ?>" />
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Mailchimp list Key</th>
                    <td><input type="text" name="os_mailchimp_list" value="<?php echo esc_attr( get_option('os_mailchimp_list') ); ?>" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Mailchimp DG.</th>
                    <td><input type="text" name="os_mailchimp_dg" value="<?php echo esc_attr( get_option('os_mailchimp_dg') ); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Active save to pods.</th>
                   <td>
                       <input type="checkbox" name="os_register_pods" id="cbox1" value="1" <?php if (get_option('os_register_pods')):  ?> checked <?php endif ?> >
                   </td>
                </tr>
            </table>

            <?php submit_button(); ?>

        </form>
    </div>
<?php }

/**
 * @throws \Predis\ClientException
 */
function newsletter__register() {

    $email = strip_tags($_POST['email']);
    $accept = strip_tags($_POST['accept']);

    if(!isset($email)) {

        wp_send_json(['message' => __('Error invalid email', 'wpduf')], 200);

    }

    if(!isset($accept)) {

        wp_send_json(['message' => __('Debes aceptar las condiciones', 'wpduf'),'status' =>'error'], 500);

    }

    if(!is_email($email)) {
        wp_send_json(['message' => __('Error') , 'status' =>'error'], 200);
    }

    if(get_option('os_register_pods')) {
        $podsNewsletterRepository = new PodsNewsletterRepository();


        if(!is_email($email)) {
            wp_send_json(['message' => __('Error') , 'status' =>'error'], 200);
        }
        if($podsNewsletterRepository->existSubscriber($email)) {

            wp_send_json(['message' => __('Email is in List Subscriber') , 'status' =>'error'], 200);
        }

        if($podsNewsletterRepository->existSubscriber($accept)) {

            wp_send_json(['message' => __('You need accept the terms') , 'status' =>'error'], 200);
        }
        $podsNewsletterRepository->addSubscriber($email);
    }

    $mailChimpIntegration = new MailChimpIntegration(get_option('os_mailchimp_list'), get_option('os_mailchimp_dg'),get_option('os_mailchimp_api'));

    if($mailChimpIntegration->addSubscriber($email)) {

        wp_send_json(['message' => __('User add correctly'),'status' =>'ok'],200);
    }

    wp_send_json(['message' => __('User in list'),'status' =>'error'],200);

}