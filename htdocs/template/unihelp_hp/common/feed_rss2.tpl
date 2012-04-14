{*

xPyrus - Framework for Community and knowledge exchange
Copyright (C) 2003-2008 UniHelp e.V., Germany

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, only version 3 of the
License.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program. If not, see http://www.gnu.org/licenses/agpl.txt

*}<?xml version="1.0" encoding="utf-8" ?>

<rss version="2.0" 
   xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
   xmlns:admin="http://webns.net/mvcb/"
   xmlns:dc="http://purl.org/dc/elements/1.1/"
   xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
   xmlns:wfw="http://wellformedweb.org/CommentAPI/"
   xmlns:content="http://purl.org/rss/1.0/modules/content/">
<channel>
    <title>{$metadata.title}</title>
    <link>{$metadata.url}</link>
    <description>{$metadata.description}</description>
    <dc:language>de</dc:language>
    <admin:errorReportsTo rdf:resource="noreply@unihelp.de" />

    <generator>[[local.local.project_name]] - http://{$localSubdomain}.[[local.local.project_domain]]</generator>
    <pubDate>{$smarty.now|unihelp_strftime:"RSS"}</pubDate>
    {* $metadata.additional_fields.image *}

{foreach from=$entries item="entry"}
<item>
    <title>{$entry->getSynTitle()}</title>
    <link>{$entry->getSynLink()}</link>
    {foreach from=$entry->getSynCategories() item="cat"}
        <category>{$cat->name}</category>
    {/foreach}
    
    <dc:creator>{$entry->getSynAuthor()}</dc:creator>
    <content:encoded>
    {$entry->getSynContent(true)|relativeToAbsolute|@escape}
    </content:encoded>
    
    <pubDate>{$entry->getSynPublicationDate()|unihelp_strftime:"RSS"}</pubDate>
    <guid isPermaLink="false">{$entry->getSynGUID()}</guid>
</item>
{/foreach}

</channel>
</rss>
