
/* Set content with default language */
/* Variables:
 - default_language 
*/ 
UPDATE topic SET 
	title = (SELECT topic_i.title FROM topic_i WHERE topic_i.lang = 0 AND topic_i.sid = topic.id), 
	extra = (SELECT topic_i.extra FROM topic_i WHERE topic_i.lang = 0 AND topic_i.sid = topic.id),
	description = (SELECT topic_i.description FROM topic_i WHERE topic_i.lang = 0 AND topic_i.sid = topic.id),
	lang = (SELECT topic_i.lang FROM topic_i WHERE topic_i.lang = 0 AND topic_i.sid = topic.id),
	score = (SELECT topic_i.score FROM topic_i WHERE topic_i.lang = 0 AND topic_i.sid = topic.id),
	midgard_ws_id = 3
/*			(SELECT
				midgard_workspace.id                        
			FROM
				midgard_workspace, midgard_language         
			WHERE
			midgard_workspace.name = midgard_language.code AND midgard_language.id = 0) */
WHERE topic.lang = 0 AND topic.sitegroup = 1;

/* Delete content in default language to avoid duplicates in following bulk update */
DELETE FROM topic_i WHERE lang = 0;


/* Create multilang content in one table, setting valid workspaces ids */
/* Metadata fields must be included! */
/* Variables:
 - sitegroup
 - language
 - workspace
*/	
INSERT INTO topic 
		(guid, name, code, up, sitegroup, component, style, style_inherit, symlink, title, extra, description, lang, score, midgard_ws_oid_id, midgard_ws_id) 
	SELECT 
		topic.guid,topic.name, topic.code, topic.up, topic.sitegroup, topic.component, topic.style, topic.style_inherit, topic.symlink, topic_i.title, topic_i.extra, topic_i.description, topic_i.lang, topic_i.score, topic.id, 
		(SELECT 
			midgard_workspace.id 
		FROM 
			midgard_workspace, midgard_language 
		WHERE 
			midgard_workspace.name = midgard_language.code AND midgard_language.id = topic_i.lang)
	FROM 
		topic, topic_i 
	WHERE 
		topic.sitegroup = 1 AND topic.id = topic_i.sid;

/* Set unique object's id in workspace */
UPDATE topic SET
	midgard_ws_oid_id = topic.id;	
