<?php
/**
 * Integration with Emails Module
 * 
 * This class registers emails that can be sent by WPAdverts Emails module 
 * and edited from the wp-admin / Classifieids / Options / Emails panel
 * 
 * @author Grzegorz Winiarski
 * @since 1.3.0
 * @package Adverts
 * @subpackage Payments
 * 
 * The message keys are generated using the following scheme
    
 * // Payment Started (ad saved) -> Notify User
 * add_action( "advert_tmp_to_advert-pending", array( $this, "on_advert_pending_notify_user" ), 10, 1 );
 * // Payment Started (payment instructions) -> Notify User
 * add_action( "adverts_payment_new_to_pending", array( $this, "on_payment_pending_notify_user" ), 10, 1 );
 * // Payment Completed -> Notify User
 * add_action( "adverts_payment_pending_to_completed", array( $this, "on_payment_completed_notify_user" ) );
 * 
 * // Payment Started -> Notify Admin
 * add_action( "advert_tmp_to_advert-pending", array( $this, "on_payment_pending_notify_admin" ), 10, 1 );
 * // Payment Completed -> Notify Admin
 * add_action( "adverts_payment_pending_to_completed", array( $this, "on_payment_completed_notify_admin" ), 10, 1 );
 * 
 * // Paid Publish -> Notify User
 * add_action( "advert-pending_to_publish", array( $this, "on_paid_pending_to_publish_notify_user" ), 10, 1 );
 * // Paid Publish -> Notify Admin
 * add_action( "advert-pending_to_publish", array( $this, "on_paid_pending_to_publish_notify_admin" ), 10, 1 );
 * 
 * // Advert Renewal To Publish -> Notify User
 * add_action( "expired_to_publish", array( $this, "on_expired_to_publish_notify_user"), 10, 1 );
 * // Advert Renewal to Pending -> Notify User
 * add_action( "expired_to_pending", array( $this, "on_expired_to_pending_notify_user"), 10, 1 );
 * 
 * // Advert Renewal To Publish -> Notify Admin
 * add_action( "expired_to_publish", array( $this, "on_expired_to_publish_notify_admin"), 10, 1 );
 * // Advert Renewal to Pending -> Notify Admin
 * add_action( "expired_to_pending", array( $this, "on_expired_to_pending_notify_admin"), 10, 1 );
 */

class Adext_Payments_Emails_Integration {
    
    /**
     * Class constructor
     * 
     * Registers a wpadverts_messages_register filter which registers
     * a new messages in the Email Module.
     * 
     * @since  1.3.0
     * @return void
     */
    public function __construct() {
        add_filter( "wpadverts_messages_register", array( $this, "register_messages" ) );
        add_filter( "adext_emails_list_filter_options", array( $this, "register_filter_optons" ) );

        Adext_Emails::instance()->get_parser()->add_function( "payment_complete_url", "adext_payments_get_checkout_url" );
    }
    
    /**
     * Register option for filter dropdown
     * 
     * This function registers an option for dropdown in wp-admin / Classifieds / Options / Emails list
     * 
     * The function is executed by adext_emails_list_filter_options filter registered in self::__construct().
     * 
     * @see     adext_emails_list_filter_options filter
     * 
     * @since   1.3.0
     * @param   array   $options    List of filter options
     * @return  array               Updated list of options
     */
    public function register_filter_optons( $options ) {
        $options[] = array( "key" => "payments", "label" => __( "Payments", "adverts" ) );
        return $options;
    }
    
