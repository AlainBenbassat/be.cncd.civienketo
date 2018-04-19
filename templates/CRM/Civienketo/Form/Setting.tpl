{* HEADER *}

<h3>{ts}Enketo server{/ts}</h3>
  <div class="crm-section">
    <div class="label">{$form.server_url.label}</div>
    <div class="content">{$form.server_url.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.server_token.label}</div>
    <div class="content">{$form.server_token.html}</div>
    <div class="clear"></div>
  </div>

<h3>{ts}Import Settings{/ts}</h3>
  <div class="crm-section">
    <div class="label">{$form.manager.label}</div>
    <div class="content">{$form.manager.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.campaign.label}</div>
    <div class="content">{$form.campaign.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.group_parent.label}</div>
    <div class="content">{$form.group_parent.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.group_email.label}</div>
    <div class="content">{$form.group_email.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.group_postal.label}</div>
    <div class="content">{$form.group_postal.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.send_ack.label}</div>
    <div class="content">{$form.send_ack.html}</div>
    <div class="clear"></div>
  </div>

<h3>{ts}Other Settings{/ts}</h3>
  <div class="crm-section">
    <div class="label">{$form.verbose.label}</div>
    <div class="content">{$form.verbose.html}</div>
    <div class="clear"></div>
  </div>
{*
{foreach from=$elementNames item=elementName}
  <div class="crm-section">
    <div class="label">{$form.$elementName.label}</div>
    <div class="content">{$form.$elementName.html}</div>
    <div class="clear"></div>
  </div>
{/foreach}
*}
{* FOOTER *}
<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
