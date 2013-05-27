/*
 * Expands the iframe so that the user can see links. Triggered from
 * the nested page.
 */
function editorChildExpandIframe(uid)
{
	window.parent.editorParentExpandIframe(uid);
}

/*
 * Expands the upload iframe of an editor.
 */
function editorParentExpandIframe(uid)
{
	$("#iframeUpload_"+uid).height(220);
}