    /**
     * Registers new messages in Emails Module
     * 
     * This function is called by wpadverts_messages_register filter registered
     * in self::__construct()
     * 
     * @since   1.3.0
     * @param   array $messages     List of registered messages
     * @return  array               Modified list of messages
     */
    public function register_messages( $messages ) {
        
        /**
         * @TODO: {$payment|payment_complete_url} callback
         */
        
        $messages["payments::on_advert_pending_notify_user"] = array(
            "name" => "payments::on_advert_pending_notify_user",
            "action" => "advert_tmp_to_advert-pending",
            "callback" => array( "function" => array( $this, "on_advert_pending_notify_user" ), "priority" => 10, "args" => 1 ),
            "enabled" => 1,
            "label" => "",
            "notify" => "user",
            "from" => array( "name" => "", "email" => "" ),
            "to" => "{\$advert|contact_email}",
            "subject" => __( "Your Ad has been saved.", "adverts" ),
            "body" => __("Hello,\nyour Ad titled '{\$advert.post_title}' has been saved and is pending payment.\n\nYou will be notified by email how to proccess the payment.", 'adverts'),
            "headers" => array(),
            "attachments" => array()
        );
        $messages["payments::on_payment_pending_notify_user"] = array(
            "name" => "payments::on_payment_pending_notify_user",
            "action" => "adverts_payment_new_to_pending",
            "callback" => array( "function" => array( $this, "on_payment_pending_notify_user" ), "priority" => 10, "args" => 1 ),
            "enabled" => 1,
            "label" => "",
            "notify" => "user",
            "from" => array( "name" => "", "email" => "" ),
            "to" => "{\$payment|contact_email}",
            "subject" => __( "Payment Pending.", "adverts" ),
            "body" => __( "Hello,\nyour Ad titled '{\$advert.post_title}' is pending payment.\n\nIf you were not able to finish the payment you can do that using the link below.\n{\$payment|payment_complete_url}", "adverts" ),
            "headers" => array(),
            "attachments" => array()
        );
        $messages["payments::on_payment_completed_notify_user"] = array(
            "name" => "payments::on_payment_completed_notify_user",
            "action" => "adverts_payment_pending_to_completed",
            "callback" => array( "function" => array( $this, "on_payment_completed_notify_user" ), "priority" => 10, "args" => 1 ),
            "enabled" => 1,
            "label" => "",
            "notify" => "user",
            "from" => array( "name" => "", "email" => "" ),
            "to" => "{\$payment|contact_email}",
            "subject" => __( "Payment Completed", "adverts" ),
            "body" => __( "Hello,\nyour payment for Ad '{\$advert.post_title}' has been completed.", "adverts" ),
            "headers" => array(),
            "attachments" => array()
        );
        $messages["payments::on_payment_pending_notify_admin"] = array(
            "name" => "payments::on_payment_pending_notify_admin",
            "action" => "advert_tmp_to_advert-pending",
            "callback" => array( "function" => array( $this, "on_payment_pending_notify_admin" ), "priority" => 10, "args" => 1 ),
            "enabled" => 1,
            "label" => "",
            "notify" => "admin",
            "from" => array( "name" => "", "email" => "" ),
            "to" => "{\$admin_email}",
            "subject" => __( "New Ad has been published.", "adverts" ),
            "body" => __( "Hello,\nnew Ad titled '{\$advert.post_title}' has been posted and is pending payment.\n\nYou will be notified again when the payment will be proccessed.\n\nYou can edit the Ad here:\n{\$advert|admin_edit_url}", "adverts" ),
            "headers" => array(),
            "attachments" => array()
        );
        $messages["payments::on_payment_completed_notify_admin"] = array(
            "name" => "payments::on_payment_completed_notify_admin",
            "action" => "adverts_payment_pending_to_completed",
            "callback" => array( "function" => array( $this, "on_payment_completed_notify_admin" ), "priority" => 10, "args" => 1 ),
            "enabled" => 1,
            "label" => "",
            "notify" => "admin",
            "from" => array( "name" => "", "email" => "" ),
            "to" => "{\$admin_email}",
            "subject" => __( "Payment Completed.", "adverts" ),
            "body" => __( "Hello,\nthe payment for Ad '{\$advert.post_title}' has been completed.\n\nYou can view the payment details here:\n{\$payment|admin_edit_url}", "adverts" ),
            "headers" => array(),
            "attachments" => array()
        );
        $messages["payments::on_paid_pending_to_publish_notify_user"] = array(
            "name" => "payments::on_paid_pending_to_publish_notify_user",
            "action" => "advert-pending_to_publish",
            "callback" => array( "function" => array( $this, "on_paid_pending_to_publish_notify_user" ), "priority" => 10, "args" => 1 ),
            "enabled" => 1,
            "label" => "",
            "notify" => "user",
            "from" => array( "name" => "", "email" => "" ),
            "to" => "{\$advert|contact_email}",
            "subject" => __( "Your Ad has been approved.", "adverts" ),
            "body" => __( "Hello,\nyour Ad titled '{\$advert.post_title}' has been approved.\n\nTo view your Ad you can use the link below:\n{\$advert.ID|get_permalink}", "adverts" ),
            "headers" => array(),
            "attachments" => array()
        );
        $messages["payments::on_paid_pending_to_publish_notify_admin"] = array(
            "name" => "payments::on_paid_pending_to_publish_notify_admin",
            "action" => "advert-pending_to_publish",
            "callback" => array( "function" => array( $this, "on_paid_pending_to_publish_notify_admin" ), "priority" => 10, "args" => 1 ),
            "enabled" => 1,
            "label" => "",
            "notify" => "admin",
            "from" => array( "name" => "", "email" => "" ),
            "to" => "{\$admin_email}",
            "subject" => __( "New Ad has been published", "adverts" ),
            "body" => __( "Hello,\nnew Ad titled '{\$advert.post_title}' has been published.\n\nYou can view the Ad here:\n{\$advert.ID|get_permalink}\n\nYou can edit the Ad here:\n{\$advert|admin_edit_url}", "adverts" ),
            "headers" => array(),
            "attachments" => array()
        );
        $messages["payments::on_expired_to_publish_notify_user"] = array(
            "name" => "payments::on_expired_to_publish_notify_user",
            "action" => "expired_to_publish",
            "callback" => array( "function" => array( $this, "on_expired_to_publish_notify_user" ), "priority" => 10, "args" => 1 ),
            "enabled" => 1,
            "label" => "",
            "notify" => "user",
            "from" => array( "name" => "", "email" => "" ),
            "to" => "{\$advert|contact_email}",
            "subject" => __( "Your Ad has been saved", "adverts" ),
            "body" => __( "Hello\nyour Ad titled '{\$advert.post_title}' has been renewed and is visible on site again.\n\nTo view your Ad you can use the link below:\n{\$advert.ID|get_permalink}", "adverts" ),
            "headers" => array(),
            "attachments" => array()
        );
        $messages["payments::on_expired_to_pending_notify_user"] = array(
            "name" => "payments::on_expired_to_pending_notify_user",
            "action" => "expired_to_pending",
            "callback" => array( "function" => array( $this, "on_expired_to_pending_notify_user" ), "priority" => 10, "args" => 1 ),
            "enabled" => 1,
            "label" => "",
            "notify" => "user",
            "from" => array( "name" => "", "email" => "" ),
            "to" => "{\$advert|contact_email}",
            "subject" => __( "Your Ad has been renewed and is panding approval.", "adverts" ),
            "body" => __( "Hello,\nyour Ad titled '{\$post.post_title}' has been renewed and is pending moderation.\n\nOnce the administrator will approve or reject your Ad you will be notified by email.", "adverts" ),
            "headers" => array(),
            "attachments" => array()
        );
        $messages["payments::on_expired_to_publish_notify_admin"] = array(
            "name" => "payments::on_expired_to_publish_notify_admin",
            "action" => "expired_to_publish",
            "callback" => array( "function" => array( $this, "on_expired_to_publish_notify_admin" ), "priority" => 10, "args" => 1 ),
            "enabled" => 1,
            "label" => "",
            "notify" => "admin",
            "from" => array( "name" => "", "email" => "" ),
            "to" => "{\$admin_email}",
            "subject" => __( "Ad has been renewed.", "adverts" ),
            "body" => __( "Hello,\nAd titled '%s' has been renewed and published.\n\nYou can view the Ad here:\n{\$advert.ID|get_permalink}\n\nYou can edit the Ad here:\n{\$advert|admin_advert_edit_url}", "adverts" ),
            "headers" => array(),
            "attachments" => array()
        );
        $messages["payments::on_expired_to_pending_notify_admin"] = array(
            "name" => "payments::on_expired_to_pending_notify_admin",
            "action" => "expired_to_pending",
            "callback" => array( "function" => array( $this, "on_expired_to_pending_notify_admin" ), "priority" => 10, "args" => 1 ),
            "enabled" => 1,
            "label" => "",
            "notify" => "admin",
            "from" => array( "name" => "", "email" => "" ),
            "to" => "{\$admin_email}",
            "subject" => __( "Ad has been renewed (action required).", "adverts" ),
            "body" => __( "Hello,\nAd titled '{\$advert.post_title}' has been renewed and is pending approval.\n\nYou can view the Ad here:\n{\$advert.ID|get_permalink}\n\nYou can edit the Ad here:\n{\$advert|admin_advert_edit_url}", "adverts" ),
            "headers" => array(),
            "attachments" => array()
        );
            
        return $messages;
    } 
    
