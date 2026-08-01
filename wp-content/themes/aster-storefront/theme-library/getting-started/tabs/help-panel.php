<?php
/**
 * Help Panel.
 *
 * @package aster_storefront
 */
?>

<div id="help-panel" class="panel-left visible">
    <div id="#help-panel" class="steps">  
        <h4 class="c">
            <?php esc_html_e( 'Quick Setup for Home Page', 'aster-storefront' ); ?>
            <a href="<?php echo esc_url( ASTER_STOREFRONT_THEME_DOCUMENTATION ); ?>" class="button button-primary" style="margin-left: 5px; margin-right: 10px;" target="_blank"><?php esc_html_e( 'Free Theme Documentation', 'aster-storefront' ); ?></a>
        </h4>
        <hr class="quick-set">
        <p><?php esc_html_e( '1) Go to the dashboard. navigate to pages, add a new one, and label it "home" or whatever else you like.The page has now been created.', 'aster-storefront' ); ?></p>
        <p><?php esc_html_e( '2) Go back to the dashboard and then select Settings.', 'aster-storefront' ); ?></p>
        <p><?php esc_html_e( '3) Then Go to readings in the setting.', 'aster-storefront' ); ?></p>
        <p><?php esc_html_e( '4) There are 2 options your latest post or static page.', 'aster-storefront' ); ?></p>
        <p><?php esc_html_e( '5) Select static page and select from the dropdown you wish to use as your home page, save changes.', 'aster-storefront' ); ?></p>
        <p><?php esc_html_e( '6) You can set the home page in this manner.', 'aster-storefront' ); ?></p>
        <br>
        <h4><?php esc_html_e( 'Setup Banner Section', 'aster-storefront' ); ?></h4>
        <hr class="quick-set">
         <p><?php esc_html_e( '1) Go to Appereance > then Go to Customizer.', 'aster-storefront' ); ?></p>
         <p><?php esc_html_e( '2) In Customizer > Go to Front Page Options > Go to Banner Section.', 'aster-storefront' ); ?></p>
         <p><?php esc_html_e( '3) For Setup Banner Section you have to create post in dashboard first.', 'aster-storefront' ); ?></p>
         <p><?php esc_html_e( '4) In Banner Section > Enable the Toggle button > and fill the following details.', 'aster-storefront' ); ?></p>
         <p><?php esc_html_e( '5) In this way you can set Banner Section.', 'aster-storefront' ); ?></p>
        <br>
        <h4><?php esc_html_e( 'Setup Services Section', 'aster-storefront' ); ?></h4>
        <hr class="quick-set">
         <p><?php esc_html_e( '1) Go to dashboard > Go to plugin > add woocommerce plugin.', 'aster-storefront' ); ?></p>
         <p><?php esc_html_e( '2) After installing plugin make products in it and give them particular category.', 'aster-storefront' ); ?></p>
         <p><?php esc_html_e( '3) In Customizer > Go to Front Page Options > Go to Trending Product Section.', 'aster-storefront' ); ?></p>
         <p><?php esc_html_e( '4) In Trending Product Section > Enable the Toggle button and give heading', 'aster-storefront' ); ?></p>
         <p><?php esc_html_e( '5) In this way you can set Trending Product Section.', 'aster-storefront' ); ?></p>
        <br>
    </div>
    <div class="custom-setting">
        <h4><?php esc_html_e( 'Quick Customizer Settings', 'aster-storefront' ); ?></h4>
        <span><a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>" target="_blank" class=""><?php esc_html_e( 'Customize', 'aster-storefront' ); ?></a></span>
    </div>
    <hr>
   <div class="setting-box">
        <div class="custom-links">
            <div class="icon-box">
                <img src="<?php echo esc_url(get_template_directory_uri()) .'/theme-library/getting-started/images/img1.png'; ?>" />
            </div>
            <h5><?php esc_html_e( 'Site Logo', 'aster-storefront' ); ?></h5>
            <a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[control]=custom_logo' ) ); ?>" target="_blank" class=""><?php esc_html_e( 'Customize', 'aster-storefront' ); ?></a>
            
        </div>
        <div class="custom-links">
            <div class="icon-box">
                <img src="<?php echo esc_url(get_template_directory_uri()) .'/theme-library/getting-started/images/img2.png'; ?>" />
            </div>
            <h5><?php esc_html_e( 'Color Picker', 'aster-storefront' ); ?></h5>
            <a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[control]=aster_storefront_primary_color' ) ); ?>" target="_blank" class=""><?php esc_html_e( 'Customize', 'aster-storefront' ); ?></a>
            
        </div>
        <div class="custom-links">
            <div class="icon-box">
                <img src="<?php echo esc_url(get_template_directory_uri()) .'/theme-library/getting-started/images/img3.png'; ?>" />
            </div>
            <h5><?php esc_html_e( 'Theme Options', 'aster-storefront' ); ?></h5>
            <a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=aster_storefront_theme_options' ) ); ?>"target="_blank" class=""><?php esc_html_e( 'Customize', 'aster-storefront' ); ?></a>
            
        </div>
    </div>
    <div class="setting-box">
        <div class="custom-links">
            <div class="icon-box">
                <img src="<?php echo esc_url(get_template_directory_uri()) .'/theme-library/getting-started/images/img4.png'; ?>" />
            </div>
            <h5><?php esc_html_e( 'Header Image ', 'aster-storefront' ); ?></h5>
            <a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[control]=header_image' ) ); ?>" target="_blank" class=""><?php esc_html_e( 'Customize', 'aster-storefront' ); ?></a>
            
        </div>
        <div class="custom-links">
            <div class="icon-box">
                <img src="<?php echo esc_url(get_template_directory_uri()) .'/theme-library/getting-started/images/img5.png'; ?>" />
            </div>
            <h5><?php esc_html_e( 'Footer Option ', 'aster-storefront' ); ?></h5>
            <a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[control]=aster_storefront_footer_copyright_text' ) ); ?>" target="_blank" class=""><?php esc_html_e( 'Customize', 'aster-storefront' ); ?></a>
            
        </div>
        <div class="custom-links">
            <div class="icon-box">
                <img src="<?php echo esc_url(get_template_directory_uri()) .'/theme-library/getting-started/images/img6.png'; ?>" />
            </div>
            <h5><?php esc_html_e( 'Front Page Option', 'aster-storefront' ); ?></h5>
            <a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=aster_storefront_front_page_options' ) ); ?>" target="_blank" class=""><?php esc_html_e( 'Customize', 'aster-storefront' ); ?></a>
            
        </div>
    </div>
</div>