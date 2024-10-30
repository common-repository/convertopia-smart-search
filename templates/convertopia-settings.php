<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$convertopiaAdminNotices = new ConvertopiaAdminNotices();
if (isset($_POST['cp_settings'])){

    // Verify the nonce
    if (!isset($_POST['convertopia_settings_nonce']) || 
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['convertopia_settings_nonce'])), 'convertopia_settings_action')) {
        
        // If nonce verification fails, add an admin error notice and stop processing
        $convertopiaAdminNotices->error("Nonce verification failed. Please try again.");
        return; // Stop further processing if nonce verification fails
    }

    $convertopia_settings = array();
    $convertopia_settings['ftp_host'] = isset($_POST['ftp_host']) ? sanitize_text_field(wp_unslash($_POST['ftp_host'])) : '';
    $convertopia_settings['ftp_user'] = isset($_POST['ftp_user']) ? sanitize_text_field(wp_unslash($_POST['ftp_user'])) : '';
    $convertopia_settings['ftp_password'] = isset($_POST['ftp_password']) ? sanitize_text_field(wp_unslash($_POST['ftp_password'])) : '';
    $convertopia_settings['ftp_port'] = isset($_POST['ftp_port']) ? sanitize_text_field(wp_unslash($_POST['ftp_port'])) : '';
    $convertopia_settings['ftp_path'] = isset($_POST['ftp_path']) ? sanitize_text_field(wp_unslash($_POST['ftp_path'])) : '';
    $convertopia_settings['cp_store_id'] = isset($_POST['cp_store_id']) ? sanitize_text_field(wp_unslash($_POST['cp_store_id'])) : '';
    $convertopia_settings['cp_client_key'] = isset($_POST['cp_client_key']) ? sanitize_text_field(wp_unslash($_POST['cp_client_key'])) : '';
    $convertopia_settings['cp_secret_key'] = isset($_POST['cp_secret_key']) ? sanitize_text_field(wp_unslash($_POST['cp_secret_key'])) : '';
    $convertopia_settings['cdn_URL'] = isset($_POST['cdn_URL']) ? esc_url_raw(wp_unslash($_POST['cdn_URL'])) : '';
    $convertopia_settings['service_URL'] = isset($_POST['service_URL']) ? esc_url_raw(wp_unslash($_POST['service_URL'])) : '';

    if (get_option('convertopia_settings')) {
        update_option('convertopia_settings', $convertopia_settings);
        $convertopiaAdminNotices->success("Settings updated successfully!");
    } else {
        add_option('convertopia_settings', $convertopia_settings);
        $convertopiaAdminNotices->success("Settings saved successfully!");
    }
}
$convertopia_settings = get_option('convertopia_settings');

// Ensure $convertopia_settings is an array
if (!is_array($convertopia_settings)) {
    $convertopia_settings = array();
}

