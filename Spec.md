
Midgard1 to Midgard2 migration
==============================

1. Data migration
-----------------

* Database

Entire migration should be done on a copy of production database. Once data migration is done, 
specific changes should ba applied in virtual hosts configuration (e.g. changing database name, at least).

* Workspaces

Midgard2 uses workspaces, so all sitegroups and languages will be transformed to workspaces.
New, particular workspace will be created for every sitegroup and language found in Midgard1 database.
In case of languages, workspaces will be created for those languages for which content exist.

Workspaces can form a tree structure, so by default we will use 'SG0' or 'ROOT' workspace, 
with data transformed from sitegroup 0. Any other sitegroup will be a child workspace. 
Sitegroups are not built as tree structure, so all sitegroups workspaces will be children of 
'ROOT' one.

Examples:

If Midgard1 contains sitegroup 'My Company', it'll be tranformed to workpsace 
accessible by '/ROOT/My Company' path.

'en' language (if such content exist) will be accessible by '/ROOT/My Company/en' path.

* Content

Optimal and preferred way to migrate content itself should be direct SQL query (or queries) executed for each 
type defined as mgdschema one.

2. Accessing sitegroups and multilang content

In Midgard1 we set sitegroup and language, and then we are able to request objects in sitegroup or language context.
The same rule appplies in Midgard2, however we set workspaces instead of sitegroupor languages.

In Midgard1

    mgd_set_sitegroup(x)
    mgd_set_lang(y)
    object.get_by_guid(xyz)

In Midgard2

    ws = WorkspaceManager.get_workspace_by_path('/ROOT/My Company/en')
    MidgardConnection.set_workspace(ws)
    object.get_by_guid(xyz)


