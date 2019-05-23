## Access single-sign-on protected URLs using configured credentials, and perform read/write operations.

If you are ever on a rush for an automated script to perform read/write operation on data residing behind single-sign-on protected URL, this module is for you. Just configure a working SSO username & password into config.ini file - you are done!

*  class _SingleSignOn_ - tries to access the single-sign-on protected URL, and follows HTTP redirects to the authentication server, and pass through authentication server by posting credentials configured in config.ini file. After successful authentication, a cookie jar stores necessary cookies to perform subsequent visits to any URLs within the protected host.
*  class _Data Streamer_ - is used to perform read/write operations on protected server resources, after successfully signing into protected hosts with the help of _SignleSignOn_ class.

### How To Install
Visit [packagist](https://packagist.org/packages/jojijacobk/access-sso-protected-data) for details. 

1. Install the package via composer 
```composer require jojijacobk/access_sso_protected_data```

2. Make _config.ini_ file in the root directory (where _composer.json_ file resides). You can make the _config.ini_ file either by copying it from `vendor/jojijacobk/access_sso_protected_data/config.ini`, or by the copying the _config.ini_ sample as described below.

` config.ini `

````
; single sign-on credentials
[single_sign_on]
username = hello@company.com
password = xxxxx
````

3. Make a PHP file in the root directory (where _composer.json & _config.ini_ resides), let's call it `demo.php` and write the following script to read data from single-sign-on protected page: 

` demo.php `

````PHP
<?php
 
require 'vendor/autoload.php';

$requestUrl_1 = "https://jira.your-company.com/jira/rest/api/2/search?jql=xxx";
$requestUrl_2 = "https://confluence.your-company.com/confluence/rest/api/content/yyy";
 
echo \jojijacobk\access_sso_protected_data\DataStreamer::read($requestUrl_1);
echo \jojijacobk\access_sso_protected_data\DataStreamer::read($requestUrl_2);
  
````

### Support or Contact

Github pull requests: *https://github.com/jojijacobk/Access-SSO-Protected-Data*
 
Contact me: <joji.jacob.k@gmail.com>
