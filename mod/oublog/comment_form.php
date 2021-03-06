<?php

require_once($CFG->libdir.'/formslib.php');

class mod_oublog_comment_form extends moodleform {

    function definition() {

        global $CFG;

        $maxvisibility = $this->_customdata['maxvisibility'];
        $edit = $this->_customdata['edit'];
        $commentid = $this->_customdata['commentid'];
        $moderated = $this->_customdata['moderated'];
        $confirmed = $this->_customdata['confirmed'];
        $blogid = $this->_customdata['blogid'];
        $postid = $this->_customdata['postid'];

        $mform    =& $this->_form;

        // Get comment content, if editing existing comment
        $comment = get_record('oublog_comments','id',$commentid);

        $mform->addElement('header', 'general', '');

        if ($moderated) {
            $mform->addElement('static', '', '',
                    get_string('moderated_info', 'oublog', $CFG->wwwroot .
                        '/mod/oublog/bloglogin.php?returnurl=editcomment.php?blog=' .
                        $blogid . '%26post=' . $postid));

            $mform->addElement('text', 'authorname',
                    get_string('moderated_authorname', 'oublog'), 'size="48"');
            $mform->setType('authorname', PARAM_TEXT);
            $mform->addRule('authorname', get_string('required'), 'required', null, 'client');
            if ($edit) $mform->setDefault('authorname',$comment->authorname);
        }

        $mform->addElement('text', 'title', get_string('title', 'oublog'), 'size="48"');
        $mform->setType('title', PARAM_TEXT);
        if ($edit) $mform->setDefault('title',$comment->title);

        $message_type = 'htmleditor';
        if ($moderated) {
            $message_type = 'textarea';
        }
        $message_rows = 30;
        if (class_exists('ouflags') && ou_get_is_mobile()){
            $message_type = 'textarea';
            $message_rows = 20;
        }

        $mform->addElement($message_type, 'message', get_string('message', 'oublog'), array('cols'=>50, 'rows'=>$message_rows));
        if ($edit) $mform->setDefault('message',$comment->message);
        $mform->setType('message', PARAM_CLEANHTML);
        $mform->addRule('message', get_string('required'), 'required', null, 'client');

        $mform->setHelpButton('message', array('reading', 'writing', 'questions', 'richtext'), false, 'editorhelpbutton');

        // When using moderation, we include the world's dumbest capcha (the
        // user is told to type 'yes' into the box). Because there is moderation
        // we do not really need a capcha; this is only meant to exclude the
        // stupidest spam robots and reduce the quantity of email sent to
        // moderators. A cookie can skip this step.
        if ($moderated && !$confirmed) {
            $mform->addElement('static', '', '',
                    get_string('moderated_confirminfo', 'oublog'));
            $mform->addElement('text', 'confirm', get_string('moderated_confirm', 'oublog'));
        }

        if ($edit) {
            $submitstring = get_string('savechanges');
        } else {
            $submitstring = get_string('addcomment', 'oublog');
        }

        $this->add_action_buttons(true, $submitstring);

    /// Hidden form vars
        $mform->addElement('hidden', 'blog');
        $mform->setType('blog', PARAM_INT);

        $mform->addElement('hidden', 'post');
        $mform->setType('post', PARAM_INT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        if ($edit) $mform->setDefault('id',$comment->id);
    }

    function validation($data, $files) {
        $moderated = $this->_customdata['moderated'];
        $confirmed = $this->_customdata['confirmed'];

        $errors = array();
        if ($moderated && !$confirmed && (empty($data['confirm']) ||
                $data['confirm'] !== get_string('moderated_confirmvalue', 'oublog'))) {
            $errors['confirm'] = get_string('error_noconfirm', 'oublog');
        }
        return $errors;
    }
}

?>