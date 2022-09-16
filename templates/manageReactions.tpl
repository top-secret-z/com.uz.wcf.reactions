{capture assign='pageTitle'}{lang}wcf.reactions.manage.page{/lang}{if $pageNo > 1} - {lang}wcf.page.pageNo{/lang}{/if}{/capture}

{capture assign='contentTitle'}{lang}wcf.reactions.manage.page{/lang} <span class="badge">{#$items}</span>{/capture}

{capture assign='contentDescription'}{lang}wcf.reactions.manage.page.description{/lang}{/capture}

{capture assign='headContent'}
	{if $pageNo < $pages}
		<link rel="next" href="{link controller='ManageReactions'}pageNo={@$pageNo+1}{/link}">
	{/if}
	{if $pageNo > 1}
		<link rel="prev" href="{link controller='ManageReactions'}{if $pageNo > 2}pageNo={@$pageNo-1}{/if}{/link}">
	{/if}
{/capture}

{assign var='linkParameters' value=''}
{if $guest}{capture append=linkParameters}&guest={@$guest|rawurlencode}{/capture}{/if}
{if $objectTypeID}{capture append=linkParameters}&objectTypeID={@$objectTypeID|rawurlencode}{/capture}{/if}
{if $reactionTypeID}{capture append=linkParameters}&reactionTypeID={@$reactionTypeID|rawurlencode}{/capture}{/if}
{if $receiver}{capture append=linkParameters}&receiver={@$receiver|rawurlencode}{/capture}{/if}
{if $user}{capture append=linkParameters}&user={@$user|rawurlencode}{/capture}{/if}

{if WCF_VERSION|substr:0:3 >= '5.5'}
	{capture assign='contentInteractionPagination'}
		{pages print=true assign=pagesLinks controller="ManageReactions" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$linkParameters"}
	{/capture}
	
	{include file='header'}
{else}
	{include file='header'}
	
	{hascontent}
		<div class="paginationTop">
			{content}
				{pages print=true assign=pagesLinks controller="ManageReactions" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$linkParameters"}
			{/content}
		</div>
	{/hascontent}
{/if}

<section class="section">
	<h2 class="sectionTitle">
		{lang}wcf.reactions.manage.filter{/lang}
	</h2>
	
	<form method="post" action="{link controller='ManageReactions'}{/link}">
		<section class="section">
			<div class="row rowColGap formGrid">
				<dl class="col-xs-12 col-md-12">
					<dt></dt>
					<dd class="floated">
						<label><input type="radio" name="reactionTypeID" value="0"{if $reactionTypeID == '0'} checked{/if} /> {lang}wcf.reactions.manage.allReactions{/lang}</label>
						{foreach from=$availableReactionsTypes key=key item=item}
							<label><input type="radio" name="reactionTypeID" value="{$key}"{if $reactionTypeID == $key} checked{/if} /> {@$item}</label>
						{/foreach}
					</dd>
				</dl>
			</div>
			<div class="row rowColGap formGrid">
				<dl class="col-xs-12 col-md-3">
					<dt></dt>
					<dd>
						<select name="objectTypeID" id="objectTypeID">
							<option value="0"{if $objectTypeID == 0} selected="selected"{/if}>{lang}wcf.reactions.manage.allObjects{/lang}</option>
							{foreach from=$availableObjectTypes key=key item=item}
								<option value="{$key}"{if $objectTypeID == $key} selected="selected"{/if}> {lang}{$item}{/lang}</option>
							{/foreach}
						</select>
					</dd>
				</dl>
				
				<dl class="col-xs-12 col-md-3">
					<dt></dt>
					<dd>
						<input type="text" id="receiver" name="receiver" value="{$receiver}" placeholder="{lang}wcf.reactions.manage.receiver{/lang}" class="long">
					</dd>
				</dl>
				
				<dl class="col-xs-12 col-md-3">
					<dt></dt>
					<dd>
						<input type="text" id="user" name="user" value="{$user}" placeholder="{lang}wcf.reactions.manage.user{/lang}" class="long">
					</dd>
				</dl>
				
				<dl class="col-xs-12 col-md-3">
					<dt></dt>
					<dd>
						<label><input type="checkbox" name="guest" value="1"{if $guest} checked{/if}> {lang}wcf.reactions.manage.guestReceiver{/lang}</label>
					</dd>
				</dl>
			</div>
		</section>
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
			{csrfToken}
		</div>
	</form>
</section>

{if $items}
	<div class="section tabularBox jsClipboardContainer" data-type="com.uz.wcf.reaction">
		<table class="table">
			<thead>
				<tr>
					{if $__wcf->session->getPermission('admin.user.canDeleteReactions')}
						<th class="columnMark"><label><input type="checkbox" class="jsClipboardMarkAll"></label></th>
						<th class="columnID columnLikeID{if $sortField == 'likeID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='ManageReactions'}pageNo={@$pageNo}&sortField=likeID&sortOrder={if $sortField == 'likeID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					{else}
						<th class="columnID columnLikeID{if $sortField == 'likeID'} active {@$sortOrder}{/if}"><a href="{link controller='ManageReactions'}pageNo={@$pageNo}&sortField=likeID&sortOrder={if $sortField == 'likeID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					{/if}
					<th class="columnTime columnTime{if $sortField == 'time'} active {@$sortOrder}{/if}"><a href="{link controller='ManageReactions'}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.reactions.manage.time{/lang}</a></th>
					<th class="columnText columnReactionTypeID{if $sortField == 'reactionTypeID'} active {@$sortOrder}{/if}"><a href="{link controller='ManageReactions'}pageNo={@$pageNo}&sortField=reactionTypeID&sortOrder={if $sortField == 'reactionTypeID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.reactions.manage.type{/lang}</a></th>
					<th class="columnText columnReceiver{if $sortField == 'receiver'} active {@$sortOrder}{/if}"><a href="{link controller='ManageReactions'}pageNo={@$pageNo}&sortField=receiver&sortOrder={if $sortField == 'receiver' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.reactions.manage.receiver{/lang}</a></th>
					<th class="columnText columnUsername{if $sortField == 'user'} active {@$sortOrder}{/if}"><a href="{link controller='ManageReactions'}pageNo={@$pageNo}&sortField=user&sortOrder={if $sortField == 'user' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.reactions.manage.user{/lang}</a></th>
					<th class="columnText columnTitle{if $sortField == 'objectTypeID'} active {@$sortOrder}{/if}"><a href="{link controller='ManageReactions'}pageNo={@$pageNo}&sortField=objectTypeID&sortOrder={if $sortField == 'objectTypeID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.reactions.manage.title{/lang}</a></th>
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=reaction}
					<tr class="jsLikeRow jsClipboardObject" data-object-id="{@$reaction->likeID}">
						{if $__wcf->session->getPermission('admin.user.canDeleteReactions')}
							<td class="columnMark"><input type="checkbox" class="jsClipboardItem" data-object-id="{@$reaction->likeID}"></td>
							<td class="columnIcon">
								<a href="#" class="jsButtonDelete jsTooltip" title="{lang}wcf.global.button.delete{/lang}" data-confirm-message-html="{lang __encode=true}wcf.reactions.manage.delete.sure{/lang}"><span class="icon icon16 fa-times"></span></a>
							</td>
						{/if}
						<td class="columnID columnLikeID">{$reaction->likeID}</td>
						<td class="columnTime columnTime" style="white-space: nowrap;">{@$reaction->time|time}</td>
						{assign var='reactionType' value=$reaction->getReactionType()}
						<td class="columnText columnReactionTypeID"><span class="jsTooltip" title="{lang}{$reactionType->title}{/lang}">{@$reactionType->renderIcon()}</span></td>
						{assign var='receiver' value=$reaction->getReceiverUserProfile()}
						<td class="columnText columnReceiver">{if $receiver}<a href="{$receiver->getLink()}" data-object-id="{$receiver->userID}" class="userLink">{@$receiver->getFormattedUsername()}</a>{else}{lang}wcf.reactions.manage.guest{/lang}{/if}</td>
						{assign var='user' value=$reaction->getUserProfile()}
						<td class="columnText columnUsername"><a href="{$user->getLink()}" data-object-id="{$user->userID}" class="userLink">{@$user->getFormattedUsername()}</a></td>
						<td class="columnText columnTitle">{@$reaction->getTitle()}</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
{else}
	<p class="info">{lang}wcf.reactions.manage.noItems{/lang}</p>
{/if}

<footer class="contentFooter">
	{hascontent}
		<div class="paginationBottom">
			{content}{@$pagesLinks}{/content}
		</div>
	{/hascontent}
	
	{hascontent}
		<nav class="contentFooterNavigation">
			<ul>
				{content}
					
					{event name='contentFooterNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</footer>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Controller/Clipboard', 'UZ/ManageReactions/InlineEditor'],
		function(ControllerClipboard, UZManageReactionsInlineEditor) {
			new UZManageReactionsInlineEditor(0);
			
			ControllerClipboard.setup({
				hasMarkedItems: {if $hasMarkedItems}true{else}false{/if},
				pageClassName: 'wcf\\page\\ManageReactionsPage'
		});
	});
</script>

{include file='footer'}
