
First, read README file. It includes the main ideas.


Schema types
------------

For every multilang type:

* Create new, one type with all properties
* Explicitly add sitegroup (unsigned integer) property 
* Explicitly add lang (unsigned integer) property

New schemas should be taken into account during migration.
They can be defined in ini file with midgard.schema_path directive.

SQL
-------------

All types:

 * Update workspace id using mapped workspaces, sitegroups and languages

Multilang types:

 * Update records which hold content in default language
 (This should be done for every sitegroup found)
 * Delete table_i entries with content in default lang
 * For every language: insert into table select table, table_i where table.id = table_i.sid and lang=X

Repligard table

