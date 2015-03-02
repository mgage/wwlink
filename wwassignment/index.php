<?php
// $Id: index.php,v 1.7 2007-07-18 19:25:24 mleventi Exp $


/// This page lists all the instances of wwassignment in a particular course
/// Replace wwassignment with the name of your module

    require_once("../../config.php");
    require_once("lib.php");
       
    $id = required_param('id', PARAM_INT);   // course
    
    if (! $course = get_record("course", "id", $id)) {
        error("Course ID is incorrect");
    }
    
    require_login($course->id);
    
    add_to_log($course->id, "wwassignment", "view all", "index.php?id=$course->id", "");

     
/// Get all required strings

    $strwwassignments = get_string("modulenameplural", "wwassignment");
    $strwwassignment  = get_string("modulename", "wwassignment");


/// Print the header

    if ($course->category) {
        $navigation = "<a href=\"../../course/view.php?id=$course->id\">$course->shortname</a> �";
    }
    print_header("$course->shortname: $strwwassignments", "$course->fullname", "$navigation $strwwassignments", "", "", true, "", navmenu($course));

/// Get all the appropriate data
    if (! $wwassignments = get_all_instances_in_course("wwassignment", $course)) {
        notice("There are no $strwwassignments", "../../course/view.php?id=$course->id");
        die;
    }
/// Print the list of instances (your module will probably extend this)
    
    $timenow = time();
    $strname  = get_string("name");
    $strweek  = get_string("week");
    $strtopic  = get_string("topic");
    $strdescription = get_string('description');
    
    $strOpenDate = get_string("open_date", "wwassignment");
    $strDueDate = get_string("due_date", "wwassignment");
    
    if ($course->format == "weeks") {
        $table->head  = array ($strweek, $strname,$strdescription, $strOpenDate, $strDueDate);
        $table->align = array ("center", "left", "left", "left", "left");
    } else if ($course->format == "topics") {
        $table->head  = array ($strtopic, $strname,$strdescription, $strOpenDate, $strDueDate);
        $table->align = array ("center", "left", "left", "left", "left", "left", "left");
    } else {
        $table->head  = array ($strname,$strdescription, $strOpenDate, $strDueDate);
        $table->align = array ("left", "left", "left", "left", "left", "left");
    }
    $webworkclient =& new webwork_client();
    $webworkcourse = _wwassignment_mapped_course($COURSE->id,false);
    foreach ($wwassignments as $wwassignment) {
        // grab specific info for this set:
        if(isset($wwassignment)) {
            //var_dump($wwassignment);
            $wwassignmentsetinfo = $webworkclient->get_assignment_data($webworkcourse,$wwassignment->webwork_set,false);
            $description = $wwassignment->description;
            if (!$wwassignment->visible) {
                //Show dimmed if the mod is hidden
                $link = "<a class=\"dimmed\" href=\"view.php?id=$wwassignment->coursemodule\">$wwassignment->name</a>";
            } else {
                //Show normal if the mod is visible
                $link = "<a href=\"view.php?id=$wwassignment->coursemodule\">$wwassignment->name</a>";
            }
            if ($course->format == "weeks" or $course->format == "topics") {
                $table->data[] = array ($wwassignment->section,  $link, $description, strftime("%c", $wwassignmentsetinfo['open_date']), strftime("%c", $wwassignmentsetinfo['due_date']));
            } else {
                $table->data[] = array ($link, $description, strftime("%c", $wwassignmentsetinfo['open_date']), strftime("%c", $wwassignmentsetinfo['due_date']));
            }
        }
    }
    
    echo "<br />";
    
    print_table($table);
    if( isteacher($course->id) ) {
        print("<p style='font-size: smaller; color: #aaa; text-align: center;'><a style='color: #666;text-decoration:underline' href='".WWASSIGNMENT_WEBWORK_URL."/$course->shortname/instructor' target='_webwork_edit'>".get_string("go_to_webwork", "wwassignment")."</a></p>");
    }
/// Finish the page
    print_footer($course);

?>