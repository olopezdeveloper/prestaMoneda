<span class="rj-price-c">
	<span class="product-price {$class}">
		{if !$priceDisplay}
			{displayPrice currency=$currencyP price=$price }
		{else}
			{displayPrice currency=$currencyP price=$price_tax_exc}
		{/if}
	</span>
</span>