    /**
     * Payment Started (ad saved) -> Notify User
     * 
     * This function is executed on "advert_tmp_to_advert-pending" Post Status Transition
     * 
     * @see     advert_tmp_to_advert-pending
     * 
     * @since   1.3.0
     * @param   WP_Post     $post       Advert Object
     * @return  void
     */
    public function on_advert_pending_notify_user( $post ) {
        if( $post->post_type !== "advert" ) {
            return;
        }
        
        return Adext_Emails::instance()->messages->send_message( "payments::on_advert_pending_notify_user", array( 
            "advert" => $post
        ) );
    }
    
    /**
     * Payment Started (payment instructions) -> Notify User
     * 
     * This function is executed when Payment changes status from "new" to "pending".
     * 
     * @see     adverts_payment_new_to_pending
     * 
     * @since   1.3.0
     * @param   WP_Post     $payment       Payment Object
     * @return  void
     */
    public function on_payment_pending_notify_user( $payment ) {
        if( $payment->post_type !== "adverts-payment" ) {
            return;
        }
        
        return Adext_Emails::instance()->messages->send_message( "payments::on_payment_pending_notify_user", array( 
            "advert" => get_post( get_post_meta( $payment->ID, '_adverts_object_id', true ) ),
            "payment" => $payment
        ) );
    }
    
