<?

function submit_enter() {
  global $anonymous, $categories, $allowed_html, $theme, $user;
  
  ### Guidlines:
  $output .= "<P>Got some news or some thoughts you would like to share?  Fill out this form and they will automatically get whisked away to our submission queue where our moderators will frown at it, poke at it and hopefully post it.  Every registered user is automatically a moderator and can vote whether or not your sumbission should be carried to the front page for discussion.</P>\n";
  $output .= "<P>Note that we do not revamp or extend your submission so it is up to you to make sure your submission is well-written: if you don't care enough to be clear and complete, your submission is likely to be moderated down by our army of moderators.  Try to be complete, aim for clarity, organize and structure your text, and try to carry out your statements with examples.  It is also encouraged to extend your submission with arguments that flow from your unique intellectual capability and experience: offer some insight or explanation as to why you think your submission is interesting.  Make sure your submission has some meat on it!</P>\n";
  $output .= "<P>However, if you have bugs to report, complaints, personal questions or anything besides a public submission, we would prefer you to mail us instead, or your message is likely to get lost.</P><BR>\n";

  ### Submission form:
  $output .= "<FORM ACTION=\"submit.php\" METHOD=\"post\">\n";

  $output .= "<P>\n <B>Your name:</B><BR>\n";
  $output .= format_username($user->userid);
  $output .= "</P>\n";
 
  $output .= "<P>\n";
  $output .= " <B>Subject:</B><BR>\n";
  $output .= " <INPUT TYPE=\"text\" NAME=\"subject\" SIZE=\"50\" MAXLENGTH=\"60\"><BR>\n";
  $output .= " <SMALL><I>Bad subjects are 'Check this out!' or 'An article'.  Be descriptive, clear and simple!</I></SMALL>\n";
  $output .= "</P>\n";

  $output .= "<P><B>Category:</B><BR>\n";
  $output .= " <SELECT NAME=\"category\">\n";
    
  for ($i = 0; $i < sizeof($categories); $i++) {
    $output .= "  <OPTION VALUE=\"$categories[$i]\">$categories[$i]</OPTION>\n";
  }
  
  $output .= " </SELECT>\n";
  $output .= "</P>\n";

  $output .= "<P>\n"; 
  $output .= " <B>Abstract:</B><BR>\n";
  $output .= " <TEXTAREA WRAP=\"virtual\" COLS=\"50\" ROWS=\"10\" NAME=\"abstract\" MAXLENGTH=\"20\"></TEXTAREA><BR>\n";
  $output .= " <SMALL><I>Allowed HTML tags: ". htmlspecialchars($allowed_html) .".</I></SMALL>\n";
  $output .= "</P>\n";

  $output .= "<P>\n"; 
  $output .= " <B>Extended story:</B><BR>\n";
  $output .= " <TEXTAREA WRAP=\"virtual\" COLS=\"50\" ROWS=\"15\" NAME=\"article\"></TEXTAREA><BR>\n";
  $output .= " <SMALL><I>Allowed HTML tags: ". htmlspecialchars($allowed_html) .".</I></SMALL>\n";
  $output .= "</P>\n";
 
  $output .= "<P>\n";
  $output .= " You must preview at least once before you can submit:<BR>\n";
  $output .= " <INPUT TYPE=\"submit\" NAME=\"op\" VALUE=\"Preview submission\">\n";
  $output .= "</P>\n";
 
  $output .= "</FORM>\n";
  
  $theme->header();
  $theme->box("Submit a story", $output);
  $theme->footer();
}

