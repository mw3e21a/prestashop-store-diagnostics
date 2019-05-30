{*
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitues a violation of the user agreement.
*
*
* @author       Michał Wilczyński <mwilczynski0@gmail.com>
* @copyright    Michał Wilczyński
* @license      see above
*}

{block name="defaultForm"}

	{if (isset($form_errors)) && (count($form_errors) > 0)}
		<div class="alert alert-danger">
			<h4>{l s='Error!' mod='storediagnostics'}</h4>
			<ul class="list-unstyled">
				{foreach from=$form_errors item='message'}
					<li>{$message|escape:'htmlall':'UTF-8'}</li>
				{/foreach}
			</ul>
		</div>
	{/if}

	{if (isset($form_infos)) && (count($form_infos) > 0)}
		<div class="alert alert-warning">
			<h4>{l s='Warning!' mod='storediagnostics'}</h4>
			<ul class="list-unstyled">
				{foreach from=$form_infos item='message'}
					<li>{$message|escape:'htmlall':'UTF-8'}</li>
				{/foreach}
			</ul>
		</div>
	{/if}

	{if (isset($form_successes)) && (count($form_successes) > 0)}
		<div class="alert alert-success">
			<h4>{l s='Success!' mod='storediagnostics'}</h4>
			<ul class="list-unstyled">
				{foreach from=$form_successes item='message'}
					<li>{$message|escape:'htmlall':'UTF-8'}</li>
				{/foreach}
			</ul>
		</div>
	{/if}

{/block}



<div class="panel">
	<h3><i class="icon icon-credit-card"></i> {l s='Store Diagnostics' mod='storediagnostics'}</h3>
	<p>
		<strong>{l s='Here is my new generic module!' mod='storediagnostics'}</strong><br />
		{l s='Thanks to PrestaShop, now I have a great module.' mod='storediagnostics'}<br />
		{l s='I can configure it using the following configuration form.' mod='storediagnostics'}
	</p>
	<br />
	<p>
		{l s='This module will boost your sales!' mod='storediagnostics'}
	</p>
</div>

<div class="panel">
	<h3><i class="icon icon-tags"></i> {l s='Documentation' mod='storediagnostics'}</h3>
	<p>
		&raquo; {l s='You can get a PDF documentation to configure this module' mod='storediagnostics'} :
		<ul>
			<li><a href="#" target="_blank">{l s='English' mod='storediagnostics'}</a></li>
			<li><a href="#" target="_blank">{l s='French' mod='storediagnostics'}</a></li>
		</ul>
	</p>
</div>