?>
<div class="wrap">
    <h1 style="padding: 15px;border-left: 7px solid #007cba;line-height: 0.1;"><?php echo esc_html__('Convertopia Settings', 'convertopia-smart-search'); ?></h1>
    <div style="display:block;">
        <div style='background-color:white;padding:25px 25px 65px 25px ;margin:10px;border-radius:10px;box-shadow: 0px 1px 7px #e1dada;'>
            <div class="wrap-content">
                <form method="post" action="">
                    <?php
                        // Generate a nonce field
                        wp_nonce_field('convertopia_settings_action', 'convertopia_settings_nonce');
                    ?>
                    <input name="cp_settings" type="hidden" />

                    <h2 style="padding: 15px;border-left: 7px solid #007cba;line-height: 0.1;"><?php echo esc_html__('FTP Settings', 'convertopia-smart-search'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th>
                                <label for="ftp_host"><?php echo esc_html__('FTP Host:', 'convertopia-smart-search'); ?></label>
                                <span>*</span>
                            </th>
                            <td>
                                <input type="text" name="ftp_host" class="regular-text" required value="<?php echo esc_attr(isset($convertopia_settings['ftp_host']) ? $convertopia_settings['ftp_host'] : ''); ?>" />
                                <p class="description" id="home-description"><?php echo esc_html__('Your FTP Host', 'convertopia-smart-search'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="ftp_user"><?php echo esc_html__('FTP Username:', 'convertopia-smart-search'); ?></label>
                                <span>*</span>
                            </th>
                            <td>
                                <input type="text" name="ftp_user" class="regular-text" required value="<?php echo esc_attr(isset($convertopia_settings['ftp_user']) ? $convertopia_settings['ftp_user'] : ''); ?>" />
                                <p class="description" id="home-description"><?php echo esc_html__('Your FTP Username', 'convertopia-smart-search'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="ftp_user"><?php echo esc_html__('FTP Password:', 'convertopia-smart-search'); ?></label>
                                <span>*</span>
                            </th>
                            <td>
                                <input type="password" name="ftp_password" required class="regular-text" value="<?php echo esc_attr(isset($convertopia_settings['ftp_password']) ? $convertopia_settings['ftp_password'] : ''); ?>" />
                                <p class="description" id="home-description"><?php echo esc_html__('Your FTP Password', 'convertopia-smart-search'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="ftp_port"><?php echo esc_html__('FTP Port:', 'convertopia-smart-search'); ?></label>
                                <span>*</span>
                            </th>
                            <td>
                                <input type="text" name="ftp_port" class="regular-text" required value="<?php echo esc_attr(isset($convertopia_settings['ftp_port']) ? $convertopia_settings['ftp_port'] : ''); ?>" />
                                <p class="description" id="home-description"><?php echo esc_html__('Your FTP Port', 'convertopia-smart-search'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="ftp_path"><?php echo esc_html__('FTP Path:', 'convertopia-smart-search'); ?></label>
                                <span>*</span>
                            </th>
                            <td>
                                <input type="text" name="ftp_path" class="regular-text" required value="<?php echo esc_attr(isset($convertopia_settings['ftp_path']) ? $convertopia_settings['ftp_path'] : ''); ?>" />
                                <p class="description" id="home-description"><?php echo esc_html__('Your FTP Path', 'convertopia-smart-search'); ?></p>
                            </td>
                        </tr>
                    </table>

                    <h2 style="padding: 15px;border-left: 7px solid #007cba;line-height: 0.1;"><?php echo esc_html__('Convertopia Store Settings', 'convertopia-smart-search'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th>
                                <label for="cp_store_id"><?php echo esc_html__('Store ID:', 'convertopia-smart-search'); ?></label>
                                <span>*</span>
                            </th>
                            <td>
                                <input type="text" name="cp_store_id" class="regular-text" required value="<?php echo esc_attr(isset($convertopia_settings['cp_store_id']) ? $convertopia_settings['cp_store_id'] : ''); ?>" />
                                <p class="description" id="home-description"><?php echo esc_html__('Your Convertopia Store Id', 'convertopia-smart-search'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="cp_client_key"><?php echo esc_html__('Client Key:', 'convertopia-smart-search'); ?></label>
                                <span>*</span>
                            </th>
                            <td>
                                <input type="text" name="cp_client_key" class="regular-text" required value="<?php echo esc_attr(isset($convertopia_settings['cp_client_key']) ? $convertopia_settings['cp_client_key'] : ''); ?>" />
                                <p class="description" id="home-description"><?php echo esc_html__('Your Convertopia Client Key', 'convertopia-smart-search'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="cp_secret_key"><?php echo esc_html__('Client Secret Key:', 'convertopia-smart-search'); ?></label>
                                <span>*</span>
                            </th>
                            <td>
                                <input type="text" name="cp_secret_key" class="regular-text" required value="<?php echo esc_attr(isset($convertopia_settings['cp_secret_key']) ? $convertopia_settings['cp_secret_key'] : ''); ?>" />
                                <p class="description" id="home-description"><?php echo esc_html__('Your Convertopia Client Secret Key', 'convertopia-smart-search'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="cdn_URL"><?php echo esc_html__('CDN URL:', 'convertopia-smart-search'); ?></label>
                                <span>*</span>
                            </th>
                            <td>
                                <input type="text" name="cdn_URL" class="regular-text" required value="<?php echo esc_attr(isset($convertopia_settings['cdn_URL']) ? $convertopia_settings['cdn_URL'] : ''); ?>" />
                                <p class="description" id="home-description"><?php echo esc_html__('Your Convertopia CDN URL', 'convertopia-smart-search'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="service_URL"><?php echo esc_html__('Service URL:', 'convertopia-smart-search'); ?></label>
                                <span>*</span>
                            </th>
                            <td>
                                <input type="text" name="service_URL" class="regular-text" required value="<?php echo esc_attr(isset($convertopia_settings['service_URL']) ? $convertopia_settings['service_URL'] : ''); ?>" />
                                <p class="description" id="home-description"><?php echo esc_html__('Your Convertopia Service URL', 'convertopia-smart-search'); ?></p>
                            </td>
                        </tr>
                    </table>

                    <?php submit_button(); ?>
                </form>
            </div>
        </div>
    </div>
</div>