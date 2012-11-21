<html>
<head>
<style>
body{
	margin:0;
	padding:0;
}
input{
	font-family: arial,helvetica,clean,sans-serif;
	font-size: 13px;
}
</style>
<script>
// http://www.openjs.com/articles/ajax/ajax_file_upload/
function init() {
	document.getElementById('file_upload_form').onsubmit=function() {
		//'upload_target' is the name of the iframe
		document.getElementById('file_upload_form').target = 'upload_target';
		// 
		document.getElementById("upload_target").onload = uploadDone;
	}
}
//Function will be called when iframe is loaded
function uploadDone() {
	var img = frames['upload_target'].document.getElementsByTagName("body")[0].innerHTML;
	var url = document.URL.split("index.php")[0];
	parent.parent.document.getElementById("id_answer_1").changeURL(url+img);
}
window.onload=init;
</script>
</head>
<form id="file_upload_form" method="post" enctype="multipart/form-data" action="upload.php">
<input name="file" id="file" size="27" type="file" />
<input type="submit" name="action" value="Upload" /><br />
<iframe id="upload_target" name="upload_target" src="" style="width:0px;height:0px;border:0px solid #fff;"></iframe>
</form>

</html>
