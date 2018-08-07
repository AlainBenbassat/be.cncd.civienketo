<h3>{ts}Logs{/ts}</h3>

<p>The current time is {$currentTime}</p>

{foreach from=$logs item=log key=fieldName}
<p>{$log}</p>
{/foreach}
