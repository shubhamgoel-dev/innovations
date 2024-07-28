# Node by email

Whether you run a news site or blog with daily updates, an eCommerce
store, restaurant, or any kind of business really, composing and sending
emails from a mobile device and posting these to Drupal by email is
probably going to be much easier than doing it through your drupal
dashboard.

Node by email provides functionality to create the node by only sending
the mail to an email id from defined email id.

For Example:

Receiving email id: RRR@example.com From Email ID: FFF@example.com

So the whatever mail is sent by FFF@example.com to Email ID
"RRR@example.com" will create a node in Drupal installation. Email must
be unseen ie Unread email.

IMPORTANT: The email subject is mapped to the node title field The email
body is mapped to the node body field

For a full description of the module, visit the
[project page](https://www.drupal.org/project/node_by_email).

Submit bug reports and feature suggestions, or track changes in the
[issue queue](https://www.drupal.org/project/issues/node_by_email).


## Table of contents

-   Introduction
-   Requirements
-   Installation
-   Configuration
-   Maintainers


## REQUIREMENTS

- This module requires php-imap library.
- sudo apt install php-imap
- Drush must be version 9 or above.


## INSTALLATION

Install the Node by email:
Download it from [Node by email](https://www.drupal.org/project/node_by_email) and install it on your website.

- With Composer
  `$ composer require 'drupal/node_by_email'`


## CONFIGURATIONS

- The page /admin/config/node_by_email/nodebyemailconfig gives
    configuration screen. Detail description is given below.

    1) Imap Connection String: Enter the IMAP Connection String.
        Examples are given in field description
    2) Email/Username Of IMAP Account
        Email/Username of the Email Account
    3) IMAP Account Password
        The password is used to login the Account.
    4) FROM Email
        Email ID, Whatever email is sent from this email to Email ID of your 
        IMAP account. All unseen emails will be created as a node.

    Once the IMAP connection is connected you must see "IMAP connection is 
    made successfully." message on the top of the configuration page. 
    Otherwise warning message will display.

- CONTENT TYPE SETTINGS In content type setting: you need to choose
    which node type should create by this module.

- PUBLISHING OPTIONS
    1) In this option you need to select a user
    which will be the author of the nodes created by this module.
    2) The Published/Unpublished are configurable during the node creation. 
    3) Cron interval time is in seconds.


## Maintainers

- Rajveer Gangwar - [rajveergangwar](https://www.drupal.org/u/rajveergangwar)
- Prashant Mishra - [PrashantMishra](https://www.drupal.org/u/prashant-mishra)
