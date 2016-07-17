<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
{if $cur eq 'product'}
<div class="treeBox">
    <h3>{$lang.product_tree}</h3>
    <ul>
        {foreach from=$item_category item=cate}
            <li{if $cate.cur} class="cur"{/if}><a href="{$cate.url}">{$cate.cat_name}</a></li>
            {if $cate.child}
                <ul>
                    {foreach from=$cate.child item=child}
                        <li{if $child.cur} class="cur"{/if}>-<a href="{$child.url}">{$child.cat_name}</a></li>
                    {/foreach}
                </ul>
            {/if}
        {/foreach}
    </ul>
</div>
{else}
<div class="treeBox">
    <h3>{$item_tree}</h3>
    <ul>
        {foreach from=$item_category item=cate}
            <li{if $cate.cur} class="cur"{/if}><a href="{$cate.url}">{$cate.cat_name}</a></li>
            {if $cate.child}
                <ul>
                    {foreach from=$cate.child item=child}
                        <li{if $child.cur} class="cur"{/if}>-<a href="{$child.url}">{$child.cat_name}</a></li>
                    {/foreach}
                </ul>
            {/if}
        {/foreach}
    </ul>
    <ul class="search">
        <div class="searchBox">
            <form name="search" id="search" method="get" action="{$site.root_url}">
                <input type="hidden" name="module" value="article">
                <label for="keyword">{$lang.search_cue}</label>
                <input name="s" type="text" class="keyword" title="{$lang.search_cue}" autocomplete="off" maxlength="128" value="{if $keyword_article}{$keyword_article|escape}{else}{$lang.search_article}{/if}" onclick="formClick(this,'{$lang.search_article}')">
                <input type="submit" class="btnSearch" value="{$lang.btn_submit}">
            </form>
        </div>
    </ul>
</div>
{/if}