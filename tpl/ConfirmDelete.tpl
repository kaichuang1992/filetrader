Are you sure you want to delete the following files?
<form method="post">
{if !empty($deleteList) && !empty($markedFiles)}
	{html_checkboxes name='markedFiles' options=$deleteList selected=$markedFiles}</td></tr>
{else}
	<p><em>No files marked for deletion</em></p>
{/if}
<input type="hidden" name="action" value="updateFileInfo">
<input type="submit" value="Confirm Delete" name="buttonPressed">
</form>
