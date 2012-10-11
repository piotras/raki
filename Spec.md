
First, read README file. It includes the main ideas.


Schema types
------------

For every multilang type:

* Create new, one type with all properties
* Explicitly add sitegroup (unsigned integer) property 
* Explicitly add lang (unsigned integer) property

New schemas should be taken into account during migration.
They can be defined in ini file with midgard.schema_path directive.

