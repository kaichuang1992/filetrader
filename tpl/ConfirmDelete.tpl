<div class="filelist">
{if empty($deleteList)}
        <div class="filelist_nofiles">No files in this collection</div>
{else}
	<div class="filelist_confirm">Are you sure you want to delete the following files?</div>
        <form method="post">
                <input type="hidden" name="action" value="updateFileInfo">
                <table>
                {foreach $deleteList as $id => $name}
                        <tr>
                                <td><input type="checkbox" name="markedFiles[]" value="{$id}" checked="checked" /></td>
                                <td>{$name}</td>
                        </tr>
                {/foreach}
                </table>
                <div class="filelist_controls">
                        <input type="submit" value="Confirm Delete" name="buttonPressed">
                </div> <!-- /filelist_controls -->
        </form>
{/if}
</div>
