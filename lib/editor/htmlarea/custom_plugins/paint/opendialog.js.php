<?php
/**
 * Created by Nadav Kavalerchik.
 * Contact info: nadavkav@gmail.com
 * Date: 1/15/11 Time: 9:32 PM
 *
 * Description:
 *  Edit the current selected Image (IMG html tag) using a remote Pixlr.com online Image editing service
 *  And saving back, the modified image, into the current course (special user's folder) replacing the current image.
 *  So it can be saved (backup) with the course's files.
 */

  require_once("../../../../../config.php");
  $courseid = optional_param('id', SITEID, PARAM_INT);

?>

var outparam = null;

function __paint (editor) {

    editor.focusEditor();
    image = editor.getParentElement();
    editor.selectNodeContents(image);

    //var outparam = null;
    if (typeof image == "undefined") {
        image = editor.getParentElement();
        if (image && !/^img$/i.test(image.tagName))
            image = null;
    }

    //var sel = editor._getSelection();
    //var range = editor._createRange(sel);

    if (image) outparam = {
        f_url    : HTMLArea.is_ie ? editor.stripBaseURL(image.src) : image.getAttribute("src"),
        f_alt    : image.alt,
        f_border : image.border,
        f_align  : image.align,
        f_vert   : image.vspace,
        f_horiz  : image.hspace,
        f_width  : image.width,
        f_height : image.height
    };

    nbDialog("<?php
    if(true or !empty($courseid) and has_capability('moodle/course:managefiles', get_context_instance(CONTEXT_COURSE, $courseid)) ) {
        echo $CFG->wwwroot."/lib/editor/htmlarea/custom_plugins/paint/dialog.php?id=$courseid";
    } else {
        //echo "insert_swf.php?id=$id";
    }?>" ,1024,768, function (param) {

        if (!param) {   // user must have pressed Cancel
            return false;
        }
        var img = image;
        if (!img) {
            var sel = editor._getSelection();
            var range = editor._createRange(sel);
                if (HTMLArea.is_ie) {
                editor._doc.execCommand("insertimage", false, param.f_url);
                }
            if (HTMLArea.is_ie) {
                img = range.parentElement();
                // wonder if this works...
                if (img.tagName.toLowerCase() != "img") {
                    img = img.previousSibling;
                }
            } else {
                // MOODLE HACK: startContainer.perviousSibling
                // Doesn't work so we'll use createElement and
                // insertNodeAtSelection
                //img = range.startContainer.previousSibling;
                var img = editor._doc.createElement("embed"); //nurit img >embed

                img.setAttribute("src",""+ param.f_url +"");
                img.setAttribute("alt",""+ param.f_alt +"");
                editor.insertNodeAtSelection(img);
            }
        } else {
            img.src = '<?php echo $CFG->wwwroot; ?>/file.php/'+param.f_url;
        }
        for (field in param) {
            var value = param[field];
            switch (field) {
                case "f_alt"    : img.alt    = value; img.title = value; break;
                case "f_border" : img.border = parseInt(value || "0"); break;
                case "f_align"  : img.align  = value; break;
                case "f_vert"   : img.vspace = parseInt(value || "0"); break;
                case "f_horiz"  : img.hspace = parseInt(value || "0"); break;
                case "f_width"  :
                    if(value != 0) {
                        img.width = parseInt(value);
                    } else {
                        break;
                    }
                    break;
                case "f_height"  :
                    if(value != 0) {
                        img.height = parseInt(value);
                    } else {
                        break;
                    }
                    break;
            }
        }
    });
}