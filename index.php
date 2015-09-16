<?php

// $Id: index.php,v 1.7.2.3 2009/08/31 22:00:00 mudrd8mz Exp $

/**
 * This page lists all the instances of newmodule in a particular course
 *
 * @author  obh@turforlag.dk
 * @version $Id: index.php,v 1.7.2.3 2010/03/01 22:00:00 mudrd8mz Exp $
 * @package mod/webfeedback
 */
global $DB, $USER, $SESSION, $CFG;

require_once('../../config.php');
require_once('lib.php');
require_once('webfeedback_form.php');

// add the javascripts
require_js('js/flashdetect.js');
require_js('js/webfeedback.js');

// collect prams
$courseid = required_param('courseid', PARAM_INT);   // course
$pagetitle = required_param('pagetitle');

if (!$course = get_record('course', 'id', $courseid)) {
    error('Course ID is incorrect');
}

// ensure the user is logged in and has access to the site
require_course_login($course);

// setup the page
print_header_simple(get_string('modulenameplural', 'webfeedback'));

// initiaiate the form (webfeedback_form.php)
$wfform = new webfeedback_form();

// handle the form
if ($wfform->is_cancelled()) {
    // Cancel form
    close_window();
} else if ($fromform = $wfform->get_data()) {
    // Submit form
    // only send a mail if the user have added a description
    if ($fromform->description != '') {

        /*

          $mail = get_mailer();
          $mail->From = $CFG->webfeedback_email_sender;
          $mail->FromName = $CFG->webfeedback_email_sendername;
          //$mail->FromName = $USER->email;

          if (!empty($CFG->webfeedback_email1)) {
          $mail->AddAddress($CFG->webfeedback_email1);
          }
          if (!empty($CFG->webfeedback_email2)) {
          $mail->AddAddress($CFG->webfeedback_email2);
          }
          if (!empty($CFG->webfeedback_email3)) {
          $mail->AddAddress($CFG->webfeedback_email3);
          }

          $mail->IsHTML(false);
          $mail->Subject = $fromform->pagetitle;
         * 
         */

        // construct the mailto link
        $mailcontent = str_replace("\r\n", "%0D%0A", $fromform->description);
        $mailcontent = str_replace(" ", "%20", $mailcontent);
        $mailcontent = str_replace(" ", "", $mailcontent);
        $mailcontent = '"Klik for at besvare denne mail":mailto:' . $USER->email . '?subject=turteori.dk%20/%20support&body=Hej%20' . str_replace(" ", "%20", $USER->firstname) . '%0D%0A%0D%0ADu%20skriver:%0D%0A%0D%0A' . $mailcontent;
        $mailsigniture = '%0D%0A%0D%0A%0D%0A%0D%0A%0D%0ATak%20for%20din%20henvendelse.%0D%0A%0D%0A%0D%0AMed%20venlig%20hilsen%0D%0A%0D%0ATurteori%20support%0D%0ATUR%20Forlag%0D%0A%0D%0Awww.turteori.dk%0D%0Awww.turforlag.dk';

        // build the body text
        $bodytext = $mailcontent . $mailsigniture . "\n";
        $bodytext .= "\n" . "$fromform->description\n\n";
        $bodytext .= "\n" . get_string('user', 'webfeedback') . "$USER->username";
        $bodytext .= "\n" . get_string('username', 'webfeedback') . "$USER->firstname $USER->lastname";
        $bodytext .= "\n" . get_string('usermail', 'webfeedback') . $USER->email;
        $bodytext .= "\n" . get_string('pageurl', 'webfeedback') . "$fromform->pageurl";
        $bodytext .= "\n" . get_string('uaos', 'webfeedback') . "$fromform->uaos";
        $bodytext .= "\n" . get_string('uabrowser', 'webfeedback') . "$fromform->uabrowser";
        $bodytext .= "\n" . get_string('uabrowserversion', 'webfeedback') . "$fromform->uabrowserversion";
        $bodytext .= "\n" . get_string('uaflashversion', 'webfeedback') . "$fromform->uaflashversion";
        $bodytext .= "\n" . get_string('uascreensize', 'webfeedback') . "$fromform->uascreensize";
        $bodytext .= "\n" . get_string('rating', 'webfeedback') . "$fromform->rating";

        //$mail->Body = $bodytext;
        //$mail->Send();
        // setup redmine integration
        require_once ('ActiveResource.php');

        class Issue extends ActiveResource {

            var $site = 'http://mailin:EacxDyP7@projekt.turforlag.dk/redmine/';
            var $request_format = 'xml';

        }

        // create a new issue
        $issue = new Issue(array('subject' => utf8_decode($fromform->pagetitle), 'project_id' => '15', 'description' => utf8_decode($bodytext)));
        $issue->save();
    }

    // save the rating to the database
    $tabelname = 'webfeedback';
    $dbentry = new stdClass();
    $dbentry->courseid = $COURSE->id;
    $dbentry->userid = $USER->id;
    $dbentry->pageurl = $fromform->pageurl;
    $dbentry->pagetitle = $fromform->pagetitle;
    $dbentry->rating = $fromform->rating;
    //   $dbentry->timecreated = strftime();
    // update or insert record
    if (record_exists($tabelname, 'userid', $dbentry->userid, 'pageurl', $dbentry->pageurl)) {
        $recordtoupdate = get_record($tabelname, 'userid', $dbentry->userid, 'pageurl', $dbentry->pageurl);
        $dbentry->id = $recordtoupdate->id;
        update_record($tabelname, $dbentry);
    } else {
        insert_record($tabelname, $dbentry);
    }

    close_window();
} else {
    // initial form view
    // if the user have rated this page before, fetch that rating
    $tabelname = 'webfeedback';
    if (record_exists($tabelname, 'userid', $USER->id, 'pageurl', get_current_url())) {
        $dbentry = get_record($tabelname, 'userid', $USER->id, 'pageurl', get_current_url());
        $rating = $dbentry->rating;
    } else {
        $rating = 2;
    }

    // initiate the browser stat class
    require_once 'Browser.php';
    $browser = new Browser();

    // set the form defatult values
    $toform['pagetitle'] = $pagetitle;
    $toform['uaos'] = $browser->getPlatform();
    $toform['uabrowser'] = $browser->getBrowser();
    $toform['uabrowserversion'] = $browser->getVersion();
    $toform['pageurl'] = get_current_url();
    $toform['courseid'] = $courseid;
    $toform['userid'] = $USER->id;
    $toform['rating'] = $rating;
    $wfform->set_data($toform);
    $wfform->display();
}

/**
 * Returns the url for the page last viewed by the user.
 * 
 * Uses the moodle log to recover the current activity
 * 
 * @return string url to the last viewed page
 */
function get_current_url() {
    global $CFG, $USER;

    $prevurl = $_GET['parenturl'];

    // if its a scorm, look elsewhere
    if (strpos($prevurl, '/scorm/') !== false) {
        $sql = 'SELECT *
        FROM ' . $CFG->prefix . 'log
        WHERE userid=' . $USER->id . ' AND
              action = "view"
        ORDER BY id DESC
        LIMIT 1';

        $lastlogitem = array_pop(get_records_sql($sql));
        $url = make_log_url($lastlogitem->module, $lastlogitem->url);
        $prevurl = $CFG->wwwroot . str_replace('amp;', '', $url);
    }
    return $prevurl;
}

?>