<?php // $Id: insert_table.php,v 1.4 2007/01/27 23:23:44 skodak Exp $
    require("../../../../config.php");

    $id = optional_param('id', SITEID, PARAM_INT);

    require_course_login($id);
    @header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <title><?php print_string("insertsvg","editor");?></title>
<script type="text/javascript" src="popup.js"></script>
<script type="text/javascript">
//<![CDATA[

function Init() {
  __dlg_init();
  document.getElementById('svgcode').focus();
};

function onOK() {
  var required = {
    "svgcode": "You should paste some EMBED code here, before we move on..."
  };
  for (var i in required) {
    var el = document.getElementById(i);
    if (!el.value) {
      alert(required[i]);
      el.focus();
      return false;
    }
  }
  var fields = ["svgcode"];
  var param = new Object();
  for (var i in fields) {
    var id = fields[i];
    var el = document.getElementById(id);
    param[id] = el.value;
  }
  __dlg_close(param);
  return false;
};

function onCancel() {
  __dlg_close(null);
  return false;
};
//[[>

function setSVG (svgcode){
    document.getElementById('svgcode').innerHTML = svgcode;
}

</script>

</script>
<style type="text/css">
html, body {
margin: 2px;
background-color: rgb(212,208,200);
font-family: Tahoma, Verdana, sans-serif;
font-size: 11px;
}
button { width: 70px; }
.space { padding: 2px; }
.title { direction:rtl; text-align:center; font-size: 22px;}
form { margin-bottom: 0px; margin-top: 0px; }
</style>

</head>
<body onload="Init()">

<div class="title"><?php print_string("insertsvg","editor") ?></div>

<form action="" method="get">

<table width="100%" border="0" cellspacing="0" cellpadding="22">
  <tr>
    <td width="100%" valign="top">
    <!--iframe src="http://svg-edit.googlecode.com/svn/tags/stable/editor/svg-editor.html" width="750" height="600"/-->
        <iframe src ="/moodle/lib/svg-edit-2.5.nadavkav/svg-editor.html" width="100%" height="600px">
            <p>Your browser does not support iframes.</p-->
        </iframe>

        <textarea style="display:none;" name="svgcode" id="svgcode" cols="40" rows="12"></textarea><br><hr>
        <button type="button" name="ok" onclick="return onOK();"><?php print_string("ok","editor") ?></button>
        <button type="button" name="cancel" onclick="return onCancel();"><?php print_string("cancel","editor") ?></button>
    </td>
  </tr>
</table>

</form>

</body>
</html>