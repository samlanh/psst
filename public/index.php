<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
    
// Define path to public directory
defined('PUBLIC_PATH') || define('PUBLIC_PATH', realpath(dirname(__FILE__)));
defined('TITLE_REPORT') || define('TITLE_REPORT', "style='color:#000; font-size:16px;font-family:Times New Roman,Khmer OS Muol Light;white-space:nowrap;'");
defined('OTHER_LANG_REQUIRED') || define('OTHER_LANG_REQUIRED', 'false');
defined('SYSTEM_SES') || define('SYSTEM_SES', "authstupsisnew");

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

defined('HEADER_REPORT_TYPE') || define('HEADER_REPORT_TYPE', 2);//1,2,3
defined('PHISYCAL_CONFIG') || define('PHISYCAL_CONFIG', '54-48-10-E2-F8-4E');
defined('PICKUP_TYPE') || define('PICKUP_TYPE', 1);// type=1 for ELT and psis , type=2 for good will
defined('CARD_TYPE') || define('CARD_TYPE', 1);// tyep=1 for ELT , type=2 for good will , type=3 for New World
defined('BRANCHES') || define('BRANCHES', '3');
defined('EDUCATION_LEVEL') || define('EDUCATION_LEVEL', 0);//1=true to show,0=false not show
defined('SHOW_IN_DEGREE') || define('SHOW_IN_DEGREE', 0);//1=show , 0=hide
defined('SHOW_IN_GRADE') || define('SHOW_IN_GRADE', 0);//1=show , 0=hide
defined('SUTUDENT_SESSION') || define('SUTUDENT_SESSION', 'student_auth');//1=true to show,0=false not show
defined('AUTO_PUSH_NOTIFICATION') || define('AUTO_PUSH_NOTIFICATION', 0);//1=push , 0=not push
defined('APP_ID') || define('APP_ID', 'dfc704ab-e023-4b0b-b030-e300f13b74eb');
defined('RECEIPT_TYPE') || define('RECEIPT_TYPE', 3);//1elt,2nws,3psis
defined('ICODE') || define('ICODE', '100323');
defined('SCORE_RESULT_TEMPLATE') || define('SCORE_RESULT_TEMPLATE', 2);//1=for general,2=for AHS
defined('NEW_STU_ID_FROM_TEST') || define('NEW_STU_ID_FROM_TEST', 1);//0=default,1=show stu_id register to enter
defined('STU_ID_TYPE') || define('STU_ID_TYPE', 3);//1=Auto By Branch,2=Auto By Degree,3 by school option
defined('STU_SERIAL_TYPE') || define('STU_SERIAL_TYPE', 2);//1=for general,2=for psis
defined('TEST_CONDICTION') || define('TEST_CONDICTION', 2);//1=for general show branch in text index,2=for psis(not show)
defined('STUDY_DAY_SETTING') || define('STUDY_DAY_SETTING', 2);//0=default 7 day 1= 6 day study, 2= 5 day study
defined('TIEM_IS_MANUAL') || define('TIEM_IS_MANUAL', 1);//0=static 1= manual customize
defined('FEATURE_SCAN_CALLOUT') || define('FEATURE_SCAN_CALLOUT', 1);//0=Disable Feature, 1= Enable Feature
defined('SCORE_RESULT_TEMPLATE') || define('SCORE_RESULT_TEMPLATE', 2);//1=for general,2=for AHS

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap()
            ->run();