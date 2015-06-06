<?php
require '../../../zb_system/function/c_system_base.php';
require '../../../zb_system/function/c_system_admin.php';
$zbp->Load();
$action = 'root';
if (!$zbp->CheckRights($action)) {$zbp->ShowError(6);die();}
if (!$zbp->CheckPlugin('themeEditor')) {$zbp->ShowError(48);die();}

$blogtitle = 'themeEditor';
require $blogpath . 'zb_system/admin/admin_header.php';
?>
<style type="text/css">
.fixedPanel {
	position: fixed;
	height: 32px;
	top: 0px;
	width: 80%;
	z-index: 1000000;
	opacity: 0.8;
	text-align: center;
}

</style>
<?php
require $blogpath . 'zb_system/admin/admin_top.php';
require './function.php';
?>
<div id="divMain">
  <div id="divMain2">
  <form method="post" id="formSubmit">
  <div class="fixedPanel" style="">
  	<input class="button" type="button" value="保存" id="saveButton" style="margin: 0; height: 32px;">
  <select style="width: 80%; height: 32px; padding: 0;" id="fileSelect">
  	<option value="" disabled="disabled" selected="selected">请选择...</option>
  <?php
$options = scanThemeDir();
foreach ($options as $id => $value) {
	echo '<option value="' . $value . '">' . $value . '</option>';
}
?>
  </select>
	</div>
	<div id="editor"></div>
  </form>
  </div>
</div>
<script src="ace/ace.js" type="text/javascript"></script>
<script src="ace/ext-emmet.js" type="text/javascript"></script>
<script src="ace/emmet.js"></script>
<script>
$(function() {

	var fileChangeState = false;
	var emmet = require('ace/ext/emmet');
	var editor = ace.edit("editor");
	var editorSession = editor.getSession();
	var saveEditor = function() {
		$.ajax({
			url: 'ajax.php?action=save&filename=' + encodeURI($("#fileSelect").val()),
			data: {
				content: editorSession.getValue()
			},
			method: 'POST',
			dataType: 'json'
		}).done(function(data) {
			fileChangeState = false;
		}).always(function() {

		});
	};

	editor.setTheme("ace/theme/xcode");
	editor.setAutoScrollEditorIntoView(true);
	//editor.setOption("minLines", parseInt(screen.height / 20));
	editor.setOption("maxLines", 10000000);
	editor.setOption("enableEmmet", true);
	editor.commands.addCommand({
		name: "showKeyboardShortcuts",
		bindKey: {win: "Ctrl-Alt-h", mac: "Command-Alt-h"},
		exec: function(editor) {
			ace.config.loadModule("ace/ext/keybinding_menu", function(module) {
				module.init(editor);
				editor.showKeyboardShortcuts()
			})
		}
	});
	editor.commands.addCommand({
		name: "Save",
		bindKey: {win: "Ctrl-S", mac: "Command-S"},
		exec: function(editor) {
			saveEditor();
		}
	});
	editorSession.on('change', function() {
		fileChangeState = true;
	});
	$("#fileSelect").change(function(e) {
		if (!fileChangeState || confirm('你当前编辑的文件还没保存，确定要切换文件吗？')) {
			$.ajax({
				url: 'ajax.php?action=load&filename=' + encodeURI($(this).val()),
				method: 'GET',
				dataType: 'json'
			}).done(function(data) {
				editorSession.setMode('ace/mode/' + data.aceMode);

				editorSession.setValue(data.content);
				fileChangeState = false;
			}).always(function() {

			});
		}
	});
	$("#saveButton").click(saveEditor);
	window.onbeforeunload = function() {
		if (fileChangeState) {
			return '你当前编辑的文件还没保存，确定要退出吗？'
		} else {
			return;
		}
	}
});
</script>
<?php
require $blogpath . 'zb_system/admin/admin_footer.php';
RunTime();
?>