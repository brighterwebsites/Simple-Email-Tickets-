<?php
/*
Plugin Name: Simple Tickets from Email
Description: Piping Support Ticket emails into WordPress.
Version: 1.0
Author: Vanessa Brighter Websites 
*/




// Email credentials
$hostname = '{ventraip.email:993/imap/ssl}INBOX';
$username = 'testing@brighterwebsites.com.au';
$password = 'PASSWORD HERE';  // This is Not Secure - Using for development purposes need to update this to be stored in databased hashed. 

// Connect to the mailbox
$inbox = imap_open($hostname, $username, $password) or die('Cannot connect to mailbox: ' . imap_last_error());

// Search for new emails with specific subject line
$emails = imap_search($inbox, 'UNSEEN SUBJECT "Support Request"');  //Emails with Support Request in subject line are pipped. 


//Implementing Multiple Filters:
// To process emails based on multiple criteria, use logical operators in the imap_search query:
// $emails = imap_search($inbox, 'UNSEEN FROM "sender@example.com" SUBJECT "Specific Subject Line"');


if ($emails) {
    // Sort emails from newest to oldest
    rsort($emails);

    foreach ($emails as $email_number) {
        // Fetch the email overview and body
        $overview = imap_fetch_overview($inbox, $email_number, 0);
        $message = imap_fetchbody($inbox, $email_number, 1);

        $subject = $overview[0]->subject;
        $from = $overview[0]->from;

        // Create the ticket post
        $post_data = array(
            'post_title'    => $subject,
            'post_content'  => $message,
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_type'     => 'ticket',
        );

        // Insert the post into WordPress
        wp_insert_post($post_data);

        // Mark the email as seen
        imap_setflag_full($inbox, $email_number, "\\Seen");
    }
}

// Close the mailbox
imap_close($inbox);