function submit_preview($subject, $abstract, $article, $category) {
  global $categories, $allowed_html, $theme, $user;

  $output .= "<FORM ACTION=\"submit.php\" METHOD=\"post\">\n";

  $output .= "<P>\n";
  $output .= " <B>Your name:</B><BR>\n";
  $output .= format_username($user->userid);
  $output .= "</P>\n";

  $output .= "<P>\n";
  $output .= " <B>Subject:</B><BR>\n";
  $output .= " <INPUT TYPE=\"text\" NAME=\"subject\" SIZE=\"50\" MAXLENGTH=\"60\" VALUE=\"". check_output(check_field($subject)) ."\"><BR>\n";
  $output .= " <SMALL><I>Bad subjects are 'Check this out!' or 'An article'.  Be descriptive, clear and simple!</I></SMALL>\n";
  $output .= "</P>\n";

  $output .= "<P><B>Category:</B><BR>\n";
  $output .= " <SELECT NAME=\"category\">\n";
  for ($i = 0; $i < sizeof($categories); $i++) {
    $output .= "  <OPTION VALUE=\"$categories[$i]\" ";
    if ($category == $categories[$i]) $output .= "SELECTED";
    $output .= ">$categories[$i]</OPTION>\n";
  }
  $output .= "</SELECT>\n";
  $output .= "</P>\n";

  $output .= "<P>\n";
  $output .= "<B>Abstract:</B><BR>\n";
  $output .= " <TEXTAREA WRAP=\"virtual\" COLS=\"50\" ROWS=\"10\" NAME=\"abstract\">". check_output($abstract) ."</TEXTAREA><BR>\n";
  $output .= " <SMALL><I>Allowed HTML tags: ". htmlspecialchars($allowed_html) .".</I></SMALL>\n";
  $output .= "</P>\n";

  $output .= "<P>\n";
  $output .= " <B>Extended story:</B><BR>\n";
  $output .= " <TEXTAREA WRAP=\"virtual\" COLS=\"50\" ROWS=\"15\" NAME=\"article\">". check_output($article) ."</TEXTAREA><BR>\n";
  $output .= " <SMALL><I>Allowed HTML tags: ". htmlspecialchars($allowed_html) .".</I></SMALL>\n";
  $output .= "</P>\n";

  if (empty($subject)) {
    $output .= "<P>\n";
    $output .= " <FONT COLOR=\"red\"><B>Warning:</B></FONT> you did not supply a <U>subject</U>.\n";
    $outout .= "</P>\n";
    $output .= "<P>\n";
    $output .= " <INPUT TYPE=\"submit\" NAME=\"op\" VALUE=\"Preview submission\">\n";
    $output .= "</P>\n";
  }
  else if (empty($abstract)) {
    $output .= "<P>\n";
    $output .= " <FONT COLOR=\"red\"><B>Warning:</B></FONT> you did not supply an <U>abstract</U>.\n";
    $outout .= "</P>\n";
    $output .= "<P>\n";
    $output .= " <INPUT TYPE=\"submit\" NAME=\"op\" VALUE=\"Preview submission\">\n";
    $output .= "</P>\n";
  }
  else { 
    $output .= "<P>\n";
    $output .= " <INPUT TYPE=\"submit\" NAME=\"op\" VALUE=\"Preview submission\"> <INPUT TYPE=\"submit\" NAME=\"op\" VALUE=\"Submit submission\">\n";
    $output .= "</P>\n";
  }

  $output .= "</FORM>\n";
  
  $theme->header();
  $theme->preview($user->userid, check_output($subject), check_output($abstract), "", check_output($article), format_date(time(), "extra large"), check_output($category), "we-hate-typoes");
  $theme->box("Submit a story", $output);
  $theme->footer();
}

function submit_submit($subject, $abstract, $article, $category) {
  global $user, $theme;

  ### Add submission to SQL table:
  db_insert("INSERT INTO stories (author, subject, abstract, article, category, timestamp) VALUES ('$user->id', '". check_input($subject) ."', '". check_input($abstract) ."', '". check_input($article) ."', '". check_input($category) ."', '". time() ."')");
  
  ### Display confirmation message:
  $theme->header(); 
  $theme->box("Thanks for your submission.", "Thanks for your submission.  The submission moderators in our basement will frown at it, poke at it, and vote for it!");
  $theme->footer();

  ### Send e-mail notification (if enabled):
  if ($notify) {
    $message = "New submission:\n\nsubject...: $subject\nauthor....: $user->userid <$user->real_email>\ncategory..: $category\nabstract..:\n$abstract\n\narticle...:\n$article";
    mail($notify_email, "$notify_subject $subject", $message, "From: $notify_from\nX-Mailer: PHP/" . phpversion());
  }

  ### Add log entry:
  watchdog(1, "added new submission with subject `$subject'.");
}

include "includes/theme.inc";

switch($op) {
  case "Preview submission":
    submit_preview($subject, $abstract, $article, $category);
    break;
  case "Submit submission":
    submit_submit($subject, $abstract, $article, $category);
    break;
  default:
    submit_enter();
    break;
}

?>