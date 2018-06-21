<?php

/**
 * Fields
 */
$GLOBALS['TL_LANG']['MSC']['alias'] = ['Alias', 'The alias is a unique reference that can be used instead of the numeric id.'];
$GLOBALS['TL_LANG']['MSC']['utilsBundle'][\HeimrichHannot\UtilsBundle\Dca\DcaUtil::PROPERTY_AUTHOR_TYPE] = ['Author type', 'Choose the type of author here.'];
$GLOBALS['TL_LANG']['MSC']['utilsBundle'][\HeimrichHannot\UtilsBundle\Dca\DcaUtil::PROPERTY_AUTHOR_TYPE][\HeimrichHannot\UtilsBundle\Dca\DcaUtil::AUTHOR_TYPE_NONE] = 'No author';
$GLOBALS['TL_LANG']['MSC']['utilsBundle'][\HeimrichHannot\UtilsBundle\Dca\DcaUtil::PROPERTY_AUTHOR_TYPE][\HeimrichHannot\UtilsBundle\Dca\DcaUtil::AUTHOR_TYPE_MEMBER] = 'Member (Frontend)';
$GLOBALS['TL_LANG']['MSC']['utilsBundle'][\HeimrichHannot\UtilsBundle\Dca\DcaUtil::PROPERTY_AUTHOR_TYPE][\HeimrichHannot\UtilsBundle\Dca\DcaUtil::AUTHOR_TYPE_USER] = 'User (Backend)';
$GLOBALS['TL_LANG']['MSC']['utilsBundle'][\HeimrichHannot\UtilsBundle\Dca\DcaUtil::PROPERTY_AUTHOR] = ['Author', 'This field contains the author of the record.'];
$GLOBALS['TL_LANG']['MSC']['utilsBundle'][\HeimrichHannot\UtilsBundle\Dca\DcaUtil::PROPERTY_SESSION_ID] = ['Session-ID', 'This field contains the session ID of the allowed editor.'];

/**
 * Salutations
 */
$GLOBALS['TL_LANG']['MSC']['utilsBundle']['salutation'] = 'Dear';
$GLOBALS['TL_LANG']['MSC']['utilsBundle']['salutationMale'] = 'Dear Mr.';
$GLOBALS['TL_LANG']['MSC']['utilsBundle']['salutationFemale'] = 'Dear Mrs.';
$GLOBALS['TL_LANG']['MSC']['utilsBundle']['salutationGeneric'] = 'Dear Sir or Madam';

$GLOBALS['TL_LANG']['MSC']['utilsBundle']['genderMale'] = 'Mr.';
$GLOBALS['TL_LANG']['MSC']['utilsBundle']['genderFemale'] = 'Mrs.';

$GLOBALS['TL_LANG']['MSC']['utilsBundle']['genderFe']['male']   = 'Mr.';
$GLOBALS['TL_LANG']['MSC']['utilsBundle']['genderFe']['female'] = 'Mrs.';

/**
 * Date/time
 */
$GLOBALS['TL_LANG']['MSC']['datediff']['just_now'] = 'Just now';
$GLOBALS['TL_LANG']['MSC']['datediff']['min_ago'] = '1 minute ago';
$GLOBALS['TL_LANG']['MSC']['datediff']['nmins_ago'] = '%d minute ago';
$GLOBALS['TL_LANG']['MSC']['datediff']['hour_ago'] = '1 hour ago';
$GLOBALS['TL_LANG']['MSC']['datediff']['nhours_ago'] = '%d hours ago';
$GLOBALS['TL_LANG']['MSC']['datediff']['yesterday'] = 'Yesterday';
$GLOBALS['TL_LANG']['MSC']['datediff']['ndays_ago'] = '%d days ago';
$GLOBALS['TL_LANG']['MSC']['datediff']['week_ago'] = '1 week ago';
$GLOBALS['TL_LANG']['MSC']['datediff']['nweeks_ago'] = '%d weeks ago';
$GLOBALS['TL_LANG']['MSC']['datediff']['nmonths_ago'] = '%d months ago';
$GLOBALS['TL_LANG']['MSC']['datediff']['year_ago'] = '%d years ago';
$GLOBALS['TL_LANG']['MSC']['datediff']['years_ago'] = '%d years ago';
$GLOBALS['TL_LANG']['MSC']['second'] = 'Second';
$GLOBALS['TL_LANG']['MSC']['seconds'] = 'Seconds';
$GLOBALS['TL_LANG']['MSC']['minute'] = 'Minute';
$GLOBALS['TL_LANG']['MSC']['minutes'] = 'Minutes';
$GLOBALS['TL_LANG']['MSC']['hour'] = 'Hour';
$GLOBALS['TL_LANG']['MSC']['hours'] = 'Hours';
$GLOBALS['TL_LANG']['MSC']['day'] = 'Day';
$GLOBALS['TL_LANG']['MSC']['days'] = 'Days';
$GLOBALS['TL_LANG']['MSC']['week'] = 'Week';
$GLOBALS['TL_LANG']['MSC']['weeks'] = 'Weeks';
$GLOBALS['TL_LANG']['MSC']['month'] = 'Month';
$GLOBALS['TL_LANG']['MSC']['months'] = 'Months';
$GLOBALS['TL_LANG']['MSC']['year'] = 'Year';
$GLOBALS['TL_LANG']['MSC']['years'] = 'Years';
$GLOBALS['TL_LANG']['MSC']['timePeriod']['s'] = 'Second(s)';
$GLOBALS['TL_LANG']['MSC']['timePeriod']['m'] = 'Minute(s)';
$GLOBALS['TL_LANG']['MSC']['timePeriod']['h'] = 'Hour(s)';
$GLOBALS['TL_LANG']['MSC']['timePeriod']['d'] = 'Day(s)';

