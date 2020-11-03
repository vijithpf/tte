ccm_editorFormidableMailtag = function() {

	var mceEd = tinyMCE.activeEditor;	
	var bm = mceEd.selection.getBookmark();
	mceEd.selection.moveToBookmark(bm);
		
	tinyMCE.execCommand('mceInsertRawHTML', false, '{%FORMIDABLE_MAILING%}', true); 	
}