    /**
     * Payment Completed -> Notify User
     * 
     * This function is executed when Payment changes status from "pending" to "completed".
     * 
     * @see     adverts_payment_pending_to_completed
     * 
     * @since   1.3.0
     * @param   WP_Post     $payment       Payment Object
     * @return  void
     */
    public function on_payment_completed_notify_user( $payment ) {
        if( $payment->post_type !== "adverts-payment" ) {
            return;
        }
        
        return Adext_Emails::instance()->messages->send_message( "payments::on_payment_completed_notify_user", array( 
            "advert" => get_post( get_post_meta( $payment->ID, '_adverts_object_id', true ) ),
            "payment" => $payment
        ) );
    }
    
    /**
     * Paid Publish -> Notify User
     * 
     * This function is executed on "advert-pending_to_publish" Post Status Transition
     * 
     * @see     advert-pending_to_publish
     * 
     * @since   1.3.0
     * @param   WP_Post     $post       Advert Object
     * @return  void
     */
    public function on_paid_pending_to_publish_notify_user( $post ) {
        if( $post->post_type !== "advert" ) {
            return;
        }
        
        return Adext_Emails::instance()->messages->send_message( "payments::on_paid_pending_to_publish_notify_user", array( 
            "advert" => $post
        ) );
    }
    
