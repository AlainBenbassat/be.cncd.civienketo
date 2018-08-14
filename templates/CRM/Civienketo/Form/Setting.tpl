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
    <div class="label">{$form.managers.label}</div>
    <div class="content">{$form.managers.html}</div>
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

<h3>{ts}Mail Settings{/ts}</h3>
  <div class="crm-section">
    <div class="label">{$form.template1.label}</div>
    <div class="content">{$form.template1.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.template2.label}</div>
    <div class="content">{$form.template2.html}</div>
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
{* FOOTER *}
<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
