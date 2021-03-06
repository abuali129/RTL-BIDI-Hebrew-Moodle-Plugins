<?PHP
    //This php script contains all the stuff to backup/restore
    //feedback mods

    //This is the "graphical" structure of the feedback mod:
    //
    //                     feedback---------------------------------feedback_tracking
    //                    (CL,pk->id)                            (UL, pk->id, fk->feedback,completed)
    //                        |                                           |
    //                        |                                           |
    //                        |                                           |
    //                 feedback_template                            feedback_completed
    //                   (CL,pk->id)                           (UL, pk->id, fk->feedback)
    //                        |                                           |
    //                        |                                           |
    //                        |                                           |
    //                 feedback_item---------------------------------feedback_value
    //        (ML,pk->id, fk->feedback, fk->template)       (UL, pk->id, fk->item, fk->completed)
    //
    // Meaning: pk->primary key field of the table
    //          fk->foreign key to link with parent
    //          CL->course level info
    //          ML->modul level info
    //          UL->userid level info
    //          message->text of each feedback_posting
    //
    //-----------------------------------------------------------

    //This function executes all the backup procedure about this mod
   function feedback_backup_mods($bf,$preferences) {

      global $CFG;

      $status = true;

      //Iterate over feedback table
      $feedbacks = get_records ("feedback","course",$preferences->backup_course,"id");
      if ($feedbacks) {
         foreach ($feedbacks as $feedback) {
            //Start mod
            fwrite ($bf,start_tag("MOD",3,true));
            //Print feedback data
            fwrite ($bf,full_tag("ID",4,false,$feedback->id));
            fwrite ($bf,full_tag("MODTYPE",4,false,"feedback"));
            fwrite ($bf,full_tag("VERSION",4,false,1)); //version 1 steht fuer die neue Version
            fwrite ($bf,full_tag("NAME",4,false,$feedback->name));
            fwrite ($bf,full_tag("SUMMARY",4,false,$feedback->summary));
            fwrite ($bf,full_tag("ANONYMOUS",4,false,$feedback->anonymous));
            fwrite ($bf,full_tag("EMAILNOTIFICATION",4,false,$feedback->email_notification));
            fwrite ($bf,full_tag("MULTIPLESUBMIT",4,false,$feedback->multiple_submit));
            fwrite ($bf,full_tag("TIMEMODIFIED",4,false,$feedback->timemodified));
             
            //backup the items of each feedback
            backup_feedback_data($bf, $feedback->id, $preferences->mods["feedback"]->userinfo);
             
            //End mod
            $status =fwrite ($bf,end_tag("MOD",3,true));
         }
      }
      return $status;  
   }

   function backup_feedback_data($bf, $feedbackid, $userinfo) {
      global $CFG;
      $status = true;
      $feedbackitems = get_records('feedback_item', 'feedback', $feedbackid);

      if ($feedbackitems) {
         $status =fwrite ($bf,start_tag("ITEMS",4,true));
         foreach ($feedbackitems as $feedbackitem) {
            //Start item
            fwrite ($bf,start_tag("ITEM",5,true));
            //Print item data
            fwrite ($bf,full_tag("ID",6,false,$feedbackitem->id));
            fwrite ($bf,full_tag("NAME",6,false,$feedbackitem->name));
            fwrite ($bf,full_tag("PRESENTATION",6,false,addslashes($feedbackitem->presentation)));
            fwrite ($bf,full_tag("TYP",6,false,$feedbackitem->typ));
            fwrite ($bf,full_tag("HASVALUE",6,false,$feedbackitem->hasvalue));
            fwrite ($bf,full_tag("POSITION",6,false,$feedbackitem->position));
            fwrite ($bf,full_tag("REQUIRED",6,false,$feedbackitem->required));
            
            if ($userinfo) {
               //backup the values of items
               $feedbackvalues = get_records('feedback_value', 'item', $feedbackitem->id);
               if($feedbackvalues) {
                  $status =fwrite ($bf,start_tag("FBVALUES",6,true));
                  foreach($feedbackvalues as $feedbackvalue) {
                     //start value
                     fwrite ($bf,start_tag("FBVALUE",7,true));
                     //print value data
                     fwrite ($bf,full_tag("ID",8,false,$feedbackvalue->id));
                     fwrite ($bf,full_tag("ITEM",8,false,$feedbackvalue->item));
                     fwrite ($bf,full_tag("COMPLETED",8,false,$feedbackvalue->completed));
                     fwrite ($bf,full_tag("VAL",8,false,$feedbackvalue->value));
                     //End value
                     $status =fwrite ($bf,end_tag("FBVALUE",7,true));
                  }
                  $status =fwrite ($bf,end_tag("FBVALUES",6,true));
               }
            }
            //End item
            $status =fwrite ($bf,end_tag("ITEM",5,true));
         }
         $status =fwrite ($bf,end_tag("ITEMS",4,true));
      }
      
      if($userinfo) {
         //backup of feedback-completeds
         $feedbackcompleteds = get_records('feedback_completed', 'feedback', $feedbackid);
         if($feedbackcompleteds) {
            fwrite ($bf,start_tag("COMPLETEDS",4,true));
            foreach ($feedbackcompleteds as $feedbackcompleted) {
               //Start completed
               fwrite ($bf,start_tag("COMPLETED",5,true));
               //Print completed data
               fwrite ($bf,full_tag("ID",6,false,$feedbackcompleted->id));
               fwrite ($bf,full_tag("FEEDBACK",6,false,$feedbackcompleted->feedback));
               fwrite ($bf,full_tag("USERID",6,false,$feedbackcompleted->userid));
               fwrite ($bf,full_tag("TIMEMODIFIED",6,false,$feedbackcompleted->timemodified));
               
               //End completed
               $status =fwrite ($bf,end_tag("COMPLETED",5,true));
            }
            $status =fwrite ($bf,end_tag("COMPLETEDS",4,true));
         }
         
         //backup of tracking-data
         $feedbacktrackings = get_records('feedback_tracking', 'feedback', $feedbackid);
         if($feedbacktrackings) {
            fwrite ($bf,start_tag("TRACKINGS",4,true));
            foreach ($feedbacktrackings as $feedbacktracking) {
               //Start tracking
               fwrite ($bf,start_tag("TRACKING",5,true));
               //Print tracking data
               fwrite ($bf,full_tag("ID",6,false,$feedbacktracking->id));
               fwrite ($bf,full_tag("USERID",6,false,$feedbacktracking->userid));
               fwrite ($bf,full_tag("FEEDBACK",6,false,$feedbacktracking->feedback));
               fwrite ($bf,full_tag("COMPLETED",6,false,$feedbacktracking->completed));
               fwrite ($bf,full_tag("COUNT",6,false,$feedbacktracking->count));
               
               //End completed
               $status =fwrite ($bf,end_tag("TRACKING",5,true));
            }
            $status =fwrite ($bf,end_tag("TRACKINGS",4,true));
         }
         
      }

   }


   function backup_template_data($bf, $templateid, $userinfo) {
      global $CFG;
      $status = true;
      $templateitems = get_records('feedback_item', 'template', $templateid);

      if ($templateitems) {
         $status =fwrite ($bf,start_tag("ITEMS",5,true));
         foreach ($templateitems as $templateitem) {
            //Start item
            fwrite ($bf,start_tag("ITEM",6,true));
            //Print item data
            fwrite ($bf,full_tag("ID",7,false,$templateitem->id));
            fwrite ($bf,full_tag("NAME",7,false,$templateitem->name));
            fwrite ($bf,full_tag("PRESENTATION",7,false,addslashes($templateitem->presentation)));
            fwrite ($bf,full_tag("TYP",7,false,$templateitem->typ));
            fwrite ($bf,full_tag("HASVALUE",7,false,$templateitem->hasvalue));
            fwrite ($bf,full_tag("POSITION",7,false,$templateitem->position));
            fwrite ($bf,full_tag("REQUIRED",7,false,$templateitem->required));
            
            //End item
            $status =fwrite ($bf,end_tag("ITEM",6,true));
         }
         $status =fwrite ($bf,end_tag("ITEMS",5,true));
      }
   }




   //Return an array of info (name,value)
   function feedback_check_backup_mods($course,$user_data=false,$backup_unique_code) {
      //First the course data
      $info[0][0] = get_string("modulenameplural","feedback");
      $info[0][1] = feedback_count($course);
      
      //Now, if requested, the user_data
      
      if ($user_data) {
         $info[1][0] = get_string('ready_feedbacks','feedback');
         $info[1][1] = feedback_completed_count($course);
      }
      
      return $info;
   }

///////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////
//// INTERNAL FUNCTIONS. BASED IN THE MOD STRUCTURE

   //Returns an array of feedbacks ids 
   function feedback_count ($course) {
      global $CFG;
      return count_records('feedback', 'course', $course);
   }
   
   function feedback_completed_count($course) {
      global $CFG;
      $count = 0;
      //get all feedbacks
      $feedbacks = get_records('feedback', 'course', $course);
      if($feedbacks) {
         foreach($feedbacks as $feedback) {
            $count += count_records('feedback_completed', 'feedback', $feedback->id);
         }
      }
      return $count;
   }
   
?>
