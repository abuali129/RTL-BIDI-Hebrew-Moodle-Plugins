<?php //$Id: block_html.php,v 1.8.22.7 2008/11/19 16:47:13 skodak Exp $

class block_html_students extends block_base {

    function init() {
        $this->title = get_string('html_students', 'block_html_students');
        $this->version = 2007101509;
    }

    function applicable_formats() {
        return array('all' => true);
    }

    function specialization() {
        $this->title = isset($this->config->title) ? format_string($this->config->title) : format_string(get_string('newhtmlblock', 'block_html_students'));
    }

    function instance_allow_multiple() {
        return true;
    }

    function get_content() {
		global $USER;

		$isstudent = get_record('role_assignments','userid',$USER->id,'roleid','5'); // Is the $USER assigned as Teacher, anywhere in the system?
        if ($this->content !== NULL) {
            return $this->content;
        }
		//echo "debug student=";print_r($isstudent);
		if (!isadmin($USER->id)) {
		  if (empty($isstudent)) return;
		}

        if (!empty($this->instance->pinned) or $this->instance->pagetype === 'course-view') {
            // fancy html allowed only on course page and in pinned blocks for security reasons
            $filteropt = new stdClass;
            $filteropt->noclean = true;
        } else {
            $filteropt = null;
        }

        $this->content = new stdClass;
        $this->content->text = isset($this->config->text) ? format_text($this->config->text, FORMAT_HTML, $filteropt) : '';
        $this->content->footer = '';

        unset($filteropt); // memory footprint

        return $this->content;
    }

    /**
     * Will be called before an instance of this block is backed up, so that any links in
     * any links in any HTML fields on config can be encoded.
     * @return string
     */
    function get_backup_encoded_config() {
        /// Prevent clone for non configured block instance. Delegate to parent as fallback.
        if (empty($this->config)) {
            return parent::get_backup_encoded_config();
        }
        $data = clone($this->config);
        $data->text = backup_encode_absolute_links($data->text);
        return base64_encode(serialize($data));
    }

    /**
     * This function makes all the necessary calls to {@link restore_decode_content_links_worker()}
     * function in order to decode contents of this block from the backup 
     * format to destination site/course in order to mantain inter-activities 
     * working in the backup/restore process. 
     * 
     * This is called from {@link restore_decode_content_links()} function in the restore process.
     *
     * NOTE: There is no block instance when this method is called.
     *
     * @param object $restore Standard restore object
     * @return boolean
     **/
    function decode_content_links_caller($restore) {
        global $CFG;

        if ($restored_blocks = get_records_select("backup_ids","table_name = 'block_instance' AND backup_code = $restore->backup_unique_code AND new_id > 0", "", "new_id")) {
            $restored_blocks = implode(',', array_keys($restored_blocks));
            $sql = "SELECT bi.*
                      FROM {$CFG->prefix}block_instance bi
                           JOIN {$CFG->prefix}block b ON b.id = bi.blockid
                     WHERE b.name = 'html' AND bi.id IN ($restored_blocks)"; 

            if ($instances = get_records_sql($sql)) {
                foreach ($instances as $instance) {
                    $blockobject = block_instance('html', $instance);
                    $blockobject->config->text = restore_decode_absolute_links($blockobject->config->text);
                    $blockobject->config->text = restore_decode_content_links_worker($blockobject->config->text, $restore);
                    $blockobject->instance_config_commit($blockobject->pinned);
                }
            }
        }

        return true;
    }
}
?>
