<table id="contact-activity-selector-dashlet">
<thead>
  <tr>
<!--    <th colspan="1" rowspan="1" class="ui-state-default">
	<div class="DataTables_sort_wrapper"><input type="checkbox" id="form_selector"><span class="DataTables_sort_icon"></span></div>
    </th>-->
    <th colspan="1" rowspan="1" class="ccrm-banking-payment_state ui-state-default">
	<div class="DataTables_sort_wrapper">{ts}Title{/ts}</div>
    </th>
    <th colspan="1" rowspan="1" class="ccrm-banking-payment_state ui-state-default">
	<div class="DataTables_sort_wrapper">{ts}Status{/ts}</div>
    </th>
    <th colspan="1" rowspan="1" class="ccrm-banking-payment_state ui-state-default">
	<div class="DataTables_sort_wrapper">{ts}Records{/ts}</div>
    </th>
    <th colspan="1" rowspan="1" class="ccrm-banking-payment_state ui-state-default">
	<div class="DataTables_sort_wrapper">{ts}Last submission{/ts}</div>
    </th>
    <th colspan="1" rowspan="1" class="ccrm-banking-payment_state ui-state-default">
	<div class="DataTables_sort_wrapper">{ts}Last import{/ts}</div>
    </th>
    <th colspan="1" rowspan="1" class="hiddenElement ui-state-default">
	<div class="DataTables_sort_wrapper">&nbsp;</div>
    </th>
  </tr>
</thead>
<tbody>
  {foreach from=$forms item=form key=fieldName}
  <tr class="odd ">
<!--    <td><input id="check_{$form.id}" type="checkbox"></td>-->
    <td>{$form.label}</td>
    <td>{if $form.extra.downloadable}{ts}Enabled{/ts}{/if}</td>
    <td>{$form.extra.num_of_submissions}</td>
    <td>{$form.extra.last_submission_time|date_format:"%d/%m/%y - %H:%M"}</td>
    <td>{$form.extra.last_importation_time|date_format:"%d/%m/%y - %H:%M"}</td>
    <td><a href="{$form.id}">{ts}Details{/ts}</a> 
        <a href="{$form.extra.url}">{ts}View{/ts}</a> 
        <a href="{$form.extra.enketo_url}">{ts}Form{/ts}</a>
        <a href="{$form.extra.data_url}">{ts}Download{/ts}</a>
    </td>
  </tr>
  {/foreach}
</tbody>
</table>

<div>
  <a class="button" href="{$url_refresh}">
    <div class="icon inform-icon"></div>{ts}Refresh{/ts}
  </a>
</div>