    /**
     * Paid Publish -> Notify Admin
     * 
     * This function is executed on "advert-pending_to_publish" Post Status Transition
     * 
     * @see     advert-pending_to_publish
     * 
     * @since   1.3.0
     * @param   WP_Post     $post       Advert Object
     * @return  void
     */
    public function on_paid_pending_to_publish_notify_admin( $post ) {
        if( $post->post_type !== "advert" ) {
            return;
        }
        
        return Adext_Emails::instance()->messages->send_message( "payments::on_paid_pending_to_publish_notify_admin", array( 
            "advert" => $post
        ) );
    }
    
    /**
     * Payment Started -> Notify Admin
     * 
     * This function is executed on "advert_tmp_to_advert-pending" Post Status Transition
     * 
     * @see     advert_tmp_to_advert-pending
     * 
     * @since   1.3.0
     * @param   WP_Post     $post       Advert Object
     * @return  void
     */
    public function on_payment_pending_notify_admin( $post ) {
        if( $post->post_type !== "advert" ) {
            return;
        }
        
        return Adext_Emails::instance()->messages->send_message( "payments::on_payment_pending_notify_admin", array( 
            "advert" => $post
        ) );
    }
    
    /**
     * Payment Completed -> Notify Admin
     * 
     * This function is executed when Payment changes status from "pending" to "completed".
     * 
     * @see     adverts_payment_pending_to_completed
     * 
     * @since   1.3.0
     * @param   WP_Post     $payment       Payment Object
     * @return  void
     */
    public function on_payment_completed_notify_admin( $payment ) {
        if( $payment->post_type !== "adverts-payment" ) {
            return;
        }
        
        return Adext_Emails::instance()->messages->send_message( "payments::on_payment_completed_notify_admin", array( 
            "advert" => get_post( get_post_meta( $payment->ID, '_adverts_object_id', true ) ),
            "payment" => $payment
        ) );
    }
    
    /**
     * Advert Renewal To Publish -> Notify User
     * 
     * This function is executed on "expired_to_publish" Post Status Transition
     * 
     * @see     expired_to_publish
     * 
     * @since   1.3.0
     * @param   WP_Post     $post       Advert Object
     * @return  void
     */
    public function on_expired_to_publish_notify_user( $post ) {
        if( $post->post_type !== "advert" ) {
            return;
        }
        
        return Adext_Emails::instance()->messages->send_message( "payments::on_expired_to_publish_notify_user", array( 
            "advert" => $post
        ) );
    }
    
    /**
     * Advert Renewal to Pending -> Notify User
     * 
     * This function is executed on "expired_to_pending" Post Status Transition
     * 
     * @see     expired_to_pending
     * 
     * @since   1.3.0
     * @param   WP_Post     $post       Advert Object
     * @return  void
     */
    public function on_expired_to_pending_notify_user( $post ) {
        if( $post->post_type !== "advert" ) {
            return;
        }
        
        return Adext_Emails::instance()->messages->send_message( "payments::on_expired_to_pending_notify_user", array( 
            "advert" => $post
        ) );
    }
    
    /**
     * Advert Renewal To Publish -> Notify Admin
     * 
     * This function is executed on "expired_to_publish" Post Status Transition
     * 
     * @see     expired_to_publish
     * 
     * @since   1.3.0
     * @param   WP_Post     $post       Advert Object
     * @return  void
     */
    public function on_expired_to_publish_notify_admin( $post ) {
        if( $post->post_type !== "advert" ) {
            return;
        }
        
        return Adext_Emails::instance()->messages->send_message( "payments::on_expired_to_publish_notify_admin", array( 
            "advert" => $post
        ) );
    }
    
    /**
     * Advert Renewal to Pending -> Notify Admin
     * 
     * This function is executed on "expired_to_pending" Post Status Transition
     * 
     * @see     expired_to_pending
     * 
     * @since   1.3.0
     * @param   WP_Post     $post       Advert Object
     * @return  void
     */
    public function on_expired_to_pending_notify_admin( $post ) {
        if( $post->post_type !== "advert" ) {
            return;
        }
        
        return Adext_Emails::instance()->messages->send_message( "payments::on_expired_to_pending_notify_admin", array( 
            "advert" => $post
        ) );
    }
}