/**
 * Counties
 */
$GLOBALS['TL_LANG']['COUNTIES']['de']['bw'] = 'Baden-Württemberg';
$GLOBALS['TL_LANG']['COUNTIES']['de']['by'] = 'Bavaria';
$GLOBALS['TL_LANG']['COUNTIES']['de']['be'] = 'Berlin';
$GLOBALS['TL_LANG']['COUNTIES']['de']['bb'] = 'Brandenburg';
$GLOBALS['TL_LANG']['COUNTIES']['de']['hb'] = 'Bremen';
$GLOBALS['TL_LANG']['COUNTIES']['de']['hh'] = 'Hamburg';
$GLOBALS['TL_LANG']['COUNTIES']['de']['he'] = 'Hesse';
$GLOBALS['TL_LANG']['COUNTIES']['de']['mv'] = 'Mecklenburg Western Pomerania';
$GLOBALS['TL_LANG']['COUNTIES']['de']['ni'] = 'Lower Saxony';
$GLOBALS['TL_LANG']['COUNTIES']['de']['nw'] = 'Northrhine-Westphalia';
$GLOBALS['TL_LANG']['COUNTIES']['de']['rp'] = 'Rhineland Palatinate';
$GLOBALS['TL_LANG']['COUNTIES']['de']['sl'] = 'Saarland';
$GLOBALS['TL_LANG']['COUNTIES']['de']['sn'] = 'Saxony';
$GLOBALS['TL_LANG']['COUNTIES']['de']['st'] = 'Saxony-Anhalt';
$GLOBALS['TL_LANG']['COUNTIES']['de']['sh'] = 'Schleswig-Holstein';
$GLOBALS['TL_LANG']['COUNTIES']['de']['th'] = 'Thuringia';

/**
 * Logic
 */
$GLOBALS['TL_LANG']['MSC']['databaseOperators'] = [
    \HeimrichHannot\UtilsBundle\Database\DatabaseUtil::OPERATOR_LIKE          => 'like',
    \HeimrichHannot\UtilsBundle\Database\DatabaseUtil::OPERATOR_UNLIKE        => 'not like',
    \HeimrichHannot\UtilsBundle\Database\DatabaseUtil::OPERATOR_EQUAL         => '=',
    \HeimrichHannot\UtilsBundle\Database\DatabaseUtil::OPERATOR_UNEQUAL       => '!=',
    \HeimrichHannot\UtilsBundle\Database\DatabaseUtil::OPERATOR_LOWER         => '&lt;',
    \HeimrichHannot\UtilsBundle\Database\DatabaseUtil::OPERATOR_GREATER       => '&gt;',
    \HeimrichHannot\UtilsBundle\Database\DatabaseUtil::OPERATOR_LOWER_EQUAL   => '&lt;=',
    \HeimrichHannot\UtilsBundle\Database\DatabaseUtil::OPERATOR_GREATER_EQUAL => '&gt;=',
    \HeimrichHannot\UtilsBundle\Database\DatabaseUtil::OPERATOR_IN            => 'in',
    \HeimrichHannot\UtilsBundle\Database\DatabaseUtil::OPERATOR_NOT_IN        => 'not in',
    \HeimrichHannot\UtilsBundle\Database\DatabaseUtil::OPERATOR_IS_NULL       => 'is null',
    \HeimrichHannot\UtilsBundle\Database\DatabaseUtil::OPERATOR_IS_NOT_NULL   => 'is not null',
    \HeimrichHannot\UtilsBundle\Database\DatabaseUtil::OPERATOR_IS_EMPTY      => 'is empty (=\'\')',
    \HeimrichHannot\UtilsBundle\Database\DatabaseUtil::OPERATOR_IS_NOT_EMPTY  => 'is not empty (!=\'\')',
];

$GLOBALS['TL_LANG']['MSC']['connectives'] = [
    \HeimrichHannot\UtilsBundle\Database\DatabaseUtil::SQL_CONDITION_AND => 'and',
    \HeimrichHannot\UtilsBundle\Database\DatabaseUtil::SQL_CONDITION_OR  => 'or',
];

/**
 * Errors
 */
$GLOBALS['TL_LANG']['ERR']['posFloat']['commaFound'] = 'Invalid character found: comma. Please use a dot instead.';
$GLOBALS['TL_LANG']['ERR']['posFloat']['noFloat'] = 'Please enter a positive floating point number (with dot instead of comma).';

/**
 * Misc
 */
$GLOBALS['TL_LANG']['MSC']['yes'] = 'Yes';
$GLOBALS['TL_LANG']['MSC']['no'] = 'No';
$GLOBALS['TL_LANG']['MSC']['page'] = 'Page';


/**
 * Pagination
 */
$GLOBALS['TL_LANG']['MSC']['readOnSinglePage'] = 'Read on one page';